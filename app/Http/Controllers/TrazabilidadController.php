<?php

namespace App\Http\Controllers;

use App\Models\Solicitud;
use App\Models\Paquete;
use App\Models\HistorialSeguimientoDonacione;
use App\Models\Vehiculo;
use App\Models\Solicitante;

use Illuminate\Http\Request;

class TrazabilidadController extends Controller
{
    public function porVoluntario(string $ci)
    {
        $solicitudes = Solicitud::where('ci_voluntario', $ci)
            ->with('solicitante')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($s) {
                return [
                    'id_solicitud'       => $s->id_solicitud,
                    'codigo_seguimiento' => $s->codigo_seguimiento,
                    'accion'             => $s->aprobada ? 'aprobada' : 'negada',
                    'fecha'              => $s->updated_at->format('d-m-Y'),
                    'justificacion'      => $s->justificacion,
                ];
            });

        $paquetes = Paquete::where('id_encargado', $ci)
            ->with(['solicitud', 'estado'])
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(function ($p) {
                $historial = HistorialSeguimientoDonacione::where('id_paquete', $p->id_paquete)
                    ->with('ubicacion')
                    ->orderBy('fecha_actualizacion', 'asc')
                    ->get()
                    ->map(function ($h) {
                        return [
                            'fecha'     => $h->fecha_actualizacion,
                            'estado'    => $h->estado,
                            'ubicacion' => $h->ubicacion->zona ?? null,
                            'lat'       => optional($h->ubicacion)->latitud,
                            'lng'       => optional($h->ubicacion)->longitud,
                            'conductor' => $h->conductor_nombre,
                            'vehiculo'  => $h->vehiculo_placa,
                            'imagen'    => $h->imagen_evidencia ? asset('storage/' . $h->imagen_evidencia) : null,
                        ];
                    });

                return [
                    'id_paquete'       => $p->id_paquete,
                    'estado_actual'    => optional($p->estado)->nombre_estado,
                    'codigo'           => optional($p->solicitud)->codigo_seguimiento,
                    'creado'           => $p->created_at->format('d/m/Y'),
                    'historial'        => $historial,
                ];
            });

        return response()->json([
            'success'       => true,
            'ci_voluntario' => $ci,
            'solicitudes'   => $solicitudes,
            'paquetes'      => $paquetes,
        ]);
    }

    public function porCodigoPaquete(string $codigo)
    {
        $solicitud = Solicitud::with(['solicitante', 'destino', 'tipoEmergencia'])
            ->whereRaw('UPPER(codigo_seguimiento) = ?', [mb_strtoupper($codigo)])
            ->first();

        if (!$solicitud) {
            return response()->json([
                'success' => false,
                'message' => "No se encontró ninguna solicitud con código {$codigo}.",
            ], 404);
        }

        $paquete = Paquete::with([
                'estado',
                'conductor',
                'vehiculo.marcaVehiculo',
                'vehiculo.tipoVehiculo',
            ])
            ->where('id_solicitud', $solicitud->id_solicitud)
            ->first();

        if (!$paquete) {
            return response()->json([
                'success'   => true,
                'codigo'    => $codigo,
                'solicitud' => $solicitud,
                'paquete'   => null,
                'conductor_asignado' => null,
                'vehiculo_asignado'  => null,
                'historial'          => [],
            ]);
        }

        $historial = HistorialSeguimientoDonacione::with('ubicacion')
            ->where('id_paquete', $paquete->id_paquete)
            ->orderBy('fecha_actualizacion', 'asc')
            ->get()
            ->map(function ($h) {
                return [
                    'id'        => $h->id ?? $h->id_historial ?? null,
                    'fecha'     => $h->fecha_actualizacion,
                    'estado'    => $h->estado,
                    'zona'      => optional($h->ubicacion)->zona,
                    'lat'       => optional($h->ubicacion)->latitud,
                    'lng'       => optional($h->ubicacion)->longitud,
                    'conductor' => [
                        'nombre' => $h->conductor_nombre,
                        'ci'     => $h->conductor_ci,
                    ],
                    'vehiculo'  => [
                        'placa' => $h->vehiculo_placa,
                    ],
                    'imagen'    => $h->imagen_evidencia
                        ? asset('storage/' . $h->imagen_evidencia)
                        : null,
                ];
            });

        $conductorAsignado = null;
        if ($paquete->conductor) {
            $conductorAsignado = [
                'nombre' => trim(($paquete->conductor->nombre ?? '') . ' ' . ($paquete->conductor->apellido ?? '')),
                'ci'     => $paquete->conductor->ci,
            ];
        }
        $vehiculoAsignado = null;
        if ($paquete->vehiculo) {
            $vehiculoAsignado = [
                'placa' => $paquete->vehiculo->placa,
                'marca' => optional($paquete->vehiculo->marcaVehiculo)->nombre_marca,
                'tipo'  => optional($paquete->vehiculo->tipoVehiculo)->nombre_tipo_vehiculo,
            ];
        }

        return response()->json([
            'success'           => true,
            'codigo_paquete'    => $codigo,
            'solicitud'         => $solicitud,
            'paquete'           => $paquete,
            'conductor_asignado'=> $conductorAsignado,
            'vehiculo_asignado' => $vehiculoAsignado,
            'historial'         => $historial,
        ]);
    }

    public function porVehiculo(string $placa)
    {
        $vehiculo = Vehiculo::with(['marcaVehiculo', 'tipoVehiculo'])
            ->where('placa', $placa)
            ->first();

        if (!$vehiculo) {
            return response()->json([
                'success' => false,
                'message' => "No se encontró vehículo con placa {$placa}",
            ], 404);
        }

        $paquetes = Paquete::where('id_vehiculo', $vehiculo->id_vehiculo)
            ->with([
                'estado',
                'solicitud.solicitante',
                'solicitud.destino',
            ])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($p) {
                $historial = HistorialSeguimientoDonacione::where('id_paquete', $p->id_paquete)
                    ->with('ubicacion')
                    ->orderBy('fecha_actualizacion', 'asc')
                    ->get()
                    ->map(function ($h) {
                        return [
                            'fecha'     => $h->fecha_actualizacion,
                            'estado'    => $h->estado,
                            'ubicacion' => $h->ubicacion->zona ?? null,
                            'lat'       => optional($h->ubicacion)->latitud,
                            'lng'       => optional($h->ubicacion)->longitud,
                            'conductor' => $h->conductor_nombre,
                            'conductor_ci' => $h->conductor_ci,
                            'vehiculo_placa'  => $h->vehiculo_placa,
                            'imagen'    => $h->imagen_evidencia
                                ? asset('storage/' . $h->imagen_evidencia)
                                : null,
                        ];
                    });

                return [
                    'id_paquete'    => $p->id_paquete,
                    'codigo'        => $p->codigo ?? optional($p->solicitud)->codigo_seguimiento,
                    'estado_actual' => optional($p->estado)->nombre_estado,
                    'creado'        => $p->created_at->format('d/m/Y'),

                    'solicitud' => $p->solicitud ? [
                        'id_solicitud'       => $p->solicitud->id_solicitud,
                        'codigo_seguimiento' => $p->solicitud->codigo_seguimiento,
                        'cantidad_personas'  => $p->solicitud->cantidad_personas,
                        'tipo_emergencia'    => $p->solicitud->tipo_emergencia,
                        'comunidad'          => optional($p->solicitud->destino)->comunidad,
                        'provincia'          => optional($p->solicitud->destino)->provincia,
                    ] : null,

                    'historial' => $historial,
                ];
            });

        return response()->json([
            'success'  => true,
            'placa'    => $placa,
            'vehiculo' => [
                'id_vehiculo' => $vehiculo->id_vehiculo,
                'placa'       => $vehiculo->placa,
                'marca'       => optional($vehiculo->marcaVehiculo)->nombre_marca,
                'tipo'        => optional($vehiculo->tipoVehiculo)->nombre_tipo_vehiculo,
            ],
            'paquetes' => $paquetes,
        ]);
    }

    public function porSolicitante(string $ci)
    {
        $solicitante = Solicitante::where('ci', $ci)->first();

        if (!$solicitante) {
            return response()->json([
                'success' => false,
                'message' => 'Solicitante no encontrado',
            ], 404);
        }

        $solicitudes = Solicitud::where('id_solicitante', $solicitante->id_solicitante)
            ->with(['destino', 'tipoEmergencia'])
            ->orderByDesc('fecha_solicitud')
            ->get()
            ->map(function (Solicitud $s) {

                $paquetes = Paquete::where('id_solicitud', $s->id_solicitud)
                    ->with(['estado', 'conductor', 'vehiculo'])
                    ->orderBy('created_at', 'asc')
                    ->get()
                    ->map(function (Paquete $p) {

                        $historial = HistorialSeguimientoDonacione::where('id_paquete', $p->id_paquete)
                            ->with('ubicacion')
                            ->orderBy('fecha_actualizacion', 'asc')
                            ->get()
                            ->map(function (HistorialSeguimientoDonacione $h) {
                                return [
                                    'fecha'     => $h->fecha_actualizacion,
                                    'estado'    => $h->estado,
                                    'ubicacion' => optional($h->ubicacion)->zona,
                                    'lat'       => optional($h->ubicacion)->latitud,
                                    'lng'       => optional($h->ubicacion)->longitud,
                                    'conductor' => $h->conductor_nombre,
                                    'vehiculo'  => $h->vehiculo_placa,
                                    'imagen'    => $h->imagen_evidencia
                                        ? asset('storage/' . $h->imagen_evidencia)
                                        : null,
                                ];
                            });

                        $conductor = $p->conductor;
                        $vehiculo = $p->vehiculo;

                        return [
                            'id_paquete'     => $p->id_paquete,
                            'codigo_paquete' => $p->codigo,
                            'estado_actual'  => optional($p->estado)->nombre_estado,
                            'creado'         => optional($p->created_at)?->format('d/m/Y'),
                            'fecha_entrega'  => $p->fecha_entrega,
                            'conductor'      => $conductor ? [
                                'ci'     => $conductor->ci,
                                'nombre' => trim(($conductor->nombre ?? '') . ' ' . ($conductor->apellido ?? '')),
                            ] : null,
                            'vehiculo'       => $vehiculo ? [
                                'id_vehiculo' => $vehiculo->id_vehiculo,
                                'placa'       => $vehiculo->placa,
                            ] : null,
                            'historial'      => $historial,
                        ];
                    });

                $destino = $s->destino;
                $tipo    = $s->tipoEmergencia;

                return [
                    'id_solicitud'       => $s->id_solicitud,
                    'codigo_seguimiento' => $s->codigo_seguimiento,
                    'estado'             => $s->estado,
                    'fecha_solicitud'    => $s->fecha_solicitud,
                    'fecha_necesidad'    => $s->fecha_necesidad,
                    'tipo_emergencia'    => $tipo ? [
                        'id'     => $tipo->id_emergencia,
                        'nombre' => $tipo->emergencia,
                    ] : null,
                    'destino'            => $destino ? [
                        'comunidad' => $destino->comunidad,
                        'provincia' => $destino->provincia,
                        'direccion' => $destino->direccion,
                        'latitud'   => $destino->latitud,
                        'longitud'  => $destino->longitud,
                    ] : null,
                    'paquetes'           => $paquetes,
                ];
            });

        return response()->json([
            'success'        => true,
            'ci_solicitante' => $ci,
            'solicitante'    => [
                'id_solicitante' => $solicitante->id_solicitante,
                'nombre'         => $solicitante->nombre,
                'apellido'       => $solicitante->apellido,
                'email'          => $solicitante->email,
                'telefono'       => $solicitante->telefono,
            ],
            'solicitudes'    => $solicitudes,
        ]);
    }

    //SOLICITUDES POR PROVINCIA
     public function porProvincia(string $provincia)
    {
        $provinciaBuscada = mb_strtolower($provincia);

        $solicitudes = Solicitud::with(['destino', 'tipoEmergencia'])
            ->whereHas('destino', function ($q) use ($provinciaBuscada) {
                $q->whereRaw('LOWER(provincia) LIKE ?', ['%'.$provinciaBuscada.'%']);
            })
            ->orderByDesc('fecha_solicitud')
            ->get()
            ->map(function (Solicitud $s) {

                $paquetes = Paquete::where('id_solicitud', $s->id_solicitud)
                    ->with(['estado', 'conductor', 'vehiculo'])
                    ->orderBy('created_at', 'asc')
                    ->get()
                    ->map(function (Paquete $p) {

                        $historial = HistorialSeguimientoDonacione::where('id_paquete', $p->id_paquete)
                            ->with('ubicacion')
                            ->orderBy('fecha_actualizacion', 'asc')
                            ->get()
                            ->map(function (HistorialSeguimientoDonacione $h) {
                                return [
                                    'fecha'     => $h->fecha_actualizacion,
                                    'estado'    => $h->estado,
                                    'ubicacion' => optional($h->ubicacion)->zona,
                                    'lat'       => optional($h->ubicacion)->latitud,
                                    'lng'       => optional($h->ubicacion)->longitud,
                                    'conductor' => $h->conductor_nombre,
                                    'vehiculo'  => $h->vehiculo_placa,
                                    'imagen'    => $h->imagen_evidencia
                                        ? asset('storage/' . $h->imagen_evidencia)
                                        : null,
                                ];
                            });

                        $conductor = $p->conductor;
                        $vehiculo  = $p->vehiculo;

                        return [
                            'id_paquete'     => $p->id_paquete,
                            'codigo_paquete' => $p->codigo,
                            'estado_actual'  => optional($p->estado)->nombre_estado,
                            'creado'         => optional($p->created_at)?->format('d/m/Y'),
                            'fecha_entrega'  => $p->fecha_entrega,
                            'conductor'      => $conductor ? [
                                'ci'     => $conductor->ci,
                                'nombre' => trim(($conductor->nombre ?? '') . ' ' . ($conductor->apellido ?? '')),
                            ] : null,
                            'vehiculo'       => $vehiculo ? [
                                'id_vehiculo' => $vehiculo->id_vehiculo,
                                'placa'       => $vehiculo->placa,
                            ] : null,
                            'historial'      => $historial,
                        ];
                    });

                $destino = $s->destino;
                $tipo    = $s->tipoEmergencia;

                return [
                    'id_solicitud'       => $s->id_solicitud,
                    'codigo_seguimiento' => $s->codigo_seguimiento,
                    'estado'             => $s->estado,
                    'fecha_solicitud'    => $s->fecha_solicitud,
                    'fecha_necesidad'    => $s->fecha_necesidad,
                    'tipo_emergencia'    => $tipo ? [
                        'id'     => $tipo->id_emergencia,
                        'nombre' => $tipo->emergencia,
                    ] : null,
                    'destino'            => $destino ? [
                        'comunidad' => $destino->comunidad,
                        'provincia' => $destino->provincia,
                        'direccion' => $destino->direccion,
                        'latitud'   => $destino->latitud,
                        'longitud'  => $destino->longitud,
                    ] : null,
                    'paquetes'           => $paquetes,
                ];
            });

        return response()->json([
            'success'           => true,
            'provincia_busqueda'=> $provincia,
            'total_solicitudes' => $solicitudes->count(),
            'solicitudes'       => $solicitudes,
        ]);
    }

    public function codigosSolicitudes()
    {
        $solicitudes = Solicitud::select('id_solicitud', 'codigo_seguimiento', 'estado')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success'      => true,
            'total'        => $solicitudes->count(),
            'solicitudes'  => $solicitudes,
        ]);
    }

    public function placasVehiculos()
    {
        $vehiculos = Vehiculo::select('id_vehiculo', 'placa')
            ->orderBy('placa')
            ->get();

        return response()->json([
            'success'   => true,
            'total'     => $vehiculos->count(),
            'vehiculos' => $vehiculos,
        ]);
    }

    public function exponerUsuarios()
    {
        $cisVoluntarios = Solicitud::query()
            ->whereNotNull('ci_voluntario')
            ->pluck('ci_voluntario')
            ->map(fn($v) => trim((string)$v))
            ->filter()
            ->toArray();

        // $cisSolicitantes = Solicitante::query()
            // ->whereNotNull('ci')
           // ->pluck('ci')
           // ->map(fn($v) => trim((string)$v))
           // ->filter()
           // ->toArray();

        $cisConductores = HistorialSeguimientoDonacione::query()
            ->whereNotNull('conductor_ci')
            ->pluck('conductor_ci')
            ->map(fn($v) => trim((string)$v))
            ->filter()
            ->toArray();

        $lista = [];
        $seen = [];

        foreach (array_merge($cisVoluntarios, $cisConductores) as $ci) {
            if (!isset($seen[$ci])) {
                $seen[$ci] = true;
                $lista[] = $ci;
            }
        }

        return response()->json([
            'success'  => true,
            'total'    => count($lista),
            'lista_ci' => $lista,
        ]);
    }

}

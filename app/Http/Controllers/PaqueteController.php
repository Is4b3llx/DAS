<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\PaqueteActualizado;
use App\Mail\PaqueteEntregado;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

use App\Models\Reporte;
use Illuminate\Support\Str;
use App\Http\Requests\PaqueteRequest;
use App\Models\Paquete;
use App\Models\Estado;
use App\Models\HistorialSeguimientoDonacione;
use App\Models\Solicitud;
use App\Models\Ubicacion;
use App\Models\Conductor;
use App\Models\Vehiculo;
use App\Models\TipoLicencia;
use App\Models\Marca;
use App\Models\TipoVehiculo;
use Illuminate\Support\Facades\Cache;
use App\Mail\CodigoEntregaPaquete;
use App\Exports\PaqueteSeguimientoExport;
use Maatwebsite\Excel\Facades\Excel;

class PaqueteController extends Controller
{
    public function index(Request $request)
    {
        $paquetesQuery = Paquete::with(['estado','solicitud.solicitante','solicitud.destino'])
            ->orderByRaw(
                "CASE WHEN LOWER((SELECT nombre_estado FROM estado WHERE estado.id_estado = paquete.estado_id)) = 'en camino' THEN 0 ELSE 1 END"
            )
            ->orderByDesc('updated_at');

        $paquetes = $paquetesQuery->paginate();
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $paquetes
            ]);
        }

        return view('paquete.index', compact('paquetes'))
            ->with('i', ($request->input('page', 1) - 1) * $paquetes->perPage());
    }

    public function pendientes()
    {
        $paquetesPendientes = Paquete::with([
                'estado',
                'solicitud.solicitante',
                'solicitud.destino',
            ])
            ->whereHas('estado', function ($query) {
                $query->whereRaw('LOWER(nombre_estado) = ?', ['pendiente']);
            })
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $paquetesPendientes,
        ]);
    }

    public function create(): View
    {
        $paquete      = new Paquete();
        $estados      = Estado::orderBy('nombre_estado')->pluck('nombre_estado','id_estado');
        $solicitudes  = Solicitud::with(['solicitante', 'destino'])->get();
        $conductores  = Conductor::orderBy('nombre')->orderBy('apellido')->get();
       
       $estadoEnCaminoIds = Estado::whereRaw('LOWER(nombre_estado) = ?', ['en camino'])
            ->pluck('id_estado');

        $conductoresOcupadosIds = Paquete::whereIn('estado_id', $estadoEnCaminoIds)
            ->whereNotNull('id_conductor')
            ->pluck('id_conductor');

        $conductores = Conductor::orderBy('nombre')->orderBy('apellido')
            ->when($conductoresOcupadosIds->isNotEmpty(), function ($q) use ($conductoresOcupadosIds) {
                $q->whereNotIn('conductor_id', $conductoresOcupadosIds);
            })
            ->get();

        $vehiculosBloqueadosIds = Paquete::whereIn('estado_id', $estadoEnCaminoIds)
            ->whereNotNull('id_vehiculo')
            ->select('id_vehiculo', DB::raw('COUNT(*) as total'))
            ->groupBy('id_vehiculo')
            ->havingRaw('COUNT(*) >= 3')
            ->pluck('id_vehiculo');

        $vehiculoActualId = $paquete->id_vehiculo;

        $vehiculos = Vehiculo::with(['marcaVehiculo','tipoVehiculo'])
            ->when($vehiculosBloqueadosIds->isNotEmpty(), function ($q) use ($vehiculosBloqueadosIds, $vehiculoActualId) {
                $ids = $vehiculosBloqueadosIds;

                if ($vehiculoActualId) {
                    $ids = $ids->filter(function ($id) use ($vehiculoActualId) {
                        return (int) $id !== (int) $vehiculoActualId;
                    });
                }

                if ($ids->isNotEmpty()) {
                    $q->whereNotIn('id_vehiculo', $ids);
                }
            })
            ->orderBy('capacidad_aproximada')
            ->get();


        $licencias    = TipoLicencia::orderBy('licencia')->get();
        $marcas       = Marca::orderBy('nombre_marca')->get();
        $tiposVehiculo = TipoVehiculo::orderBy('nombre_tipo_vehiculo')->get();

        return view('paquete.create', compact(
            'paquete',
            'estados',
            'solicitudes',
            'conductores',
            'vehiculos',
            'licencias',
            'marcas',
            'tiposVehiculo'
        ));
    }

    public function store(PaqueteRequest $request)  
    {
        DB::beginTransaction();
        try{

            $data = $request->validated();

            $data['fecha_entrega']   = null;
            $data['id_encargado']     = Auth::user()->ci;
            $data['codigo']           = $this->makeCodigoPaquete();

            $estadoIdNuevo = (int) ($data['estado_id'] ?? 0);
            $estadoNuevo   = Estado::find($estadoIdNuevo);
            $nombreEstadoNuevo = optional($estadoNuevo)->nombre_estado;

            $esEnCaminoNuevo = $nombreEstadoNuevo &&
                (strcasecmp($nombreEstadoNuevo, 'En Camino') === 0 || strcasecmp($nombreEstadoNuevo, 'En camino') === 0);

            if ($esEnCaminoNuevo && !empty($data['id_conductor']) && !empty($data['id_vehiculo'])) {
                $conductorId = (int) $data['id_conductor'];
                $vehiculoId  = (int) $data['id_vehiculo'];

                $estadoEnCaminoIds = Estado::whereRaw('LOWER(nombre_estado) = ?', ['en camino'])
                    ->pluck('id_estado');

                $existeConflicto = Paquete::whereIn('estado_id', $estadoEnCaminoIds)
                    ->where('id_conductor', $conductorId)
                    ->whereNotNull('id_vehiculo')
                    ->where('id_vehiculo', '!=', $vehiculoId)
                    ->exists();

                if ($existeConflicto) {
                    DB::rollBack();

                    $mensaje = 'El conductor seleccionado ya está asignado a otro vehículo con paquetes en estado "En Camino". '
                            . 'Selecciona otro conductor o vehículo.';

                    if ($request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'errors'  => ['id_conductor' => [$mensaje]],
                        ], 422);
                    }

                    return back()
                        ->withErrors(['id_conductor' => $mensaje])
                        ->withInput();
                }
            }

            if ($request->hasFile('imagen')) {
                $data['imagen'] = $request->file('imagen')->store('paquetes', 'public');
            }

            /** @var \App\Models\Paquete $paq */
            $paq = Paquete::create($data);

            $estadoNombre = optional($paq->estado)->nombre_estado ?? 'Pendiente';

            if (strcasecmp($estadoNombre, 'Entregada') === 0 || strcasecmp($estadoNombre, 'Entregado') === 0) {
                $paq->update(['fecha_entrega' => now()->toDateString()]);
            }

            $lat  = $request->input('latitud');
            $lng  = $request->input('longitud');
            $zona = $request->input('zona');

            $ubicacionId = null;
            if ($lat !== null && $lng !== null) {
                $ubic = Ubicacion::create([
                    'latitud'  => $lat,
                    'longitud' => $lng,
                    'zona'     => $zona,
                ]);
                $ubicacionId = $ubic->id_ubicacion;
            }

            $ubicacionString = $this->buildUbicacionString($zona, $lat, $lng);
            $paq->update(['ubicacion_actual' => $ubicacionString]);

            $conductorNombre = null;
            $conductorCi     = null;
            $vehiculoPlaca   = null;

            if ($paq->id_conductor) {
                $conductor = Conductor::find($paq->id_conductor);
                if ($conductor) {
                    $conductorNombre = trim(($conductor->nombre ?? '') . ' ' . ($conductor->apellido ?? ''));
                    $conductorCi     = $conductor->ci;
                }
            }

            if ($paq->id_vehiculo) {
                $vehiculo = Vehiculo::find($paq->id_vehiculo);
                if ($vehiculo) {
                    $vehiculoPlaca = $vehiculo->placa;
                }
            }

            HistorialSeguimientoDonacione::create([
                'ci_usuario'          => optional(Auth::user())->ci,
                'estado'              => $estadoNombre,
                'imagen_evidencia'    => $request->input('imagen_evidencia'),
                'id_paquete'          => $paq->id_paquete,
                'id_ubicacion'        => $ubicacionId,
                'fecha_actualizacion' => now(),
                'conductor_nombre'    => $conductorNombre,
                'conductor_ci'        => $conductorCi,
                'vehiculo_placa'      => $vehiculoPlaca,
            ]);

             DB::commit();
            $paqId = $paq->id_paquete;
        }catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $paq
            ], 201);
        }

        return Redirect::route('paquete.index')
            ->with('success', "Paquete creado (ID {$paq->id_paquete}).");
    }

    public function marcarArmado(Request $request, Paquete $paquete)
    {
        $estadoActual = optional($paquete->estado)->nombre_estado;
        if (!$estadoActual || strcasecmp(trim($estadoActual), 'Pendiente') !== 0) {
            return response()->json([
                'success' => false,
                'message' => 'Solo se puede armar paquetes pendientes.'
            ], 422);
        }

        $estadoArmado = Estado::whereRaw('LOWER(nombre_estado) = ?', ['armado'])->first();
        if (!$estadoArmado) {
            return response()->json([
                'success' => false,
                'message' => "El estado 'Armado' no existe. Créalo desde el CRUD de Estados."
            ], 422);
        }

        $request->validate([
            'ci_usuario'        => ['nullable','string','max:50'],
            'ubicacion_actual'  => ['nullable','string','max:255'],
        ]);

        DB::beginTransaction();
        try {
            $paquete->update([
                'estado_id'        => $estadoArmado->id_estado,
                'ubicacion_actual' => $request->filled('ubicacion_actual')
                    ? $request->input('ubicacion_actual')
                    : $paquete->ubicacion_actual,
            ]);

            $user = Auth::user();
            $conductorNombre = null;
            $conductorCi     = null;
            $vehiculoPlaca   = null;

            if ($paquete->id_conductor) {
                $conductorNombre = optional($paquete->conductor)->nombre;
                $conductorApellido = optional($paquete->conductor)->apellido;
                $conductorNombre = trim(($conductorNombre ?? '') . ' ' . ($conductorApellido ?? '')) ?: null;
                $conductorCi = optional($paquete->conductor)->ci;
            }

            if ($paquete->id_vehiculo) {
                $vehiculoPlaca = optional($paquete->vehiculo)->placa;
            }

            $ciUsuario = $request->input('ci_usuario') ?? optional($user)->ci;

            HistorialSeguimientoDonacione::create([
                'ci_usuario'          => $ciUsuario,
                'estado'              => 'Armado',
                'id_paquete'          => $paquete->id_paquete,
                'fecha_actualizacion' => now(),
                'imagen_evidencia'    => null,
                'id_ubicacion'        => $paquete->id_ubicacion,
                'conductor_nombre'    => optional($paquete->conductor)->nombre,
                'conductor_ci'        => optional($paquete->conductor)->ci,
                'vehiculo_placa'      => optional($paquete->vehiculo)->placa,
            ]);


            $paquete->load(['estado', 'solicitud.solicitante', 'solicitud.destino']);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return response()->json([
            'success' => true,
            'message' => 'Paquete actualizado a Armado.',
            'data'    => $paquete,
        ]);
    }

    private function makeCodigoPaquete(): string
    {
        for ($i = 0; $i < 10; $i++) {
            $code = 'D-' . str_pad((string) random_int(0, 999), 3, '0', STR_PAD_LEFT);
            if (!Paquete::where('codigo', $code)->exists()) {
                return $code;
            }
        }
        return 'D-' . substr((string) time(), -3);
    }
    private function buildUbicacionString($zona, $lat, $lng): string
    {
        $parts = [];
        if ($zona) $parts[] = $zona;
        if ($lat && $lng) $parts[] = "($lat, $lng)";
        return implode(' - ', $parts);
    }


    public function show(Request $request, $id)
    {
        $paquete = Paquete::with([
            'estado',
            'solicitud.solicitante',
            'solicitud.destino',
            'conductor',
            'vehiculo.marcaVehiculo',
            'vehiculo.tipoVehiculo',
            ])->findOrFail($id);
        
        $historial = HistorialSeguimientoDonacione::with('ubicacion')
        ->where('id_paquete', $paquete->id_paquete)
        ->orderBy('fecha_actualizacion', 'asc')
        ->get();

        if ($request->wantsJson()) {
            return response()->json([
                'success'   => true,
                'data'      => $paquete,
                'historial' => $historial,
            ]);
        }
        return view('paquete.show', compact('paquete', 'historial'));
    }

    public function exportExcel(Paquete $paquete)
    {
        $paquete->load([
            'estado',
            'solicitud.solicitante',
            'solicitud.destino',
            'conductor',
            'vehiculo.marcaVehiculo',
            'vehiculo.tipoVehiculo',
            'encargado',
        ]);

        $filename = sprintf('Reporte_Paquete_%s.xlsx', $paquete->id_paquete);

        return Excel::download(new PaqueteSeguimientoExport($paquete), $filename);
    }

    public function edit(Paquete $paquete): View
    {
        $paquete->load(['estado','solicitud.solicitante','solicitud.destino']);
        $estados      = Estado::orderBy('nombre_estado')->pluck('nombre_estado','id_estado');
        $solicitudes  = Solicitud::with(['solicitante', 'destino'])->get();
        $estadoEnCaminoIds = Estado::whereRaw('LOWER(nombre_estado) = ?', ['en camino'])
            ->pluck('id_estado');

        $conductoresOcupadosIds = Paquete::whereIn('estado_id', $estadoEnCaminoIds)
            ->whereNotNull('id_conductor')
            ->pluck('id_conductor');

        $conductorActualId = $paquete->id_conductor;
        $conductores = Conductor::orderBy('nombre')->orderBy('apellido')
            ->when($conductoresOcupadosIds->isNotEmpty(), function ($q) use ($conductoresOcupadosIds, $conductorActualId) {
                $ids = $conductoresOcupadosIds;

                if ($conductorActualId) {
                    $ids = $ids->filter(function ($id) use ($conductorActualId) {
                        return (int) $id !== (int) $conductorActualId;
                    });
                }

                if ($ids->isNotEmpty()) {
                    $q->whereNotIn('conductor_id', $ids);
                }
            })
            ->get();
        $vehiculosBloqueadosIds = Paquete::whereIn('estado_id', $estadoEnCaminoIds)
            ->whereNotNull('id_vehiculo')
            ->select('id_vehiculo', DB::raw('COUNT(*) as total'))
            ->groupBy('id_vehiculo')
            ->havingRaw('COUNT(*) >= 3')
            ->pluck('id_vehiculo')
            ->filter()
            ->values();

        
        $vehiculoActualId = $paquete->id_vehiculo;

        $vehiculos = Vehiculo::with(['marcaVehiculo','tipoVehiculo'])
            ->when($vehiculosBloqueadosIds->isNotEmpty(), function ($q) use ($vehiculosBloqueadosIds, $vehiculoActualId) {
                $ids = $vehiculosBloqueadosIds;

                if ($vehiculoActualId) {
                    $ids = $ids->reject(function ($id) use ($vehiculoActualId) {
                        return (int) $id === (int) $vehiculoActualId;
                    });
                }

                $idsArray = $ids->values()->all();

                if (!empty($idsArray)) {
                    $q->whereNotIn('id_vehiculo', $idsArray);
                }
            })
            ->orderBy('capacidad_aproximada')
            ->get();
        
        $licencias    = TipoLicencia::orderBy('licencia')->get();
        $marcas       = Marca::orderBy('nombre_marca')->get();
        $tiposVehiculo = TipoVehiculo::orderBy('nombre_tipo_vehiculo')->get();
        return view('paquete.edit', compact(
            'paquete',
            'estados',
            'solicitudes',
            'conductores',
            'vehiculos',
            'licencias',
            'marcas',
            'tiposVehiculo'
        ));
    }

    public function update(PaqueteRequest $request, Paquete $paquete)
    {
      DB::beginTransaction();

        try {

        $oldEstadoId = $paquete->estado_id;
        $newEstadoId = (int) $request->input('estado_id');

        $oldEstado = Estado::find($oldEstadoId);
        $newEstado = Estado::find($newEstadoId);

        $oldNombre = optional($oldEstado)->nombre_estado;
        $newNombre = optional($newEstado)->nombre_estado;

        $newIsEnCamino  = $newNombre &&
                (strcasecmp($newNombre, 'En Camino') === 0 || strcasecmp($newNombre, 'En camino') === 0);

        if ($newIsEnCamino) {
            $conductorIdFinal = (int) ($request->input('id_conductor') ?? $paquete->id_conductor);
            $vehiculoIdFinal  = (int) ($request->input('id_vehiculo') ?? $paquete->id_vehiculo);

            if ($conductorIdFinal && $vehiculoIdFinal) {
                $estadoEnCaminoIds = Estado::whereRaw('LOWER(nombre_estado) = ?', ['en camino'])
                    ->pluck('id_estado');

                $existeConflicto = Paquete::whereIn('estado_id', $estadoEnCaminoIds)
                    ->where('id_conductor', $conductorIdFinal)
                    ->whereNotNull('id_vehiculo')
                    ->where('id_vehiculo', '!=', $vehiculoIdFinal)
                    ->where('id_paquete', '!=', $paquete->id_paquete)
                    ->exists();

                if ($existeConflicto) {
                    DB::rollBack();

                    $mensaje = 'El conductor seleccionado ya está asignado a otro vehículo con paquetes en estado "En Camino". '
                            . 'Selecciona otro conductor o vehículo.';

                    if ($request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'errors'  => ['id_conductor' => [$mensaje]],
                        ], 422);
                    }

                    return back()
                        ->withErrors(['id_conductor' => $mensaje])
                        ->withInput();
                }
            }
        }

        if ($this->esEstadoEntregadoPorNombre($newNombre)) {
            $cacheKeyVerified = "paquete_entrega_verified_{$paquete->id_paquete}";
            $verified = Cache::get($cacheKeyVerified, false);

            if (!$verified) {
                DB::rollBack();

                $mensaje = 'Debes validar el código de entrega antes de marcar el paquete como "Entregado".';

                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'errors'  => ['codigo_entrega' => [$mensaje]],
                    ], 422);
                }

                return back()
                    ->withErrors(['codigo_entrega' => $mensaje])
                    ->withInput();
            }
            Cache::forget($cacheKeyVerified);
        }

        if ($oldNombre && $newNombre) {
            $oldIsPendiente = strcasecmp($oldNombre, 'Pendiente') === 0;
            $newIsPendiente = strcasecmp($newNombre, 'Pendiente') === 0;
            $newIsEnCamino  = strcasecmp($newNombre, 'En Camino') === 0
                        || strcasecmp($newNombre, 'En camino') === 0;

            if ($oldIsPendiente && !$newIsEnCamino) {
                DB::rollBack();

                $mensaje = 'Un paquete en estado "Pendiente" solo puede pasar a "En Camino".';

                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'errors'  => ['estado_id' => [$mensaje]],
                    ], 422);
                }

                return back()
                    ->withErrors(['estado_id' => $mensaje])
                    ->withInput();
            }

            if (!$oldIsPendiente && $newIsPendiente) {
                DB::rollBack();

                $mensaje = 'No puedes regresar el paquete al estado "Pendiente".';

                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'errors'  => ['estado_id' => [$mensaje]],
                    ], 422);
                }

                return back()
                    ->withErrors(['estado_id' => $mensaje])
                    ->withInput();
            }
        }

        $payload = $request->validated();
        $payload['id_encargado'] = optional(Auth::user())->ci ?? $paquete->id_encargado ?? null;

        if ($request->hasFile('imagen')) {
            $payload['imagen'] = $request->file('imagen')->store('paquetes', 'public');
            Log::info('Imagen actualizada:', [
                'ruta' => $payload['imagen'],
                'existe' => file_exists(public_path('storage/' . $payload['imagen']))
            ]);
        } else {
            unset($payload['imagen']);
            Log::info('No se proporcionó una nueva imagen.');
        }

        $paquete->update($payload);

        $estadoNombre = optional($paquete->estado)->nombre_estado ?? 'Pendiente';

        $entregado = $estadoNombre &&
            (strcasecmp($estadoNombre, 'Entregado') === 0 ||
            strcasecmp($estadoNombre, 'Entregada') === 0);
        if ($entregado) {
            if (is_null($paquete->fecha_entrega)) {
                $paquete->update(['fecha_entrega' => now()->toDateString()]);
            }
        } else {
            if (!is_null($paquete->fecha_entrega)) {
                $paquete->update(['fecha_entrega' => null]);
            }
        }

        $lat  = $request->input('latitud');
        $lng  = $request->input('longitud');
        $zona = $request->input('zona');

        $ubicacionId = null;

        if ($lat !== null && $lng !== null) {
            $ubic = Ubicacion::create([
                'latitud'  => $lat,
                'longitud' => $lng,
                'zona'     => $zona,
            ]);
            $ubicacionId = $ubic->id_ubicacion;
        }

        $ubicacionString = $this->buildUbicacionString($zona, $lat, $lng);
        $paquete->update(['ubicacion_actual' => $ubicacionString]);

        $conductorNombre = null;
        $conductorCi     = null;
        $vehiculoPlaca   = null;

        if ($paquete->id_conductor) {
            $conductor = Conductor::find($paquete->id_conductor);
            if ($conductor) {
                $conductorNombre = trim(($conductor->nombre ?? '') . ' ' . ($conductor->apellido ?? ''));
                $conductorCi     = $conductor->ci;
            }
        }

        if ($paquete->id_vehiculo) {
            $vehiculo = Vehiculo::find($paquete->id_vehiculo);
            if ($vehiculo) {
                $vehiculoPlaca = $vehiculo->placa;
            }
        }

        $pathEvidencia = null;
        if ($request->hasFile('imagen_evidencia')) {
            $pathEvidencia = $request->file('imagen_evidencia')->store('evidencias', 'public');
        }

        HistorialSeguimientoDonacione::create([
            'ci_usuario'          => optional(Auth::user())->ci,
            'estado'              => $estadoNombre,
            'imagen_evidencia'    => $pathEvidencia,
            'id_paquete'          => $paquete->id_paquete,
            'id_ubicacion'        => $ubicacionId,
            'fecha_actualizacion' => now(),
            'conductor_nombre'    => $conductorNombre,
            'conductor_ci'        => $conductorCi,
            'vehiculo_placa'      => $vehiculoPlaca,
        ]);
        DB::commit();
        
        $paquete->load([
            'estado',
            'solicitud.solicitante',
            'conductor',
            'vehiculo.marcaVehiculo',
            'vehiculo.tipoVehiculo',
        ]);

        $destinatario = optional(optional($paquete->solicitud)->solicitante)->email;
        if ($destinatario) {
            $estadoNombre = optional($paquete->estado)->nombre_estado;

            Log::info('Programando correos de paquete', [
                'paquete_id' => $paquete->id_paquete,
                'email'      => $destinatario,
                'estado'     => $estadoNombre,
            ]);
            $this->enviarMailPaquete($destinatario, new PaqueteActualizado($paquete));
            if ($estadoNombre && $this->esEstadoEntregadoPorNombre($estadoNombre)) {
                $this->enviarMailPaquete($destinatario, new PaqueteEntregado($paquete));
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data'    => $paquete,
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

    } catch (\Throwable $e) {
        DB::rollBack();
        throw $e;
    }

    return Redirect::route('paquete.index')->with('success', 'Paquete actualizado y registrado en historial.');
}


    public function destroy(Request $request, $id)
    {
        $paquete = Paquete::find($id);

        if (!$paquete) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error'   => 'Paquete no encontrado'
                ], 404);
            }

            return Redirect::route('paquete.index')
                ->with('error', 'Paquete no encontrado');
        }

        if ($paquete->imagen && Storage::disk('public')->exists($paquete->imagen)) {
            Storage::disk('public')->delete($paquete->imagen);
        }

        $paquete->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Paquete eliminado correctamente'
            ]);
        }

        return Redirect::route('paquete.index')
            ->with('success', 'Paquete eliminado correctamente');
    }

    public function storePdfReporte(Request $request, Paquete $paquete)
    {
        $validated = $request->validate([
            'archivo' => ['required', 'file', 'mimes:pdf', 'max:20480'],
            'fecha_reporte' => ['nullable', 'date'],
            'gestion' => ['nullable', 'string', 'max:255'],
        ]);

        $file = $request->file('archivo');
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeBase = Str::slug($originalName) ?: 'reporte-paquete-' . $paquete->id_paquete;
        $filename = $safeBase . '_' . now()->format('Ymd_His') . '.pdf';
        $storedPath = $file->storeAs('reportes', $filename, 'public');

        $reporte = Reporte::create([
            'id_paquete' => $paquete->id_paquete,
            'nombre_pdf' => $filename,
            'ruta_pdf' => $storedPath,
            'fecha_reporte' => $validated['fecha_reporte'] ?? now()->toDateString(),
            'gestion' => $validated['gestion'] ?? now()->format('Y'),
        ]);

        return response()->json([
            'success' => true,
            'reporte_id' => $reporte->id_reporte,
            'url' => asset('storage/' . $storedPath),
        ]);
    }

    public function galeria()
    {
        $paquetes = Paquete::with(['solicitud.solicitante', 'solicitud.destino']) ->whereHas('estado', function ($query) {
                $query->where('nombre_estado', 'Entregado');
            })
            ->get(); 
        Log::info('Paquetes entregados:', $paquetes->toArray());

        foreach ($paquetes as $paquete) {
            Log::info('Paquete:', [
                'id' => $paquete->id,
                'imagen' => $paquete->imagen,
                'ruta_completa' => public_path('storage/paquetes/' . $paquete->imagen),
                'existe' => file_exists(public_path('storage/paquetes/' . $paquete->imagen))
            ]);
        }
        return view('galeria.index', compact('paquetes'));
    }

    private function esEstadoEntregadoPorNombre(?string $nombre): bool
    {
        if (!$nombre) {
            return false;
        }

        $nombre = trim(mb_strtolower($nombre));
        return $nombre === 'entregado' || $nombre === 'entregada';
    }

    public function sendEntregaCode(Request $request, Paquete $paquete)
    {
        $estadoId = (int) $request->input('estado_id');
        $estado   = Estado::find($estadoId);
        $nombre   = optional($estado)->nombre_estado;

        if (!$this->esEstadoEntregadoPorNombre($nombre)) {
            return response()->json([
                'success' => false,
                'message' => 'El estado seleccionado no es "Entregado/Entregada".'
            ], 422);
        }

        $solicitante = optional($paquete->solicitud)->solicitante;
        $destinatario = optional($solicitante)->email;

        if (!$destinatario) {
            return response()->json([
                'success' => false,
                'message' => 'La solicitud asociada no tiene correo de contacto.'
            ], 422);
        }

        $codigo = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);

        $cacheKeyCode     = "paquete_entrega_code_{$paquete->id_paquete}";
        $cacheKeyVerified = "paquete_entrega_verified_{$paquete->id_paquete}";

        Cache::put($cacheKeyCode, $codigo, now()->addMinutes(15));
        Cache::forget($cacheKeyVerified); 

        Log::info('Programando correo de código de entrega', [
            'paquete_id' => $paquete->id_paquete,
            'email'      => $destinatario,
            'codigo'     => $codigo,
        ]);

        $this->enviarMailPaquete($destinatario, new CodigoEntregaPaquete($paquete, $codigo));

        return response()->json([
            'success' => true,
            'message' => 'Se ha enviado un código de verificación al solicitante.'
        ]);
    }


    public function verifyEntregaCode(Request $request, Paquete $paquete)
    {
        $request->validate([
            'codigo' => ['required','string','size:4'],
        ]);

        $inputCode = $request->input('codigo');

        $cacheKeyCode     = "paquete_entrega_code_{$paquete->id_paquete}";
        $cacheKeyVerified = "paquete_entrega_verified_{$paquete->id_paquete}";

        $storedCode = Cache::get($cacheKeyCode);

        if (!$storedCode || $storedCode !== $inputCode) {
            return response()->json([
                'success' => false,
                'message' => 'Código incorrecto o vencido. El paquete no fue entregado.'
            ], 422);
        }

        Cache::put($cacheKeyVerified, true, now()->addMinutes(30));
        Cache::forget($cacheKeyCode);

        return response()->json([
            'success' => true,
            'message' => 'Código validado. Ahora puedes confirmar la entrega del paquete.'
        ]);
    }

    private function enviarMailPaquete(string $destinatario, $mailable): void
    {
        try {
            $connection = config('queue.default');
            if ($connection && $connection !== 'sync') {
                Mail::to($destinatario)->queue($mailable);
            } else {
                Mail::to($destinatario)->send($mailable);
            }
        } catch (\Throwable $e) {
            Log::error('Error enviando correo de paquete', [
                'email'    => $destinatario,
                'mailable' => get_class($mailable),
                'error'    => $e->getMessage(),
            ]);
        }
    }
public function showDestinoVoluntario(Request $request, string $codigo)
{
    $paquete = Paquete::with([
            'estado',
            'solicitud.destino',
            'encargado',
        ])
        ->whereHas('solicitud', function ($q) use ($codigo) {
            $q->where('codigo_seguimiento', $codigo);
        })
        ->first();

    if (!$paquete) {
        return response()->json([
            'success' => false,
            'message' => 'Paquete no encontrado para el código indicado.',
        ], 404);
    }

    $solicitud = optional($paquete->solicitud);
    $destino   = optional($solicitud->destino);
    $encargado = optional($paquete->encargado);

    $data = [
        'id_paquete' => $paquete->id_paquete,
        'codigo'     => $solicitud->codigo_seguimiento ?? null,
        'estado'     => optional($paquete->estado)->nombre_estado,

        'destino' => [
            'id_destino' => $destino->id_destino ?? null,
            'comunidad'  => $destino->comunidad ?? null,
            'direccion'  => $destino->direccion ?? null,
            'provincia'  => $destino->provincia ?? null,
            'latitud'    => $destino->latitud ?? null,
            'longitud'   => $destino->longitud ?? null,
        ],

        'encargado' => [
            'ci'       => $encargado->ci ?? $paquete->id_encargado,
            'nombre'   => $encargado->nombre ?? null,
            'apellido' => $encargado->apellido ?? null,
            'completo' => trim(($encargado->nombre ?? '').' '.($encargado->apellido ?? '')) ?: null,
        ],
    ];

    return response()->json([
        'success'        => true,
        'codigo_busqueda'=> $codigo,
        'data'           => $data,
    ]);
}

    }
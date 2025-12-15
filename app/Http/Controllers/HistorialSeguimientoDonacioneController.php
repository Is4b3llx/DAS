<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use App\Models\HistorialSeguimientoDonacione;
use App\Models\Paquete;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Requests\HistorialSeguimientoDonacioneRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Models\Conductor;
use App\Models\Vehiculo;

class HistorialSeguimientoDonacioneController extends Controller
{
    public function index(Request $request): View
    {
        $historialSeguimientoDonaciones = HistorialSeguimientoDonacione::with([
                'paquete.solicitud',
                'ubicacion',
            ])
            ->orderBy('fecha_actualizacion', 'desc')
            ->get()
            ->groupBy('id_paquete');

        return view('seguimiento.index', compact('historialSeguimientoDonaciones'));
    }

    public function create(): View
    {
        
        $historialSeguimientoDonacione = new HistorialSeguimientoDonacione();
        return view('seguimiento.create', compact('historialSeguimientoDonacione'));
    }
    private function buildSnapshotFromPaquete(Paquete $paq): array
    {
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

        return [
            'conductor_nombre' => $conductorNombre,
            'conductor_ci'     => $conductorCi,
            'vehiculo_placa'   => $vehiculoPlaca,
        ];
    }

    public function store(HistorialSeguimientoDonacioneRequest $request)
    {
        $paq = Paquete::with('estado')->findOrFail($request->input('id_paquete'));
        $estadoNombre = optional($paq->estado)->nombre_estado ?? 'Pendiente';

        $snapshot = $this->buildSnapshotFromPaquete($paq);

        $path = null;
        if ($request->hasFile('imagen_evidencia')) {
            $path = $request->file('imagen_evidencia')->store('evidencias', 'public');
        }

        $historial = HistorialSeguimientoDonacione::create(array_merge(
            $request->validated(),
            [
                'estado'              => $estadoNombre,
                'ci_usuario'          => optional(Auth::user())->ci,
                'fecha_actualizacion' => now(),
                'imagen_evidencia'    => $path,
            ],
            $snapshot
        ));

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data'    => $historial
            ]);
        }

        return Redirect::route('seguimiento.index')
            ->with('success', 'Seguimiento registrado.');
    }


   public function show($id): View
    {
        $historialSeguimientoDonacione = HistorialSeguimientoDonacione::with(['paquete'])->findOrFail($id);

        return view('seguimiento.show', compact('historialSeguimientoDonacione'));
    }


    public function edit($id): View
    {
        $historialSeguimientoDonacione = HistorialSeguimientoDonacione::find($id);
        return view('seguimiento.edit', compact('historialSeguimientoDonacione'));
    }

    public function update(HistorialSeguimientoDonacioneRequest $request, HistorialSeguimientoDonacione $historialSeguimientoDonacione)
    {
        $paq = Paquete::with('estado')->findOrFail(
            $request->input('id_paquete', $historialSeguimientoDonacione->id_paquete)
        );
        $estadoNombre = optional($paq->estado)->nombre_estado ?? 'Pendiente';
        $snapshot = $this->buildSnapshotFromPaquete($paq);

        $path = $historialSeguimientoDonacione->imagen_evidencia;

        if ($request->hasFile('imagen_evidencia')) {
            $path = $request->file('imagen_evidencia')->store('evidencias', 'public');
        }

        $historialSeguimientoDonacione->update(array_merge(
            $request->validated(),
            [
                'estado'              => $estadoNombre,
                'ci_usuario'          => optional(Auth::user())->ci ?? $historialSeguimientoDonacione->ci_usuario,
                'fecha_actualizacion' => now(),
                'imagen_evidencia'    => $path,
            ],
            $snapshot
        ));

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data'    => $historialSeguimientoDonacione
            ]);
        }

        return Redirect::route('seguimiento.index')
            ->with('success', 'Seguimiento actualizado.');
    }

    public function destroy($id): RedirectResponse
    {
        HistorialSeguimientoDonacione::find($id)?->delete();

        return Redirect::route('seguimiento.index')
            ->with('success', 'Seguimiento eliminado.');
    }

    public function tracking($id_paquete)
    {
        $ALMACEN_LAT  = -17.722196014285355;
        $ALMACEN_LNG  = -63.17460540787094;
        $ALMACEN_ZONA = 'Almacen de Donaciones - Campus Univalle';

        $paquete = Paquete::with([
            'solicitud.solicitante',
            'solicitud.destino',
            'conductor',
            'vehiculo.marcaVehiculo',
            'vehiculo.tipoVehiculo',
        ])->findOrFail($id_paquete);

        $historial = HistorialSeguimientoDonacione::with('ubicacion')
            ->where('id_paquete', $id_paquete)
            ->orderBy('fecha_actualizacion', 'asc')
            ->get();

        $norm = function ($v) {
            if ($v === null || $v === '') return null;
            $v = str_replace(',', '.', (string)$v);
            return is_numeric($v) ? (float)$v : null;
        };

        $points = [];

        if ($historial->count() > 0) {
            foreach ($historial as $h) {
                $estado = strtolower(trim((string) ($h->estado ?? '')));

                $lat  = $h->ubicacion ? $norm($h->ubicacion->latitud) : null;
                $lng  = $h->ubicacion ? $norm($h->ubicacion->longitud) : null;
                $zona = $h->ubicacion ? ($h->ubicacion->zona ?? null) : null;

                if ($estado === 'armado' && (!$lat || !$lng)) {
                    $lat  = $ALMACEN_LAT;
                    $lng  = $ALMACEN_LNG;
                    $zona = $ALMACEN_ZONA;
                }

                if ($lat !== null && $lng !== null) {
                    $points[] = [
                        'lat'   => $lat,
                        'lng'   => $lng,
                        'zona'  => $zona ?? '',
                        'fecha' => $h->fecha_actualizacion,
                        'estado'=> $h->estado,
                    ];
                }
            }
        } else {
            if ($paquete->ubicacion_actual && strpos($paquete->ubicacion_actual, '(') !== false) {
                preg_match('/\(([-0-9.]+),\s*([-0-9.]+)\)/', $paquete->ubicacion_actual, $m);
                if (count($m) == 3) {
                    $points[] = [
                        'lat'   => (float)$m[1],
                        'lng'   => (float)$m[2],
                        'zona'  => $paquete->zona ?? '',
                        'fecha' => $paquete->fecha_aprobacion ?? '',
                    ];
                }
            }
        }

        $imagenes = $historial
            ->whereNotNull('imagen_evidencia')
            ->sortByDesc('fecha_actualizacion')
            ->pluck('imagen_evidencia')
            ->map(fn($path) => asset('storage/' . $path));

        return view('seguimiento.tracking', compact(
            'paquete',
            'historial',
            'points',
            'imagenes'
        ));
    }

}

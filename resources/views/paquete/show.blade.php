@extends('adminlte::page')

@section('template_title')
    {{ $paquete->name ?? __('Mostrar') . " " . __('Paquete') }}
@endsection

@push('css')
<link
    rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
    crossorigin=""
>
@endpush

@push('js')
<script
    src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
    crossorigin=""
></script>
@endpush


@section('content')
@php
    $ubicacionActual = $paquete->ubicacion_actual ?? null;
    $lat = $lng = null;

    if ($ubicacionActual && preg_match('/\((-?\d+\.\d+),\s*(-?\d+\.\d+)\)/', $ubicacionActual, $matches)) {
        $lat = $matches[1];
        $lng = $matches[2];
    }
@endphp

    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); color: white; display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title"><i class="fas fa-box mr-2"></i>{{ __('Mostrar') }} Paquete</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-secondary btn-sm" id="btn-imprimir-reporte">
                                    <i class="fas fa-file-pdf"></i> Reporte PDF
                            </a>
                            <button id="excel-paquete-unico" class="btn btn-success btn-sm">
                                <i class="fas fa-file-excel"></i> Reporte Excel
                            </button>
                            @php
                            $paqueteExcel = [
                                'codigo' => $paquete->codigo ?? $paquete->solicitud->codigo_seguimiento ?? '—',
                                'destino' => optional($paquete->solicitud->destino)->comunidad ?? '—',
                                'provincia' => optional($paquete->solicitud->destino)->provincia ?? '—',
                                'direccion' => optional($paquete->solicitud->destino)->direccion ?? '—',
                                'conductor' => optional($paquete->conductor)->nombre ?? '—',
                                'estado' => optional($paquete->estado)->nombre_estado ?? '—',
                                'productos' => $paquete->solicitud->insumos_necesarios ?? '—',
                                'fecha_aprobacion' => $paquete->created_at ? \Carbon\Carbon::parse($paquete->created_at)->format('d/m/Y') : '—',
                                'solicitante' => optional($paquete->solicitud->solicitante)->nombre ?? '—',
                                'ci_solicitante' => optional($paquete->solicitud->solicitante)->ci ?? '—',
                                'contacto_solicitante' => optional($paquete->solicitud->solicitante)->telefono ?? '—',
                                'referencia' => $paquete->solicitud->nombre_referencia ?? '',
                                'contacto_referencia' => $paquete->solicitud->celular_referencia ?? '',
                            ];
                            @endphp
                            <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
                            <script>
                            window.addEventListener('DOMContentLoaded', function() {
                                const btn = document.getElementById('excel-paquete-unico');
                                if (!btn) return;
                                btn.addEventListener('click', function() {
                                    const p = @json($paqueteExcel);
                                    const wsData = [
                                        Object.keys(p),
                                        Object.values(p)
                                    ];
                                    const ws = XLSX.utils.aoa_to_sheet(wsData);
                                    const wb = XLSX.utils.book_new();
                                    XLSX.utils.book_append_sheet(wb, ws, 'Reporte');
                                    XLSX.writeFile(wb, 'paquete_'+(p.codigo||'reporte')+'.xlsx');
                                });
                            });
                            </script>
                            <a class="btn btn-primary btn-sm" href="{{ route('paquete.index') }}"> {{ __('Volver') }}</a>
                        </div>
                    </div>

                <div class="card-body bg-white">
                        <style>
                            #reporte-pdf { display: none; font-family: Arial, Helvetica, sans-serif; color: #222; }
                            #reporte-pdf .header {
                                display: flex; justify-content: space-between; align-items: center;
                                border-bottom: 2px solid #444; padding-bottom: 8px; margin-bottom: 12px;
                            }
                            #reporte-pdf .brand { font-size: 18px; font-weight: 700; letter-spacing: .5px; }
                            #reporte-pdf .title { font-size: 16px; font-weight: 600; }
                            #reporte-pdf .subtle { color: #555; font-size: 12px; }
                            #reporte-pdf .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px 16px; margin: 10px 0 14px; }
                            #reporte-pdf .grid .item { padding: 6px 8px; background: #f7f7f7; border: 1px solid #e3e3e3; border-radius: 4px; }
                            #reporte-pdf .section-title { font-size: 14px; font-weight: 600; margin: 10px 0 6px; }
                            #reporte-pdf table { width: 100%; border-collapse: collapse; }
                            #reporte-pdf thead th { background: #efefef; border: 1px solid #ddd; padding: 6px; font-size: 12px; text-align: left; }
                            #reporte-pdf tbody td { border: 1px solid #ddd; padding: 6px; font-size: 12px; }
                            #reporte-pdf .footer { margin-top: 12px; border-top: 1px dashed #bbb; padding-top: 6px; font-size: 11px; color: #666; }
                        </style>
                        <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const btn = document.getElementById('btn-imprimir-reporte');
                                const uploadUrl = "{{ route('paquete.reportes.pdf', $paquete->id_paquete) }}";
                                const csrfMeta = document.head.querySelector('meta[name="csrf-token"]');
                                const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

                                async function uploadPdfToServer(blob, filename) {
                                    if (!csrfToken || !uploadUrl) return;
                                    const formData = new FormData();
                                    formData.append('_token', csrfToken);
                                    formData.append('archivo', blob, filename);
                                    formData.append('fecha_reporte', new Date().toISOString().slice(0, 10));
                                    formData.append('gestion', new Date().getFullYear());
                                    try {
                                        await fetch(uploadUrl, {
                                            method: 'POST',
                                            body: formData,
                                            headers: {
                                                'X-Requested-With': 'XMLHttpRequest'
                                            }
                                        });
                                    } catch (error) {
                                        console.error('No se pudo guardar el PDF en el servidor', error);
                                    }
                                }

                                if (btn) {
                                    btn.addEventListener('click', function() {
                                        const element = document.getElementById('reporte-pdf');
                                        const opt = {
                                            margin: 10,
                                            filename: 'Reporte_Paquete_{{ $paquete->id_paquete }}.pdf',
                                            image: { type: 'jpeg', quality: 0.98 },
                                            html2canvas: { scale: 2 },
                                            jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
                                        };

                                        element.style.display = 'block';

                                        html2pdf().set(opt).from(element).outputPdf('blob')
                                            .then(blob => {
                                                uploadPdfToServer(blob, opt.filename);

                                                const url = URL.createObjectURL(blob);
                                                const link = document.createElement('a');
                                                link.href = url;
                                                link.download = opt.filename;
                                                document.body.appendChild(link);
                                                link.click();
                                                document.body.removeChild(link);
                                                setTimeout(() => URL.revokeObjectURL(url), 1000);
                                            })
                                            .catch(error => console.error('Error generando PDF', error))
                                            .finally(() => {
                                                element.style.display = 'none';
                                            });
                                    });
                                }
                            });
                        </script>

                        <div id="reporte-pdf">
                            <div class="header">
                                <div class="brand">DAS - Alas Chiquitanas</div>
                                <div class="subtle">Generado: {{ now()->format('d/m/Y') }}</div>
                            </div>
                            <div class="title">Reporte de Paquete N°{{ $paquete->id_paquete }}</div>

                            @php
                                $solicitud = optional($paquete->solicitud);
                                $solicitante = optional($solicitud->solicitante);
                                $destino = optional($solicitud->destino);
                                $estadoSolicitud = $solicitud->estado ?? ($solicitud->aprobada === true ? 'aprobada' : ($solicitud->aprobada === false ? 'negada' : null));
                            @endphp

                            <div class="section-title">Solicitud</div>
                            <table style="margin-bottom:14px;">
                                <thead>
                                    <tr>
                                        <th style="width:180px;">Campo</th>
                                        <th>Valor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>ID Solicitud</td>
                                        <td>{{ $solicitud->codigo_seguimiento ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Estado Solicitud</td>
                                            <td>{{ 
                                                \Illuminate\Support\Str::headline($estadoSolicitud ?? 'pendiente') 
                                            }}</td>
                                    </tr>
                                    <tr>
                                        <td>Tipo Emergencia</td>
                                        <td>{{ $solicitud->tipo_emergencia ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <td>CI del Solicitante</td>
                                        <td>{{ $solicitante->ci ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Nombre del Solicitante</td>
                                        <td>{{ trim(($solicitante->nombre ?? '').' '.($solicitante->apellido ?? '')) ?: '—' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Comunidad Destino</td>
                                        <td>{{ $destino->comunidad ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Fecha Creación Solicitud</td>
                                        <td>{{ ($solicitud->fecha_creacion ?? $solicitud->created_at) ? \Carbon\Carbon::parse($solicitud->fecha_creacion ?? $solicitud->created_at)->format('d/m/Y') : '—' }}</td>
                                    </tr>
                                </tbody>
                            </table>

                            <div class="section-title">Seguimiento</div>
                            @php
                                $estadoSeguimiento = optional($paquete->estado)->nombre_estado;
                                $estadoSeguimientoLower = $estadoSeguimiento ? strtolower($estadoSeguimiento) : null;
                                $fechaEntregaPdfSource = $paquete->fecha_entrega;

                                if (!$fechaEntregaPdfSource && $estadoSeguimientoLower && \Illuminate\Support\Str::contains($estadoSeguimientoLower, 'entreg')) {
                                    $fechaEntregaPdfSource = $paquete->updated_at;
                                }

                                $fechaEntregaPdf = $fechaEntregaPdfSource
                                    ? ($fechaEntregaPdfSource instanceof \Carbon\Carbon
                                        ? $fechaEntregaPdfSource->format('d/m/Y')
                                        : \Carbon\Carbon::parse($fechaEntregaPdfSource)->format('d/m/Y'))
                                    : null;

                                if ($fechaEntregaPdf && $estadoSeguimientoLower && \Illuminate\Support\Str::contains($estadoSeguimientoLower, 'entreg')) {
                                    $estadoSeguimiento = trim(($estadoSeguimiento ?? 'Entregado') . ' - ' . $fechaEntregaPdf);
                                }

                                $historial = $historial ?? collect();
                            @endphp

                            @if($historial->count())
                                @foreach($historial as $h)
                                    @php
                                        $ubic = optional($h->ubicacion);

                                        $fechaHora = $h->fecha_actualizacion
                                            ? (\Carbon\Carbon::parse($h->fecha_actualizacion)->format('d/m/Y H:i'))
                                            : '—';

                                        $lat = $ubic->latitud ?? null;
                                        $lng = $ubic->longitud ?? null;
                                        $hasCoords = $lat !== null && $lng !== null;

                                        $textoConductor = $h->conductor_nombre
                                            ? $h->conductor_nombre . ($h->conductor_ci ? ' (CI '.$h->conductor_ci.')' : '')
                                            : null;
                                    @endphp

                                    <table style="margin-bottom:12px;">
                                        <thead>
                                            <tr>
                                                <th style="width:180px;">Campo</th>
                                                <th>Valor</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Estado</td>
                                                <td>{{ $h->estado ?? '—' }}</td>
                                            </tr>
                                            <tr>
                                                <td>Vehículo</td>
                                                <td>{{ $h->vehiculo_placa ?? '—' }}</td>
                                            </tr>
                                            <tr>
                                                <td>Conductor</td>
                                                <td>{{ $textoConductor ?? '—' }}</td>
                                            </tr>
                                            <tr>
                                                <td>Ubicación</td>
                                                <td>
                                                    @if($hasCoords)
                                                        <a href="https://www.google.com/maps?q={{ $lat }},{{ $lng }}" target="_blank">
                                                            {{ $ubic->zona ?? 'Sin dirección' }} ({{ $lat }}, {{ $lng }})
                                                        </a>
                                                    @else
                                                        {{ $ubic->zona ?? '—' }}
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Fecha de reporte</td>
                                                <td>{{ $fechaHora }}</td>
                                            </tr>
                                            <tr>
                                                <td>Voluntario Encargado</td>
                                                <td>
                                                    @if($h->ci_usuario)
                                                        CI {{ $h->ci_usuario }}
                                                    @else
                                                        —
                                                    @endif
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                @endforeach
                            @endif
                            <table style="margin-bottom:12px;">
                                <thead>
                                    <tr>
                                        <th style="width:180px;">Campo</th>
                                        <th>Valor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Estado</td>
                                        <td>{{ $estadoSeguimiento ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Vehículo</td>
                                        <td>
                                            {{ optional($paquete->vehiculo)->placa ?? '—' }}
                                            @php 
                                                $marca = optional(optional($paquete->vehiculo)->marcaVehiculo)->nombre_marca;
                                            @endphp
                                            @if($marca)
                                                - {{ $marca }}
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Conductor</td>
                                        <td>
                                            {{ trim(($paquete->conductor->nombre ?? '').' '.($paquete->conductor->apellido ?? '')) ?: '—' }}
                                            @if($paquete->conductor->ci ?? false)
                                                (CI {{ $paquete->conductor->ci }})
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Ubicación Actual</td>
                                        <td>{{ $paquete->ubicacion_actual ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Fecha de reporte</td>
                                        <td>{{ now()->format('d/m/Y') }}</td>
                                    </tr>
                                    <tr>
                                        <td>Voluntario Encargado</td>
                                        <td>
                                            @php $encargado = $paquete->encargado; @endphp
                                            {{ $encargado ? $encargado->nombre . ' ' . $encargado->apellido : '—' }}
                                            @if($paquete->id_encargado) - CI {{ $paquete->id_encargado }}@endif
                                        </td>
                                    </tr>
                                </tbody>
                            </table>


                            <div class="footer">Documento generado desde el sistema DAS.</div>
                        </div>
                @php
                    $destinoModel = optional(optional($paquete->solicitud)->destino);
                    $destLatRaw   = $destinoModel->latitud;
                    $destLngRaw   = $destinoModel->longitud;

                    $destLat = $destLatRaw !== null ? str_replace(',', '.', $destLatRaw) : null;
                    $destLng = $destLngRaw !== null ? str_replace(',', '.', $destLngRaw) : null;

                    $hasDestinoCoords = is_numeric($destLat) && is_numeric($destLng);
                @endphp

                        <div class="form-group mb-2 mb20">
                            <strong>Código de Solicitud:</strong>
                            {{ $paquete->solicitud->codigo_seguimiento ?? '—' }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Nombre del Solicitante:</strong>
                            {{ optional($paquete->solicitud->solicitante)->nombre }} {{ optional($paquete->solicitud->solicitante)->apellido }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>CI:</strong>
                            {{ optional($paquete->solicitud->solicitante)->ci }}
                        </div>
                        @if($paquete->solicitud->nombre_referencia)
                        <div class="form-group mb-2 mb20">
                            <strong>Contacto de Referencia:</strong>
                            {{ $paquete->solicitud->nombre_referencia }} 
                            @if($paquete->solicitud->celular_referencia)
                                - {{ $paquete->solicitud->celular_referencia }}
                            @endif
                        </div>
                        @endif
                        
                        <div class="form-group mb-2 mb20">
                            <strong>Estado:</strong>
                            {{ optional($paquete->estado)->nombre_estado ?? '—' }}
                        </div>

                        <div class="form-group mb-2 mb20">
                            <strong>Fecha de Creacion:</strong>
                            {{ \Carbon\Carbon::parse($paquete->created_at)->format('d/m/Y') }}
                        </div>
                        @php
                            $estadoNombreDetalle = optional($paquete->estado)->nombre_estado;
                            $isEntregadoDetalle = $estadoNombreDetalle
                                ? \Illuminate\Support\Str::contains(strtolower($estadoNombreDetalle), 'entreg   ')
                                : false;
                            $fechaEntregaSource = $paquete->fecha_entrega ?: ($isEntregadoDetalle ? $paquete->updated_at : null);
                            $fechaEntregaDetalle = $fechaEntregaSource
                                ? ($fechaEntregaSource instanceof \Carbon\Carbon
                                    ? $fechaEntregaSource->format('d/m/Y')
                                    : \Carbon\Carbon::parse($fechaEntregaSource)->format('d/m/Y'))
                                : null;
                        @endphp
                        @if($fechaEntregaDetalle)
                        <div class="form-group mb-2 mb20">
                            <strong>Fecha Entrega:</strong>
                            {{ $fechaEntregaDetalle ?? '—' }}
                        </div>
                        @endif
                        @php
                            $conductor = optional($paquete->conductor);
                            $vehiculo = optional($paquete->vehiculo);
                            $marca    = optional($vehiculo->marcaVehiculo);
                        
                        @endphp

                        @if($conductor->conductor_id || $vehiculo->id_vehiculo)
                        <div class="form-group mb-2 mb20">
                            <strong>Conductor:</strong>
                            @if($conductor->conductor_id)
                                {{ trim(($conductor->nombre ?? '').' '.($conductor->apellido ?? '')) ?: 'Sin nombre' }}
                                @if($conductor->ci)
                                  - CI {{ $conductor->ci }}
                                @endif
                            @else
                                —
                            @endif
                        </div>

                        <div class="form-group mb-2 mb20">
                            <strong>Vehículo:</strong>
                            @if($vehiculo->id_vehiculo)
                                {{ $vehiculo->placa ?? 'Sin placa' }}
                                 @if($marca->id_marca || !empty($vehiculo->modelo)) 
                                    — {{ $marca->nombre_marca ?? $marca->nombre ?? 'Sin marca' }}
                                    {{ $vehiculo->modelo ?? '' }}
                                    {{ $vehiculo->color ?? '' }}
                                @endif
                            @else
                                —
                            @endif
                        </div>
                        @endif

                        <div class="form-group mb-2 mb20">
                            <strong>Voluntario Encargado: </strong>
                            @php $encargado = $paquete->encargado; @endphp
                            {{ $encargado ? $encargado->nombre . ' ' . $encargado->apellido : 'El voluntario se asigna al iniciar la ruta' }}
                            @if($paquete->id_encargado) - CI {{ $paquete->id_encargado }}@endif
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Ubicacion Actual:</strong>
                            {{ $paquete->ubicacion_actual ?? 'La ubicación se ingresa cuando inicia la ruta'}}
                        </div>

                       @if($lat !== null && $lng !== null)
                            <div class="form-group mb-2 mb20">
                                <strong>Ubicación Actual del Paquete:</strong>
                                <div id="mapa-paquete"
                                    data-lat="{{ $lat }}"
                                    data-lng="{{ $lng }}"
                                    style="height: 300px; border-radius: 8px; overflow: hidden; border: 1px solid #ddd;">
                                </div>
                            </div>
                        @endif
                        @if($hasDestinoCoords)
                            <div class="form-group mb-2 mb20">
                                <strong>Destino en Google Maps:</strong>
                                <a href="https://www.google.com/maps?q={{ $destLat }},{{ $destLng }}"
                                target="_blank"
                                class="btn btn-warning btn-sm ml-2">
                                    Ver Destino
                                </a>
                                
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </section>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const mapDiv = document.getElementById('mapa-paquete');
    if (!mapDiv) return;

    const lat = parseFloat(mapDiv.dataset.lat);
    const lng = parseFloat(mapDiv.dataset.lng);
    if (isNaN(lat) || isNaN(lng)) return;

    const destinoLatRaw = @json(optional(optional($paquete->solicitud)->destino)->latitud);
    const destinoLngRaw = @json(optional(optional($paquete->solicitud)->destino)->longitud);

    const normalizeCoord = (v) => {
        if (v === null || v === undefined) return NaN;
        return parseFloat(String(v).replace(',', '.'));
    };

    const destinoLat = normalizeCoord(destinoLatRaw);
    const destinoLng = normalizeCoord(destinoLngRaw);
    const hasDestino = Number.isFinite(destinoLat) && Number.isFinite(destinoLng);

    const map = L.map('mapa-paquete').setView([lat, lng], 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const actualMarker = L.marker([lat, lng], { draggable: false })
        .addTo(map)
        .bindPopup('Ubicación actual');

    let destinoMarker = null;
    if (hasDestino) {
        const destinoIcon = L.divIcon({
            className: 'bg-transparent',
            html: `
                <div style="
                    background:#dc3545;
                    border:2px solid #dc3545;
                    border-radius:50%;
                    width:34px;height:34px;
                    display:flex;align-items:center;justify-content:center;
                    box-shadow:0 3px 8px rgba(0,0,0,.35);
                ">
                    <i class="fas fa-map-marker-alt" style="color:#fff;font-size:16px;"></i>
                </div>
            `,
            iconSize: [34, 34],
            iconAnchor: [17, 17]
        });

        destinoMarker = L.marker([destinoLat, destinoLng], { icon: destinoIcon, zIndexOffset: 1000 })
            .addTo(map);

        destinoMarker.bindTooltip('Destino Final', {
            permanent: true,
            direction: 'top',
            offset: [0, -16],
            opacity: 0.65
        });
        const bounds = L.latLngBounds([[lat, lng], [destinoLat, destinoLng]]);
        map.fitBounds(bounds, { padding: [30, 30] });
    } else {
        map.setView([lat, lng], 15);
    }
});
</script>
@endpush


@endsection

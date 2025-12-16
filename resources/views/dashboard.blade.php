@extends('adminlte::page')

@section('title', 'Estadisticas')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Estadísticas <small>Panel de Control</small></h1>
        <div class="btn-group">
            <button class="btn btn-outline-primary" data-toggle="modal" data-target="#dashboardFiltersModal">
                <i class="fas fa-filter"></i> Filtros
            </button>
            <button class="btn btn-primary" id="btn-refresh-dashboard">
                <i class="fas fa-sync-alt"></i> Recargar
            </button>
        </div>
    </div>
@stop

@section('content')

<div class="modal fade" id="dashboardFiltersModal" tabindex="-1" role="dialog" aria-labelledby="dashboardFiltersLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="dashboardFiltersLabel">Filtros de las Estadísticas</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="card card-outline card-info mb-3">
                    <div class="card-header py-2">
                        <h3 class="card-title mb-0">Rango de Fechas</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label class="d-block">Selecciona el periodo</label>
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                                        </div>
                                        <input type="date" class="form-control" id="filter-date-from">
                                    </div>
                                    <small class="text-muted">Desde</small>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="far fa-calendar-check"></i></span>
                                        </div>
                                        <input type="date" class="form-control" id="filter-date-to">
                                    </div>
                                    <small class="text-muted">Hasta</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-outline card-primary">
                    <div class="card-header py-2">
                        <h3 class="card-title mb-0">Solicitudes</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-0">
                            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap">
                                <label for="filter-solicitudes" class="mb-0">Mostrar por</label>
                                <div class="btn-group mt-2 mt-md-0" role="group" aria-label="Acciones filtro">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-toggle-list">
                                        <i class="fas fa-eye-slash"></i> Ocultar lista
                                    </button>
                                    <button type="button" class="btn btn-sm btn-success" id="btn-generar-reporte">
                                        <i class="fas fa-file-export"></i> Generar
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-success" id="btn-excel-solicitudes">
                                        <i class="fas fa-file-excel"></i> Excel
                                    </button>
                                </div>
                            </div>
                            <select id="filter-solicitudes" class="form-control mb-3">
                                <option value="comunidad">Comunidad</option>
                                <option value="aceptadas">Aceptadas</option>
                                <option value="negadas">Negadas</option>
                            </select>
                            <ul id="filter-solicitudes-result" class="list-group d-none">
                                <li class="list-group-item text-muted" id="filter-solicitudes-placeholder">
                                    Selecciona "Comunidad", "Aceptadas" o "Negadas" para ver la lista.
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="card card-outline card-success mt-3">
                    <div class="card-header py-2">
                        <h3 class="card-title mb-0">Paquetes</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-0">
                            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap">
                                <label for="filter-paquetes" class="mb-0">Mostrar por</label>
                                <div class="btn-group mt-2 mt-md-0" role="group" aria-label="Acciones paquetes">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-toggle-paquetes">
                                        <i class="fas fa-eye-slash"></i> Ocultar lista
                                    </button>
                                    <button type="button" class="btn btn-sm btn-success" id="btn-generar-paquetes">
                                        <i class="fas fa-file-export"></i> Generar
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-success" id="btn-excel-paquetes">
                                        <i class="fas fa-file-excel"></i> Excel
                                    </button>
                                </div>
                            </div>
                            <select id="filter-paquetes" class="form-control mb-3">
                                <option value="voluntarios">Voluntarios</option>
                                <option value="entregadas">Entregadas</option>
                                <option value="en_camino">En camino</option>
                                <option value="vehiculos">Vehículos</option>
                            </select>
                            <ul id="filter-paquetes-result" class="list-group d-none">
                                <li class="list-group-item text-muted">
                                    Selecciona "Voluntarios", "Entregadas", "En camino" o "Vehículos" para ver la lista.
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div id="dashboard-content">
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3 id="total-solicitudes">{{ $total }}</h3>
                <p>Solicitudes Totales</p>
            </div>
            <div class="icon">
                <i class="fas fa-file-alt"></i>
            </div>
            <a href="{{ url('solicitud') }}" class="small-box-footer">Más info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3 id="total-aceptadas">{{ $aceptadas }}</h3>
                <p>Aceptadas</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <a href="{{ url('solicitud') }}?estado=Aprobada" class="small-box-footer">Ver aceptadas <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3 id="total-rechazadas">{{ $rechazadas }}</h3>
                <p>Rechazadas</p>
            </div>
            <div class="icon">
                <i class="fas fa-times-circle"></i>
            </div>
            <a href="{{ url('solicitud') }}?estado=Rechazada" class="small-box-footer">Ver rechazadas <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3 id="tasa-aprobacion">{{ $tasa }}%</h3>
                <p>Tasa de Aprobación</p>
            </div>
            <div class="icon">
                <i class="fas fa-chart-pie"></i>
            </div>
            <a href="#" class="small-box-footer">Solicitudes aprobadas</a>
        </div>
    </div>
</div>


<div class="row">
    <div class="col-lg-6 col-6">
        <div class="small-box bg-purple">
            <div class="inner">
                <h3 id="total-voluntarios">{{ $totalVoluntarios }}</h3>
                <p>Total Voluntarios</p>
            </div>
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
            <a href="{{ url('usuario') }}" class="small-box-footer">Ver voluntarios <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>

    <div class="col-lg-6 col-6">
        <div class="small-box bg-teal">
            <div class="inner">
                <h3 id="voluntarios-conductores">{{ $voluntariosConductores }}</h3>
                <p>Conductores</p>
            </div>
            <div class="icon">
                <i class="fas fa-user-tie"></i>
            </div>
            <a href="{{ url('conductor') }}" class="small-box-footer">Ver conductores <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 d-flex">
        <div class="card mb-3  col-md-6">
            <div class="card-header border-0">
                <h3 class="card-title mb-0">Paquetes (Distribución)</h3>
            </div>
            <div class="card-body pt-2">
                <canvas id="solicitudesChart" style="min-height: 250px; height: 250px; max-height: 250px; width:100%;"></canvas>
            </div>
        </div>
        <div class="card mb-3 col-md-6">
            <div class="card-header border-0">
                <h3 class="card-title mb-0">Solicitudes por Provincia/Comunidad (Top 5)</h3>
            </div>
            <div class="card-body pt-2">
                <canvas id="solicitudesPorComunidadChart" style="min-height:250px;height:250px;width:100%;"></canvas>
            </div>
            <div class="modal fade" id="modalSolicitudesComunidad" tabindex="-1" role="dialog" aria-labelledby="modalSolicitudesComunidadLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title" id="modalSolicitudesComunidadLabel">Solicitudes de la comunidad</h5>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="d-flex justify-content-end mb-2">
                                <button id="btn-generar-reporte-provincia" class="btn btn-success btn-sm" style="display:none;">
                                    <i class="fas fa-file-pdf"></i> Generar reporte
                                </button>
                            </div>
                            <div id="tablaSolicitudesComunidadContainer">
                                <div class="text-center text-muted">Cargando...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-3 align-items-stretch">
    
    <div class="col-md-6 mb-4 d-flex">
        <div class="card w-100 h-100">
            <div class="card-header bg-primary">
                <h3 class="card-title text-white">Productos Más Pedidos (Top 5)</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th class="text-right">Veces pedido</th>
                        </tr>
                    </thead>
                    <tbody id="productos-tbody">
                        @forelse($productosMasPedidos as $producto => $cantidad)
                            <tr>
                                <td>{{ ucfirst($producto) }}</td>
                                <td class="text-right"><span class="badge badge-info">{{ $cantidad }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="text-center text-muted">No hay datos de productos.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    
    <div class="col-md-6 mb-4 d-flex">
        <div class="card w-100 h-100">
            <div class="card-header bg-warning">
                <h3 class="card-title">Estadísticas de Paquetes</h3>
            </div>
            <div class="card-body">

    {{-- Grid 2x2 de stats --}}
    <div class="row">
        <div class="col-6">
            <div class="info-box mb-3">
                <span class="info-box-icon bg-info"><i class="fas fa-box"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Paquetes</span>
                    <span class="info-box-number" id="total-paquetes">{{ $totalPaquetes }}</span>
                </div>
            </div>
        </div>
        <div class="col-6">
            <div class="info-box mb-3">
                <span class="info-box-icon bg-info"><i class="fas fa-stopwatch"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Promedio de Días de Entrega</span>
                    <span class="info-box-number">{{ $promedioEntrega }} días</span>
                </div>
            </div>
        </div>

        <div class="col-6">
            <div class="info-box mb-3">
                <span class="info-box-icon bg-success"><i class="fas fa-check"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Paquetes Entregados</span>
                    <span class="info-box-number" id="paquetes-entregados">{{ $paquetesEntregados }}</span>
                </div>
            </div>
        </div>

        <div class="col-6">
            <div class="info-box mb-3">
                <span class="info-box-icon bg-warning"><i class="fas fa-truck"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Paquetes En Camino</span>
                    <span class="info-box-number" id="paquetes-en-camino">{{ $paquetesEnCamino ?? 0 }}</span>
                </div>
            </div>
        </div>

        <div class="col-6">
            <div class="info-box mb-3">
                <span class="info-box-icon bg-secondary"><i class="fas fa-clock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Paquetes Pendientes</span>
                    <span class="info-box-number" id="paquetes-pendientes">{{ $paquetesPendientes ?? 0 }}</span>
                </div>
            </div>
        </div>
                <div class="col-6">
            <div class="info-box mb-3">
                <span class="info-box-icon bg-secondary"><i class="fas fa-box"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Paquetes Armados</span>
                    <span class="info-box-number" id="paquetes-armados">{{ $paquetesArmados ?? 0 }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-outline card-info mb-0">
        <div class="card-header py-2">
            <h3 class="card-title" style="font-size: 1rem;">Top Voluntarios que atendieron Paquetes</h3>
        </div>
        <div class="card-body p-0">
            <table class="table table-sm mb-0">
                <thead>
                    <tr>
                        <th>Voluntario</th>
                        <th class="text-right">Paquetes</th>
                    </tr>
                </thead>
                <tbody id="voluntarios-paquetes-tbody">
                    @forelse($topVoluntariosPaquetes as $v)
                        <tr>
                            <td>{{ $v['nombre'] }}<br><small class="text-muted">CI: {{ $v['ci'] }}</small></td>
                            <td class="text-right"><span class="badge badge-primary">{{ $v['total'] }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="text-center text-muted">Sin datos</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-secondary">
                <h3 class="card-title text-white">Paquetes: Tiempo de Entrega (últimos 10)</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th>Cod. Paquete</th>
                            <th>Fecha Creación</th>
                            <th>Fecha Entrega</th>
                            <th class="text-right">Días de Entrega</th>
                        </tr>
                    </thead>
                    <tbody id="paquetes-tbody">
                        @forelse($paquetes as $paq)
                            <tr>
                                <td><a href="{{ route('paquete.show', $paq->id_paquete) }}"> {{ optional($paq->solicitud)->codigo_seguimiento ?? $paq->id_paquete }}</a></td>
                                <td>{{ \Carbon\Carbon::parse($paq->fecha_create)->format('d/m/Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($paq->fecha_entrega)->format('d/m/Y') }}</td>
                                <td class="text-right">
                                    <span class="badge badge-{{ $paq->dias_entrega > 7 ? 'danger' : ($paq->dias_entrega > 3 ? 'warning' : 'success') }}" style="font-size: small;">
                                        {{ round($paq->dias_entrega, 1) }} días
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted">No hay paquetes con fechas de entrega.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer text-muted">
                <small>Paquetes ordenados por tiempo de entrega descendente.</small>
            </div>
        </div>
    </div>
</div>
</div>
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script>
let solicitudesChart = null;
const solicitudesData = {
    comunidad: @json($solicitudesPorComunidad),
    aceptadas: @json($solicitudesAceptadas),
    negadas: @json($solicitudesNegadas)
};
const paquetesData = {
    voluntarios: @json($voluntariosListado),    
    entregadas: @json($paquetesEntregadosListado),
    en_camino: @json($paquetesEnCaminoListado),
    vehiculos: @json($vehiculosListado)
};
let currentSolicitudesReport = null;
let currentPaquetesReport = null;
let solicitudesPorComunidadChart = null;

const solicitudLabelMap = {
    comunidad: 'Por comunidad',
    aceptadas: 'Aceptadas',
    negadas: 'Negadas'
};
const paquetesLabelMap = {
    voluntarios: 'Voluntarios',
    entregadas: 'Entregadas',
    en_camino: 'En camino',
    vehiculos: 'Vehículos'
};
const dashboardReportStoreUrl = @json(route('dashboard.reportes.store'));
const dashboardExcelExportUrl = @json(route('dashboard.reportes.excel'));
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

document.addEventListener('DOMContentLoaded', function() {
    $('#dashboardFiltersModal').on('shown.bs.modal', function() {
        const filterSelect = document.getElementById('filter-solicitudes');
        if (filterSelect) {
            // Genera y asigna el reporte actual al abrir el modal
            const type = filterSelect.value;
            let items = [];
            if (type === 'comunidad') {
                items = solicitudesData.comunidad || [];
            } else if (type === 'aceptadas') {
                items = solicitudesData.aceptadas || [];
            } else if (type === 'negadas') {
                items = solicitudesData.negadas || [];
            }
            const htmlContent = '';
            currentSolicitudesReport = buildReportObject('Solicitudes', type, items.length, htmlContent, { items });
        }
        const toggleListBtn = document.getElementById('btn-toggle-list');
        const resultList = document.getElementById('filter-solicitudes-result');
        if (toggleListBtn && resultList) {
            toggleListBtn.onclick = function() {
                listHidden = !listHidden;
                if (listHidden) {
                    resultList.classList.add('d-none');
                } else {
                    resultList.classList.remove('d-none');
                }
                updateToggleButton();
            };
            updateToggleButton();
        }
        const generateSolicitudesBtn = document.getElementById('btn-generar-reporte');
        if (generateSolicitudesBtn) {
            generateSolicitudesBtn.addEventListener('click', function() {
                const filterSelect = document.getElementById('filter-solicitudes');
                let htmlContent = '';
                let items = [];
                let type = '';
                if (filterSelect) {
                    type = filterSelect.value;
                    if (type === 'comunidad') {
                        items = solicitudesData.comunidad || [];
                        htmlContent = buildComunidadPdfCards(items);
                    } else if (type === 'aceptadas' || type === 'negadas') {
                        items = solicitudesData[type] || [];
                        htmlContent = buildSolicitudPdfCards(items);
                    }
                    currentSolicitudesReport = buildReportObject('Solicitudes', type, items.length, htmlContent, { items });
                }
                exportCurrentReport(currentSolicitudesReport, 'Solicitudes');
            });
        }
        const excelSolicitudesBtn = document.getElementById('btn-excel-solicitudes');
        if (excelSolicitudesBtn) {
            excelSolicitudesBtn.onclick = function() {
                exportReportToExcel(currentSolicitudesReport, 'Solicitudes');
            };
        }

        const togglePaquetesBtn = document.getElementById('btn-toggle-paquetes');
        const paquetesResultList = document.getElementById('filter-paquetes-result');
        const paquetesSelect = document.getElementById('filter-paquetes');
        if (togglePaquetesBtn && paquetesResultList) {
            togglePaquetesBtn.onclick = function() {
                paquetesListHidden = !paquetesListHidden;
                if (paquetesListHidden) {
                    paquetesResultList.classList.add('d-none');
                } else {
                    paquetesResultList.classList.remove('d-none');
                }
                updatePaquetesToggleButton();
            };
            updatePaquetesToggleButton();
        }
        const generatePaquetesBtn = document.getElementById('btn-generar-paquetes');
        if (generatePaquetesBtn) {
            generatePaquetesBtn.onclick = function() {
                exportCurrentReport(currentPaquetesReport, 'Paquetes');
            };
        }
        const excelPaquetesBtn = document.getElementById('btn-excel-paquetes');
        if (excelPaquetesBtn) {
            excelPaquetesBtn.onclick = function() {
                exportReportToExcel(currentPaquetesReport, 'Paquetes');
            };
        }
        if (paquetesSelect && paquetesResultList) {
            renderPaquetesList(paquetesSelect.value);
            paquetesResultList.classList.remove('d-none');
            paquetesListHidden = false;
            updatePaquetesToggleButton();
            paquetesSelect.onchange = function() {
                renderPaquetesList(this.value);
                paquetesResultList.classList.remove('d-none');
                paquetesListHidden = false;
                updatePaquetesToggleButton();
            };
        }
    });
    if (typeof Chart === 'undefined') {
        console.warn('Chart.js no está cargado. Activa el plugin Chartjs en config/adminlte.php');
    } else {
        const ctx = document.getElementById('solicitudesChart');
        if (ctx) {
            solicitudesChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Entregados', 'En camino', 'Pendientes', 'Armados'],
                    datasets: [{
                        data: [
                            {{ $paquetesEntregados }},
                            {{ $paquetesEnCamino ?? 0 }},
                            {{ $paquetesPendientes ?? 0 }},
                            {{ $paquetesArmados ?? 0 }}
                        ],
                        backgroundColor: ['#28a745', '#ffc107', '#6c757d', '#33a0ff']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: {
                        display: true,
                        position: 'bottom'
                    }
                }
            });
        }
    }
   
    const filterSelect = document.getElementById('filter-solicitudes');
    const resultList = document.getElementById('filter-solicitudes-result');
    const toggleListBtn = document.getElementById('btn-toggle-list');
    const paquetesSelect = document.getElementById('filter-paquetes');
    const paquetesResultList = document.getElementById('filter-paquetes-result');
    const togglePaquetesBtn = document.getElementById('btn-toggle-paquetes');
    const generateSolicitudesBtn = document.getElementById('btn-generar-reporte');
    const generatePaquetesBtn = document.getElementById('btn-generar-paquetes');
    const excelSolicitudesBtn = document.getElementById('btn-excel-solicitudes');
    const excelPaquetesBtn = document.getElementById('btn-excel-paquetes');
    const dateFromInput = document.getElementById('filter-date-from');
    const dateToInput = document.getElementById('filter-date-to');
    let listHidden = true;
    let paquetesListHidden = true;

    function formatForReport(dateStr) {
        if (!dateStr) return '';
        const [y, m, d] = dateStr.split('-');
        if (!y || !m || !d) return dateStr;
        return `${d}/${m}/${y}`;
    }

    function getDateRangeLabel() {
        const fromVal = dateFromInput ? dateFromInput.value : '';
        const toVal = dateToInput ? dateToInput.value : '';
        if (!fromVal && !toVal) return '';
        if (fromVal && toVal) return `${formatForReport(fromVal)} al ${formatForReport(toVal)}`;
        return fromVal ? `Desde ${formatForReport(fromVal)}` : `Hasta ${formatForReport(toVal)}`;
    }

    function buildReportObject(group, type, count, htmlContent, options = {}) {
        const reportTitleMap = {
        'Solicitudes:comunidad': 'Reporte de Solicitudes por Comunidad',
        'Solicitudes:aceptadas': 'Reporte de Solicitudes Aprobadas',
        'Solicitudes:negadas':   'Reporte de Solicitudes Rechazadas',
        'Paquetes:voluntarios': 'Reporte de Paquetes por Voluntario',
        'Paquetes:entregadas':  'Reporte de Paquetes Entregados',
        'Paquetes:en_camino':   'Reporte de Paquetes en Camino',
        'Paquetes:vehiculos':   'Reporte de Paquetes por Vehículo',
        };

        const labelMap = group === 'Solicitudes' ? solicitudLabelMap : paquetesLabelMap;
        const label = labelMap[type] || 'Listado';
        const rangeLabel = getDateRangeLabel();
        const key = `${group}:${type}`;
        const prettyTitle = reportTitleMap[key] || `${group} - ${label}`;

        return {
            group,
            type,
            count: typeof count === 'number' ? count : 0,
            title: prettyTitle,
            subtitle: rangeLabel ? `Rango: ${rangeLabel}` : 'Sin filtro de fechas',
            content: htmlContent,
            items: options.items || [],
            slug: `${group.toLowerCase()}_${(type || 'listado')}`.replace(/[^a-z0-9_]+/gi, '_').toLowerCase()
        };
    }

    function downloadBlob(blob, filename, fallbackMime = 'application/pdf') {
        try {
            let effectiveBlob = blob instanceof Blob ? blob : new Blob([blob], { type: fallbackMime });
            const url = URL.createObjectURL(effectiveBlob);
            const link = document.createElement('a');
            link.href = url;
            link.download = filename;
            link.style.display = 'none';
            document.body.appendChild(link);
            link.click();
            requestAnimationFrame(() => {
                URL.revokeObjectURL(url);
                link.remove();
            });
        } catch (error) {
            console.error('No se pudo iniciar la descarga del PDF.', error);
        }
    }

    async function uploadDashboardReport(blob, filename, meta = {}) {
        if (!dashboardReportStoreUrl) {
            return;
        }

        try {
            let effectiveBlob = blob;
            if (!(effectiveBlob instanceof Blob)) {
                effectiveBlob = new Blob([effectiveBlob], { type: 'application/pdf' });
            }

            const file = new File([effectiveBlob], filename, { type: 'application/pdf' });
            const formData = new FormData();
            formData.append('archivo', file);

            const fechaIso = meta.fechaIso || new Date().toISOString().slice(0, 10);
            const gestion = meta.gestion || String(new Date().getFullYear());

            formData.append('fecha_reporte', fechaIso);
            formData.append('gestion', gestion);

            const response = await fetch(dashboardReportStoreUrl, {
                method: 'POST',
                headers: csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {},
                body: formData,
                credentials: 'same-origin'
            });

            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(errorText || `Error HTTP ${response.status}`);
            }
        } catch (error) {
            console.error('No se pudo guardar el reporte generado en el historial.', error);
        }
    }

    function buildSolicitudPdfCards(items) {
    return items.map(item => `
        <li style="list-style:none; page-break-inside:avoid;">
            <div style="border:1px solid #d1d5db; border-radius:8px; padding:10px 12px; margin-bottom:10px;">
                <h3 style="margin:0 0 4px; font-size:1rem;">
                    ${item.codigo} · ${item.solicitante}
                </h3>
                <p style="margin:0 0 6px; font-size:0.9rem; color:#4b5563;">
                    Estado: ${item.estado} · Tipo: ${item.tipo_emergencia}
                </p>
                <div style="font-size:0.88rem;">
                    <strong>Fecha:</strong> ${item.fecha}<br>
                    <strong>Destino:</strong> ${item.comunidad || '-'}
                    <strong>Coordenadas:</strong> ${renderCoordsLink(item.latitud, item.longitud)}
                </div>
            </div>
        </li>
    `).join('');
}

    function buildComunidadPdfCards(items) {
        if (!items || !items.length) return '';
        return items.map(item => {
            const solicitudesList = (item.solicitudes || []).map((sol, index) => {
                const insumos = sol.insumos
                    ? sol.insumos.replace(/\n/g, '<br>')
                    : '<span style="color:#888;">Sin insumos especificados.</span>';
                const provincia = sol.provincia ? `, ${sol.provincia}` : '';
                return `
                    <div style="border:1px solid #e3e3e3; border-radius:8px; padding:9px 11px; margin-bottom:8px; background:#fbfbfb; page-break-inside: avoid; break-inside: avoid;">
                        <div style="display:flex; justify-content:space-between; align-items:center; gap:10px; font-size:0.9rem;">
                            <div><strong>#${index + 1} · ${sol.codigo}</strong></div>
                            <span style="font-size:0.82rem; color:#6b7280;">Inicio: ${sol.fecha_inicio || '-'}</span>
                        </div>
                        <div style="margin-top:4px; font-size:0.88rem; color:#1f2937;">
                            <strong>Tipo de emergencia:</strong> ${sol.tipo_emergencia || '-'} · <strong>Fecha solicitud:</strong> ${sol.fecha || '-'}
                        </div>
                        <div style="margin-top:4px; font-size:0.88rem; color:#1f2937;">
                            <strong>Solicitante:</strong> ${sol.solicitante || 'Sin solicitante'} (CI ${sol.solicitante_ci || '-'})<br>
                            <strong>Contacto:</strong> ${sol.solicitante_correo || '-'} · ${sol.solicitante_telefono || '-'}
                        </div>
                        <div style="margin-top:4px; font-size:0.88rem; color:#1f2937;">
                            <strong>Ubicación:</strong> ${sol.comunidad || 'Sin comunidad'}${provincia}<br>
                            <strong>Dirección:</strong> ${sol.direccion || '-'}
                        </div>
                        <div style="margin-top:6px; font-size:0.88rem; color:#1f2937;">
                            <strong>Insumos necesarios:</strong>
                            <div style="margin-top:2px; white-space:pre-wrap;">${insumos}</div>
                        </div>
                    </div>
                `;
            }).join('');

            return `
                <li style="list-style:none; page-break-inside: avoid; break-inside: avoid;">
                    <div style="border:1px solid #d1d5db; border-radius:9px; padding:12px; margin-bottom:10px; page-break-inside: avoid; break-inside: avoid;">
                        <h3 style="margin:0 0 4px; font-size:1.05rem;">${item.nombre}</h3>
                        <p style="margin:0 0 6px; color:#4b5563; font-size:0.9rem;">
                            Total de solicitudes: ${item.total} · Última: ${item.ultimaFecha || '-'}
                        </p>
                        ${item.provincia ? `<p style="margin:0 0 8px; color:#4b5563; font-size:0.88rem;">Provincia: ${item.provincia}</p>` : ''}
                        <div style="padding-left:0; margin:0;">${solicitudesList}</div>
                    </div>
                </li>
            `;
        }).join('');
    }

    function buildPaqueteEntregadoCards(items) {
        if (!items || !items.length) return '';
        return items.map(item => {
            const provincia = item.destino_provincia && item.destino_provincia !== '—' ? `, ${item.destino_provincia}` : '';
            return `
                <li style="list-style:none; page-break-inside: avoid; break-inside: avoid;">
                    <div style="border:1px solid #d1d5db; border-radius:9px; padding:10px 12px; margin-bottom:10px; page-break-inside: avoid; break-inside: avoid;">
                        <h3 style="margin:0 0 4px; font-size:1rem;">${item.codigo} · Solicitud ${item.solicitud_codigo || '-'} </h3>
                        <p style="margin:0 0 8px; color:#4b5563; font-size:0.9rem;">
                            Estado: ${item.estado || '-'} · Entrega: ${item.fecha_entrega || item.fecha || '-'}
                        </p>
                        <div style="display:flex; flex-wrap:wrap; gap:10px; font-size:0.88rem; color:#1f2937;">
                            <span><strong>Fecha de creación:</strong> ${item.fecha_creacion || '-'}</span>
                            <span><strong>Fecha aprobación:</strong> ${item.created_at || '-'}</span>
                        </div>
                        <div style="margin-top:8px; font-size:0.88rem;">
                            <strong>Solicitante:</strong> ${item.solicitante || '-'} (CI ${item.solicitante_ci || '-'})<br>
                            <strong>Contacto:</strong> ${item.solicitante_correo || '-'} · ${item.solicitante_telefono || '-'}
                        </div>
                        <div style="margin-top:8px; font-size:0.88rem;">
                            <strong>Destino:</strong> ${item.destino_comunidad || '-'}${provincia}<br>
                            <strong>Dirección:</strong> ${item.destino_direccion || '-'}
                        </div>
                        <div style="margin-top:8px; font-size:0.88rem;">
                            <strong>Ubicación actual:</strong> ${item.ubicacion_actual || '-'}
                        </div>
                        <div style="margin-top:8px; font-size:0.88rem;">
                            <strong>Conductor:</strong> ${item.conductor || '-'} (CI ${item.conductor_ci || '-'}) · Tel: ${item.conductor_telefono || '-'}<br>
                            <strong>Vehículo:</strong> ${item.vehiculo || '-'} · Placa: ${item.vehiculo_placa || '-'}
                        </div>
                    </div>
                </li>
            `;
        }).join('');
    }

    function buildPaqueteEnCaminoCards(items) {
        if (!items || !items.length) return '';
        return items.map(item => {
            const provincia = item.destino_provincia && item.destino_provincia !== '—' ? `, ${item.destino_provincia}` : '';
            const vehiculoDetalle = item.vehiculo_marca && item.vehiculo_marca !== 'Sin marca'
                ? `${item.vehiculo || '-'} · ${item.vehiculo_marca}${item.vehiculo_modelo && item.vehiculo_modelo !== '—' ? ' · ' + item.vehiculo_modelo : ''}`
                : (item.vehiculo || '-');
            return `
                <li style="list-style:none; page-break-inside: avoid; break-inside: avoid;">
                    <div style="border:1px solid #d1d5db; border-radius:9px; padding:10px 12px; margin-bottom:10px; page-break-inside: avoid; break-inside: avoid;">
                        <h3 style="margin:0 0 4px; font-size:1rem;">${item.codigo} · Solicitud ${item.solicitud_codigo || '-'}</h3>
                        <p style="margin:0 0 6px; color:#4b5563; font-size:0.9rem;">
                            Estado: ${item.estado || '-'} · Última actualización: ${item.fecha || '-'}
                        </p>
                        <div style="display:flex; flex-wrap:wrap; gap:10px; font-size:0.88rem; color:#1f2937;">
                            <span><strong>Tipo emergencia:</strong> ${item.tipo_emergencia || '-'}</span>
                            <span><strong>Fecha solicitud:</strong> ${item.fecha_solicitud_creacion || '-'}</span>
                        </div>
                        <div style="margin-top:8px; font-size:0.88rem;">
                            <strong>Solicitante:</strong> ${item.solicitante || '-'} (CI ${item.solicitante_ci || '-'})<br>
                            <strong>Contacto:</strong> ${item.solicitante_correo || '-'} · ${item.solicitante_telefono || '-'}
                        </div>
                        <div style="margin-top:8px; font-size:0.88rem;">
                            <strong>Destino:</strong> ${item.destino_comunidad || '-'}${provincia}<br>
                            <strong>Dirección:</strong> ${item.destino_direccion || '-'}
                        </div>
                        <div style="margin-top:8px; font-size:0.88rem;">
                            <strong>Ubicación actual:</strong> ${item.ubicacion_actual || '-'}
                        </div>
                        <div style="margin-top:8px; font-size:0.88rem;">
                            <strong>Conductor:</strong> ${item.conductor || '-'} (CI ${item.conductor_ci || '-'}) · Tel: ${item.conductor_telefono || '-'}<br>
                            <strong>Vehículo:</strong> ${vehiculoDetalle} · Color: ${item.vehiculo_color || '-'}
                        </div>
                        <div style="margin-top:8px; font-size:0.88rem;">
                            <strong>Voluntario encargado:</strong> ${item.voluntario || '-'} (CI ${item.voluntario_ci || '-'})
                        </div>
                    </div>
                </li>
            `;
        }).join('');
    }

    function buildFormalPdfLayout(report, listHtml, prettyDate) {
        const accentColor = '#14325c';
        const secondaryText = '#6b7280';
        return `
            <div style="font-family:'Helvetica Neue', Arial, sans-serif; color:#111827;">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; border-bottom:2px solid ${accentColor}; padding-bottom:10px; margin-bottom:16px;">
                    <div>
                        <div style="font-size:20px; font-weight:700; color:${accentColor};">Logística y Transporte</div>
                        <div style="font-size:13px; color:${secondaryText};">Panel de ${report.group}</div>
                    </div>
                    <div style="text-align:right; font-size:12px; color:${secondaryText};">
                        <div><strong>Reporte:</strong> ${report.title}</div>
                        <div><strong>Generado:</strong> ${prettyDate}</div>
                    </div>
                </div>

                <div style="display:flex; flex-wrap:wrap; gap:12px; background:#f9fafb; font-size:0.7rem; margin-bottom:5px">
                    <div><small>Filtro aplicado:</small> ${report.subtitle}</div>
                    <div><small>Total de registros:</small> ${report.count}</div>
                </div>
                <h2 style="margin:0 0 10px; font-size:18px; font-weight:700; color:${accentColor};">
                    ${report.title}
                </h2>

                <div style="margin-top:18px;">${listHtml}</div>
            </div>
        `;
    }
    function buildGoogleMapsUrl(lat, lng) {
        const has = lat !== null && lat !== undefined && lat !== '' &&
                    lng !== null && lng !== undefined && lng !== '';
        if (!has) return null;

        const q = encodeURIComponent(`${lat},${lng}`);
        return `https://www.google.com/maps/search/?api=1&query=${q}`;
    }

    function renderCoordsLink(lat, lng) {
        const url = buildGoogleMapsUrl(lat, lng);
        if (!url) return '—';

        const label = `${lat}, ${lng}`;
        return `<a href="${url}" target="_blank" rel="noopener"
                    style="color:#1d4ed8; text-decoration:underline;">
                    ${label}
                </a>`;
    }


    function exportCurrentReport(report, filenamePrefix) {
        if (!report || report.count <= 0 || !report.content) {
            return;
        }
        if (typeof html2pdf === 'undefined') {
            return;
        }
        let listMarkup = report.content;
        if (report.group === 'Solicitudes' && report.items && report.items.length) {
            if (report.type === 'comunidad') {
                listMarkup = buildComunidadPdfCards(report.items);
            } else {
                listMarkup = buildSolicitudPdfCards(report.items);
            }
        } else if (report.group === 'Paquetes' && report.items && report.items.length) {
            if (report.type === 'entregadas') {
                listMarkup = buildPaqueteEntregadoCards(report.items);
            } else if (report.type === 'en_camino') {
                listMarkup = buildPaqueteEnCaminoCards(report.items);
            }
        }
        const now = new Date();
        const day = String(now.getDate()).padStart(2, '0');
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const year = String(now.getFullYear()).slice(-2);
        const prettyDate = `${day}/${month}/${year}`;
        const todaySlug = `${now.getFullYear()}-${month}-${day}`;

        const wrapper = document.createElement('div');
        wrapper.innerHTML = buildFormalPdfLayout(report, `<ul style="list-style:none;padding:0;margin:0;">${listMarkup}</ul>`, prettyDate);
        const hasCustomLayout = (report.group === 'Solicitudes') || (report.group === 'Paquetes' && ['entregadas','en_camino'].includes(report.type));
        const shouldStyleItems = !hasCustomLayout;
        if (shouldStyleItems) {
            wrapper.querySelectorAll('li').forEach(li => {
                li.style.marginBottom = '8px';
                li.style.border = '1px solid #ddd';
                li.style.borderRadius = '6px';
                li.style.padding = '10px';
                li.style.pageBreakInside = 'avoid';
                li.style.breakInside = 'avoid';
            });
        }
        document.body.appendChild(wrapper);
        const filename = `${filenamePrefix}_${report.slug}_${todaySlug}.pdf`;
        const todayIso = now.toISOString().slice(0, 10);
        const gestionYear = String(now.getFullYear());
        const pdfOptions = {
            margin: 10,
            filename,
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2 },
            enableLinks: true,
            jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
        };

        const worker = html2pdf().set(pdfOptions).from(wrapper);

        worker.outputPdf('blob')
            .then(blob => {
                downloadBlob(blob, filename);
                uploadDashboardReport(blob, filename, {
                    group: report.group,
                    type: report.type,
                    subtitle: report.subtitle,
                    count: report.count,
                    fechaIso: todayIso,
                    gestion: gestionYear
                });
            })
            .catch(error => {
                console.error('Error generando el PDF del dashboard.', error);
                alert('No se pudo generar el PDF del reporte.'); 
            })
            .finally(() => {
                wrapper.remove();
            });
    }

    function buildExcelPayload(report) {
        if (!report || !Array.isArray(report.items) || !report.items.length) {
            return null;
        }

        const safeText = (value) => {
            if (value === null || value === undefined || value === '') {
                return '—';
            }
            return value;
        };

        if (report.group === 'Solicitudes') {
            if (report.type === 'comunidad') {
                return {
                    headings: ['Comunidad', 'Provincia', 'Total solicitudes', 'Última solicitud'],
                    rows: report.items.map(item => [
                        safeText(item.nombre),
                        safeText(item.provincia),
                        item.total ?? 0,
                        safeText(item.ultimaFecha)
                    ])
                };
            }

            const headings = ['Código', 'Solicitante', 'CI', 'Correo', 'Teléfono', 'Comunidad', 'Provincia', 'Fecha solicitud', 'Tipo de emergencia', 'Personas', 'Estado'];
            const rows = report.items.map(item => [
                safeText(item.codigo),
                safeText(item.solicitante),
                safeText(item.solicitante_ci),
                safeText(item.solicitante_correo),
                safeText(item.solicitante_telefono),
                safeText(item.comunidad),
                safeText(item.provincia),
                safeText(item.fecha),
                safeText(item.tipo_emergencia),
                item.cantidad_personas ?? '—',
                safeText(item.estado)
            ]);
            return { headings, rows };
        }

        if (report.group === 'Paquetes') {
            if (report.type === 'voluntarios') {
                const headings = ['CI', 'Nombre', 'Correo', 'Teléfono', 'Total paquetes', 'Solicitudes asociadas'];
                const rows = report.items.map(item => {
                    const paquetes = item.paquetes || [];
                    const solicitudes = paquetes
                        .map(p => p.solicitud_codigo || p.codigo || p.id || '')
                        .filter(Boolean)
                        .join(', ');
                    return [
                        safeText(item.ci),
                        safeText(item.nombre),
                        safeText(item.correo),
                        safeText(item.telefono),
                        paquetes.length,
                        solicitudes || '—'
                    ];
                });
                return { headings, rows };
            }

            if (report.type === 'entregadas') {
                const headings = ['Código paquete', 'Código solicitud', 'Estado', 'Solicitante', 'Fecha entrega', 'Conductor', 'Vehículo', 'Destino', 'Provincia', 'Dirección'];
                const rows = report.items.map(item => [
                    safeText(item.codigo),
                    safeText(item.solicitud_codigo),
                    safeText(item.estado),
                    safeText(item.solicitante),
                    safeText(item.fecha),
                    safeText(item.conductor),
                    `${safeText(item.vehiculo)} ${safeText(item.vehiculo_placa)}`.trim(),
                    safeText(item.destino_comunidad),
                    safeText(item.destino_provincia),
                    safeText(item.destino_direccion)
                ]);
                return { headings, rows };
            }

            if (report.type === 'en_camino') {
                const headings = ['Código paquete', 'Código solicitud', 'Destino', 'Provincia', 'Estado', 'Conductor', 'Vehículo', 'Ubicación', 'Voluntario', 'Fecha salida', 'Fecha solicitud'];
                const rows = report.items.map(item => [
                    safeText(item.codigo),
                    safeText(item.solicitud_codigo),
                    safeText(item.destino),
                    safeText(item.provincia),
                    safeText(item.estado),
                    safeText(item.conductor),
                    `${safeText(item.vehiculo)} ${safeText(item.vehiculo_marca)}`.trim(),
                    safeText(item.ubicacion_actual),
                    safeText(item.voluntario),
                    safeText(item.fecha),
                    safeText(item.fecha_solicitud_creacion)
                ]);
                return { headings, rows };
            }

            if (report.type === 'vehiculos') {
                const headings = ['Placa', 'Marca', 'Modelo', 'Color', 'Tipo', 'Total paquetes', 'Solicitudes asociadas'];
                const rows = report.items.map(item => {
                    const paquetes = item.paquetes || [];
                    const solicitudes = paquetes
                        .map(p => p.solicitud_codigo || p.codigo_paquete || p.id || '')
                        .filter(Boolean)
                        .join(', ');
                    return [
                        safeText(item.placa),
                        safeText(item.marca),
                        safeText(item.modelo),
                        safeText(item.color),
                        safeText(item.tipo),
                        paquetes.length,
                        solicitudes || '—'
                    ];
                });
                return { headings, rows };
            }
        }

        return null;
    }

    function exportReportToExcel(report, filenamePrefix) {
        if (!report || !Array.isArray(report.items) || !report.items.length) {
            alert('Selecciona un filtro con datos antes de exportar a Excel.');
            return;
        }
        const payload = buildExcelPayload(report);
        if (!payload || !payload.rows.length) {
            alert('No hay datos para exportar a Excel.');
            return;
        }
        const ws = XLSX.utils.aoa_to_sheet([payload.headings, ...payload.rows]);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, 'Reporte');
        const now = new Date();
        const timestamp = now.toISOString().replace(/[-:T]/g, '').replace(/\..+/, '').slice(0, 14);
        const filename = `${filenamePrefix}_${report.slug}_${timestamp}.xlsx`;
        XLSX.writeFile(wb, filename);
    }

    function passesDateFilter(item) {
        const fromVal = dateFromInput ? dateFromInput.value : '';
        const toVal = dateToInput ? dateToInput.value : '';
        if (!fromVal && !toVal) return true;
        if (!item.fecha_iso) return false;

        const itemDate = item.fecha_iso;
        if (fromVal && itemDate < fromVal) return false;
        if (toVal && itemDate > toVal) return false;
        return true;
    }

    function renderSolicitudesList(type) {
        if (!resultList) return;
        const requiresList = ['aceptadas', 'negadas', 'comunidad'].includes(type);
        if (!requiresList) {
            resultList.innerHTML = `
                <li class="list-group-item text-muted">
                    Selecciona "Comunidad", "Aceptadas" o "Negadas" para ver la lista.
                </li>
            `;
            currentSolicitudesReport = null;
            return;
        }

        const dataset = solicitudesData[type] || [];
        const filtered = dataset.filter(passesDateFilter);

        if (!filtered.length) {
            const friendlyLabel = type === 'negadas' ? 'negadas' : (type === 'comunidad' ? 'por comunidad' : 'aceptadas');
            resultList.innerHTML = `
                <li class="list-group-item text-muted">
                    No hay solicitudes ${friendlyLabel} registradas para el rango seleccionado.
                </li>
            `;
            currentSolicitudesReport = null;
            return;
        }

        if (type === 'comunidad') {
            const grouped = filtered.reduce((acc, item) => {
                const key = (item.provincia || 'Sin provincia').toLowerCase();
                if (!acc[key]) {
                    acc[key] = {
                        nombre: item.provincia || 'Sin provincia',
                        total: 0,
                        ultimaFecha: item.fecha,
                        ultimaFechaIso: item.fecha_iso,
                        solicitudes: []
                    };
                }
                acc[key].total += 1;
                if (item.fecha_iso && (!acc[key].ultimaFechaIso || item.fecha_iso > acc[key].ultimaFechaIso)) {
                    acc[key].ultimaFecha = item.fecha;
                    acc[key].ultimaFechaIso = item.fecha_iso;
                }
                acc[key].solicitudes.push(item);
                return acc;
            }, {});

            const provinciasArray = Object.values(grouped)
                .sort((a, b) => b.total - a.total);
            const rows = provinciasArray
                .map(item => `
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${item.nombre}</strong>
                            <br><small class="text-muted">Última solicitud: ${item.ultimaFecha}</small>
                        </div>
                        <span class="badge badge-primary badge-pill">${item.total}</span>
                    </li>
                `).join('');
            const provincias = Object.values(grouped)
                .sort((a, b) => b.total - a.total)
                .slice(0, 5);

            const labels = provincias.map(c => c.nombre);
            const values = provincias.map(c => c.total);

            const ctxCom = document.getElementById('solicitudesPorComunidadChart');
            if (ctxCom && typeof Chart !== 'undefined') {
                if (solicitudesPorComunidadChart) {
                    solicitudesPorComunidadChart.destroy();
                }
                const baseColors = [
                    '#007bff',
                    '#28a745',
                    '#ffc107',
                    '#17b8a8',
                    '#2c895f'
                ];
                const barColors = labels.map((_, idx) => baseColors[idx % baseColors.length]);
                solicitudesPorComunidadChart = new Chart(ctxCom, {
                    type: 'bar',
                    data: {
                        labels,
                        datasets: [{
                            label: 'Solicitudes',
                            data: values,
                            backgroundColor: barColors,
                            borderColor: barColors,
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                boxWidth: 12,
                                fontSize: 11
                            }
                        },
                        scales: {
                            yAxes: [{
                                ticks: {
                                    beginAtZero: true,
                                    precision: 0
                                },
                                gridLines: {
                                    color: 'rgba(0,0,0,0.05)'
                                }
                            }],
                            xAxes: [{
                                ticks: {
                                    autoSkip: false,
                                    maxRotation: 45,
                                    minRotation: 0
                                },
                                gridLines: {
                                    display: false
                                }
                            }]
                        },
                        layout: {
                            padding: {
                                left: 5,
                                right: 5,
                                top: 10,
                                bottom: 5
                            }
                        },
                        tooltips: {
                            callbacks: {
                                label: function(tooltipItem, data) {
                                    const label = data.labels[tooltipItem.index] || '';
                                    const value = data.datasets[0].data[tooltipItem.index] || 0;
                                    return `${label}: ${value} solicitudes`;
                                }
                            }
                        },
                        onClick: function(evt, elements) {
                            if (!elements.length) return;
                            const idx = elements[0]._index;
                            const provincia = labels[idx];
                            mostrarModalSolicitudesProvincia(provincia);
                        }
                    }
                });
            function mostrarModalSolicitudesComunidad(comunidad) {
                const solicitudes = (solicitudesData.comunidad || []).filter(item => (item.provincia || '').toLowerCase() === comunidad.toLowerCase());
                const cont = document.getElementById('tablaSolicitudesComunidadContainer');
                document.getElementById('modalSolicitudesComunidadLabel').textContent = `Solicitudes de la provincia: ${comunidad}`;
                if (!solicitudes.length) {
                    cont.innerHTML = '<div class="alert alert-warning">No hay solicitudes para esta provincia.</div>';
                } else {
                    let html = `<div class="table-responsive"><table class="table table-sm table-bordered"><thead><tr><th>Código</th><th>Solicitante</th><th>Comunidad</th><th>Estado</th><th>Fecha</th></tr></thead><tbody>`;
                    solicitudes.forEach(s => {
                        let estado = s.estado || '-';
                        if (estado && estado !== '-') {
                            estado = estado.charAt(0).toUpperCase() + estado.slice(1).toLowerCase();
                        }
                        html += `<tr><td>${s.codigo || '-'}</td><td>${s.solicitante || '-'}</td><td>${s.comunidad || '-'}</td><td>${estado}</td><td>${s.fecha || '-'}</td></tr>`;
                    });
                    html += '</tbody></table></div>';
                    cont.innerHTML = html;
                }
                $('#modalSolicitudesComunidad').modal('show');
            }

            
            function mostrarModalSolicitudesProvincia(provincia) {
                const solicitudes = (solicitudesData.comunidad || []).filter(item => (item.provincia || '').toLowerCase() === provincia.toLowerCase());
                const cont = document.getElementById('tablaSolicitudesComunidadContainer');
                document.getElementById('modalSolicitudesComunidadLabel').textContent = `Solicitudes de la provincia: ${provincia}`;
                const btnReporte = document.getElementById('btn-generar-reporte-provincia');
                if (!solicitudes.length) {
                    cont.innerHTML = '<div class="alert alert-warning">No hay solicitudes para esta provincia.</div>';
                    if (btnReporte) btnReporte.style.display = 'none';
                } else {
                    let html = `<div class="table-responsive"><table class="table table-sm table-bordered"><thead><tr><th>Código</th><th>Solicitante</th><th>Comunidad</th><th>Estado</th><th>Fecha</th></tr></thead><tbody>`;
                    solicitudes.forEach(s => {
                        let estado = s.estado || '-';
                        if (estado && estado !== '-') {
                            estado = estado.charAt(0).toUpperCase() + estado.slice(1).toLowerCase();
                        }
                        html += `<tr><td>${s.codigo || '-'}</td><td>${s.solicitante || '-'}</td><td>${s.comunidad || '-'}</td><td>${estado}</td><td>${s.fecha || '-'}</td></tr>`;
                    });
                    html += '</tbody></table></div>';
                    cont.innerHTML = html;
                    if (btnReporte) btnReporte.style.display = '';
                    btnReporte.onclick = function() {
                        generarReporteProvinciaPDF(provincia, solicitudes);
                    };
                }
                $('#modalSolicitudesComunidad').modal('show');
            function generarReporteProvinciaPDF(provincia, solicitudes) {
                if (!window.html2pdf) {
                    alert('No se puede generar el PDF. Falta html2pdf.js');
                    return;
                }
                let cardsHtml = solicitudes.map(item => {
                    return `<li style="list-style:none; page-break-inside: avoid; break-inside: avoid; margin-bottom:16px;">
                        <div style="border:1px solid #d1d5db; border-radius:9px; padding:14px 16px; background:#fff; page-break-inside: avoid; break-inside: avoid;">
                            <h3 style="margin:0 0 4px; font-size:1.1rem; color:#14325c;">${item.codigo || '-'} · ${item.solicitante || 'Sin solicitante'}</h3>
                            <p style="margin:0 0 8px; color:#4b5563; font-size:0.95rem;">
                                Estado: ${item.estado || '-'} · Tipo: ${item.tipo_emergencia || '-'}
                            </p>
                            <div style="display:flex; flex-wrap:wrap; gap:10px; font-size:0.92rem; color:#1f2937;">
                                <span><strong>Fecha solicitud:</strong> ${item.fecha || '-'}</span>
                                <span><strong>Fecha inicio:</strong> ${item.fecha_inicio || '-'}</span>
                                <span><strong>Personas:</strong> ${item.cantidad_personas ?? '-'}</span>
                            </div>
                            <div style="margin-top:8px; font-size:0.92rem;">
                                <strong>Solicitante:</strong> ${item.solicitante || '-'} (CI ${item.solicitante_ci || '-'})<br>
                                <strong>Contacto:</strong> ${item.solicitante_correo || '-'} · ${item.solicitante_telefono || '-'}
                            </div>
                            <div style="margin-top:8px; font-size:0.92rem;">
                                <strong>Destino:</strong> ${item.comunidad || '-'}<br>
                                <strong>Dirección:</strong> ${item.direccion || '-'}<br>
                                <strong>Coordenadas:</strong> ${renderCoordsLink(item.latitud, item.longitud)}
                            </div>
                            <div style="margin-top:8px; font-size:0.92rem;">
                                <strong>Insumos necesarios:</strong>
                                <div style="margin-top:2px; white-space:pre-wrap;">${item.insumos ? item.insumos.replace(/\n/g, '<br>') : '—'}</div>
                            </div>
                            ${item.justificacion ? `<div style="margin-top:8px; font-size:0.92rem; background:#fff5f5; border:1px solid #f5c2c7; border-radius:6px; padding:8px;"><strong>Motivo del rechazo:</strong><div style="margin-top:2px;">${item.justificacion}</div></div>` : ''}
                        </div>
                    </li>`;
                }).join('');
                const titulo = `Solicitudes de la provincia: ${provincia}`;
                const now = new Date();
                const fecha = `${now.getDate().toString().padStart(2,'0')}/${(now.getMonth()+1).toString().padStart(2,'0')}/${now.getFullYear()}`;
                const wrapper = document.createElement('div');
                wrapper.innerHTML = `
                    <div style='font-family:Helvetica,Arial,sans-serif;color:#111827;'>
                        <div style='font-size:22px;font-weight:700;color:#14325c;margin-bottom:8px;'>${titulo}</div>
                        <div style='font-size:13px;color:#6b7280;margin-bottom:16px;'>Generado: ${fecha}</div>
                        <ul style='list-style:none;padding:0;margin:0;'>${cardsHtml}</ul>
                    </div>
                `;
                document.body.appendChild(wrapper);
                html2pdf().set({
                    margin: 10,
                    filename: `Solicitudes_${provincia.replace(/\s+/g,'_')}_${now.getFullYear()}${(now.getMonth()+1).toString().padStart(2,'0')}${now.getDate().toString().padStart(2,'0')}.pdf`,
                    image: { type: 'jpeg', quality: 0.98 },
                    html2canvas: { scale: 2 },
                    enableLinks: true,
                    jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
                }).from(wrapper).toPdf().get('pdf').then(pdf => {
                }).save().then(() => {
                    wrapper.remove();
                });
            }
            }
            }
            resultList.innerHTML = rows;
            currentSolicitudesReport = buildReportObject(
                'Solicitudes',
                type,
                comunidadesArray.length,
                resultList.innerHTML,
                { items: comunidadesArray }
            );
            return;
        }

        resultList.innerHTML = filtered.map(item => `
            <li class="list-group-item d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                <div class="mb-2 mb-md-0">
                    <strong>${item.codigo}</strong> · ${item.solicitante}<br>
                    <small class="text-muted">${item.comunidad} · ${item.fecha}</small>
                </div>
                <a href="/solicitud/${item.id}" class="btn btn-sm btn-outline-secondary">Ver</a>
            </li>
        `).join('');
        currentSolicitudesReport = buildReportObject('Solicitudes', type, filtered.length, resultList.innerHTML, { items: filtered });
    }

    function attachDateListeners() {
        if (dateFromInput) {
            dateFromInput.addEventListener('change', () => {
                if (filterSelect) {
                    renderSolicitudesList(filterSelect.value);
                }
                if (paquetesSelect) {
                    renderPaquetesList(paquetesSelect.value);
                }
            });
        }
        if (dateToInput) {
            dateToInput.addEventListener('change', () => {
                if (filterSelect) {
                    renderSolicitudesList(filterSelect.value);
                }
                if (paquetesSelect) {
                    renderPaquetesList(paquetesSelect.value);
                }
            });
        }
    }

    if (filterSelect) {
        
        filterSelect.addEventListener('change', function() {
            resultList.classList.remove('d-none');
            listHidden = false;
            updateToggleButton();
            renderSolicitudesList(this.value);
        });
        attachDateListeners();
        renderSolicitudesList(filterSelect.value);
    }

    function updateToggleButton() {
        if (!toggleListBtn) return;
        toggleListBtn.innerHTML = listHidden
            ? '<i class="fas fa-eye"></i> Mostrar lista'
            : '<i class="fas fa-eye-slash"></i> Ocultar lista';
    }

    if (toggleListBtn && resultList) {
        updateToggleButton();
        toggleListBtn.addEventListener('click', function() {
            listHidden = !listHidden;
            if (listHidden) {
                resultList.classList.add('d-none');
            } else {
                resultList.classList.remove('d-none');
            }
            updateToggleButton();
        });
    }

    function renderPaquetesList(type) {
        if (!paquetesResultList) return;

        if (type === 'voluntarios') {
            const dataset = paquetesData.voluntarios || [];
            if (!dataset.length) {
                paquetesResultList.innerHTML = `
                    <li class="list-group-item text-muted">
                        No se encontraron voluntarios activos.
                    </li>
                `;
                currentPaquetesReport = null;
                return;
            }
            const hasDateFilter = Boolean((dateFromInput && dateFromInput.value) || (dateToInput && dateToInput.value));
            const filteredVoluntarios = [];
            const renderedItems = dataset.map(item => {
                const paquetesFiltrados = (item.paquetes || []).filter(passesDateFilter);
                if (hasDateFilter && !paquetesFiltrados.length) {
                    return null;
                }
                const paquetesParaMostrar = hasDateFilter ? paquetesFiltrados : (item.paquetes || []);
                const paquetesHtml = paquetesParaMostrar.length
                    ? paquetesParaMostrar.map(paq => {
                        let fechaCreacion = '-';
                        if (paq.created_at) {
                            let d = paq.created_at;
                            if (d.length >= 10) {
                                let datePart = d.substring(0, 10);
                                let timePart = d.length > 10 ? d.substring(11, 16) : '';
                                let parts = datePart.split('-');
                                if (parts.length === 3) {
                                    fechaCreacion = parts[2] + '/' + parts[1] + '/' + parts[0] + (timePart ? (' ' + timePart) : '');
                                } else {
                                    fechaCreacion = d;
                                }
                            } else {
                                fechaCreacion = d;
                            }
                        }
                        return `<div class="border rounded p-2 mb-2">
                            <div><small class="text-muted">Código de Solicitud:</small> <strong>${paq.solicitud_codigo || '-'}</strong></div>
                            <div><small class="text-muted">Estado:</small> ${paq.estado || '-'}</div>
                            <div><small class="text-muted">Fecha de Creación:</small> <span>${fechaCreacion}</span></div>
                        </div>`;
                    }).join('')
                    : '<small class="text-muted">Sin paquetes registrados.</small>';

                filteredVoluntarios.push(Object.assign({}, item, { paquetes: paquetesParaMostrar }));
                return `
                    <li class="list-group-item">
                        <div>
                            <strong>${item.nombre}</strong><br>
                            <small class="text-muted d-block">CI: ${item.ci}</small>
                            <small class="text-muted d-block" style="font-size: 0.85rem;">Correo: ${item.correo || '-'}</small>
                            <small class="text-muted d-block" style="font-size: 0.85rem;">Teléfono: ${item.telefono || '-'}</small>
                        </div>
                        <div class="mt-2">
                            <small class="text-uppercase text-muted" style="font-size: 0.75rem;">Paquetes asociados</small>
                            ${paquetesHtml}
                        </div>
                    </li>
                `;
            }).filter(Boolean);

            if (!renderedItems.length) {
                paquetesResultList.innerHTML = `
                    <li class="list-group-item text-muted">
                        No se encontraron voluntarios con paquetes en el rango seleccionado.
                    </li>
                `;
                currentPaquetesReport = null;
                return;
            }

            paquetesResultList.innerHTML = renderedItems.join('');
            currentPaquetesReport = buildReportObject('Paquetes', type, filteredVoluntarios.length, paquetesResultList.innerHTML, { items: filteredVoluntarios });
            return;
        }


        if (type === 'entregadas') {
            const dataset = (paquetesData.entregadas || []).filter(passesDateFilter);
            if (!dataset.length) {
                paquetesResultList.innerHTML = `
                    <li class="list-group-item text-muted">
                        No hay paquetes entregados para el rango seleccionado.
                    </li>
                `;
                currentPaquetesReport = null;
                return;
            }
            paquetesResultList.innerHTML = dataset.map(item => {
                const codigo = item.codigo || '-';
                const solicitante = item.solicitante || '-';
                const fecha = item.fecha || item.fecha_entrega || '-';
                const conductor = item.conductor || '-';
                const id = item.id || '';
                return `<li class="list-group-item d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                    <div class="mb-2 mb-md-0">
                        <strong>${codigo}</strong> · ${solicitante}<br>
                        <small class="text-muted">Entrega: ${fecha}</small>
                    </div>
                    <div class="text-md-right">
                        <div><i class="fas fa-user mr-1"></i>${conductor}</div>
                        <a href="/paquete/${id}" class="btn btn-sm btn-outline-secondary mt-2 mt-md-0">Ver</a>
                    </div>
                </li>`;
            }).join('');
            currentPaquetesReport = buildReportObject('Paquetes', type, dataset.length, paquetesResultList.innerHTML, { items: dataset });
            return;
        }

        if (type === 'en_camino') {
            const dataset = (paquetesData.en_camino || []).filter(passesDateFilter);
            if (!dataset.length) {
                paquetesResultList.innerHTML = `
                    <li class="list-group-item text-muted">
                        No hay paquetes en camino para el rango seleccionado.
                    </li>
                `;
                currentPaquetesReport = null;
                return;
            }
            paquetesResultList.innerHTML = dataset.map(item => {
                const codigo = item.codigo || '-';
                const destino = item.destino || item.destino_comunidad || '-';
                const fecha = item.fecha || item.fecha_salida || '-';
                const provincia = item.provincia || item.destino_provincia || '';
                const conductor = item.conductor || '-';
                const vehiculo = item.vehiculo || '-';
                const id = item.id || '';
                return `<li class="list-group-item d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                    <div class="mb-2 mb-md-0">
                        <strong>${codigo}</strong> · ${destino}<br>
                        <small class="text-muted">Salida: ${fecha}${provincia ? ` · Prov.: ${provincia}` : ''}</small>
                    </div>
                    <div class="text-md-right">
                        <div><i class="fas fa-user mr-1"></i>${conductor}</div>
                        <div><i class="fas fa-truck mr-1"></i>${vehiculo}</div>
                        <a href="/paquete/${id}" class="btn btn-sm btn-outline-secondary mt-2">Ver</a>
                    </div>
                </li>`;
            }).join('');
            currentPaquetesReport = buildReportObject('Paquetes', type, dataset.length, paquetesResultList.innerHTML, { items: dataset });
            return;
        }

        if (type === 'vehiculos') {
            const dataset = paquetesData.vehiculos || [];
            if (!dataset.length) {
                paquetesResultList.innerHTML = `
                    <li class="list-group-item text-muted">
                        No se encontraron vehículos registrados.
                    </li>
                `;
                currentPaquetesReport = null;
                return;
            }
            const hasDateFilter = Boolean((dateFromInput && dateFromInput.value) || (dateToInput && dateToInput.value));
            const filteredVehicles = [];
            const renderedItems = dataset.map(item => {
                const paquetes = (item.paquetes || []).filter(passesDateFilter);
                if (hasDateFilter && !paquetes.length) {
                    return null;
                }
                filteredVehicles.push(Object.assign({}, item, { paquetes }));
                const placa = item.placa || '-';
                const marca = item.marca || '-';
                const modelo = item.modelo || '-';
                const color = item.color || '-';
                const tipo = item.tipo || '-';
                const paquetesHtml = paquetes.length
                    ? paquetes.map(paq => {
                        let fechaCreacion = '-';
                        if (paq.created_at) {
                            let d = paq.created_at;
                            if (d.length >= 10) {
                                let datePart = d.substring(0, 10);
                                let timePart = d.length > 10 ? d.substring(11, 16) : '';
                                let parts = datePart.split('-');
                                if (parts.length === 3) {
                                    fechaCreacion = parts[2] + '/' + parts[1] + '/' + parts[0] + (timePart ? (' ' + timePart) : '');
                                } else {
                                    fechaCreacion = d;
                                }
                            } else {
                                fechaCreacion = d;
                            }
                        }
                        return `<div class="border rounded p-2 mb-2">
                            <div><small class="text-muted">Código de Solicitud:</small> <strong>${paq.solicitud_codigo || '-'}</strong></div>
                            <div><small class="text-muted">Estado:</small> ${paq.estado || '-'}</div>
                            <div><small class="text-muted">Fecha de Creación:</small> <span>${fechaCreacion}</span></div>
                        </div>`;
                    }).join('')
                    : `<small class="text-muted">${hasDateFilter ? 'No hay paquetes en el rango seleccionado.' : 'Sin paquetes registrados.'}</small>`;
                return `
                    <li class="list-group-item">
                        <div>
                            <strong>${placa}</strong> · ${marca}
                            <small class="text-muted d-block">Modelo: ${modelo} · Color: ${color}</small>
                            <small class="text-muted d-block">Tipo: ${tipo}</small>
                        </div>
                        <div class="mt-2">
                            <small class="text-uppercase text-muted" style="font-size: 0.75rem;">Paquetes asociados</small>
                            ${paquetesHtml}
                        </div>
                    </li>
                `;
            }).filter(Boolean);
            if (!renderedItems.length) {
                paquetesResultList.innerHTML = `
                    <li class="list-group-item text-muted">
                        No se encontraron vehículos con paquetes en el rango seleccionado.
                    </li>
                `;
                currentPaquetesReport = null;
                return;
            }
            paquetesResultList.innerHTML = renderedItems.join('');
            currentPaquetesReport = buildReportObject('Paquetes', type, filteredVehicles.length, paquetesResultList.innerHTML, { items: filteredVehicles });
            return;
        }

        paquetesResultList.innerHTML = `
            <li class="list-group-item text-muted">
                Selecciona "Voluntarios", "Entregadas", "En camino" o "Vehículos" para ver la lista disponible.
            </li>
        `;
        currentPaquetesReport = null;
    }

    function updatePaquetesToggleButton() {
        if (!togglePaquetesBtn) return;
        togglePaquetesBtn.innerHTML = paquetesListHidden
            ? '<i class="fas fa-eye"></i> Mostrar lista'
            : '<i class="fas fa-eye-slash"></i> Ocultar lista';
    }

    if (paquetesSelect) {
        
        paquetesSelect.addEventListener('change', function() {
            paquetesResultList.classList.remove('d-none');
            paquetesListHidden = false;
            updatePaquetesToggleButton();
            renderPaquetesList(this.value);
        });
    }

    if (togglePaquetesBtn && paquetesResultList) {
        updatePaquetesToggleButton();
        togglePaquetesBtn.addEventListener('click', function() {
            paquetesListHidden = !paquetesListHidden;
            if (paquetesListHidden) {
                paquetesResultList.classList.add('d-none');
            } else {
                paquetesResultList.classList.remove('d-none');
            }
            updatePaquetesToggleButton();
        });
    }

    // Eliminado: doble manejador para evitar conflicto y bug visual

    if (generatePaquetesBtn) {
        generatePaquetesBtn.addEventListener('click', function() {
            exportCurrentReport(currentPaquetesReport, 'Paquetes');
        });
    }

    if (excelSolicitudesBtn) {
        excelSolicitudesBtn.addEventListener('click', function() {
            exportReportToExcel(currentSolicitudesReport, 'Solicitudes');
        });
    }

    if (excelPaquetesBtn) {
        excelPaquetesBtn.addEventListener('click', function() {
            exportReportToExcel(currentPaquetesReport, 'Paquetes');
        });
    }

    document.getElementById('btn-refresh-dashboard').addEventListener('click', function() {
        const btn = this;
        const icon = btn.querySelector('i');
        
       
        icon.classList.add('fa-spin');
        btn.disabled = true;

        fetch('{{ route("dashboard") }}', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            
            document.getElementById('total-solicitudes').textContent = data.total;
            document.getElementById('total-aceptadas').textContent = data.aceptadas;
            document.getElementById('total-rechazadas').textContent = data.rechazadas;
            const tasaAprobacion = data.total > 0 ? Math.round((data.aceptadas / data.total) * 100) : 0;
            document.getElementById('tasa-aprobacion').textContent = tasaAprobacion + '%';
            document.getElementById('total-voluntarios').textContent = data.totalVoluntarios;
            document.getElementById('voluntarios-conductores').textContent = data.voluntariosConductores;
            document.getElementById('total-paquetes').textContent = data.totalPaquetes;
            document.getElementById('paquetes-entregados').textContent = data.paquetesEntregados;

          
            if (solicitudesChart) {
                solicitudesChart.data.datasets[0].data = [
                    data.paquetesEntregados,
                    data.paquetesEnCamino,
                    data.paquetesPendientes,
                    data.paquetesArmados
                ];
                solicitudesChart.update();
            }

            if (data.solicitudesPorComunidad) solicitudesData.comunidad = data.solicitudesPorComunidad;
            if (data.solicitudesAceptadas) solicitudesData.aceptadas = data.solicitudesAceptadas;
            if (data.solicitudesNegadas) solicitudesData.negadas = data.solicitudesNegadas;
            if (data.voluntariosListado) paquetesData.voluntarios = data.voluntariosListado;
            if (data.paquetesEntregadosListado) paquetesData.entregadas = data.paquetesEntregadosListado;
            if (data.paquetesEnCaminoListado) paquetesData.en_camino = data.paquetesEnCaminoListado;
            if (data.vehiculosListado) paquetesData.vehiculos = data.vehiculosListado;
            if (filterSelect) renderSolicitudesList(filterSelect.value);
            if (paquetesSelect) renderPaquetesList(paquetesSelect.value);

           
            const productosHtml = Object.entries(data.productosMasPedidos).length > 0
                ? Object.entries(data.productosMasPedidos).map(([producto, cantidad]) => `
                    <tr>
                        <td>${producto.charAt(0).toUpperCase() + producto.slice(1)}</td>
                        <td class="text-right"><span class="badge badge-info">${cantidad}</span></td>
                    </tr>
                `).join('')
                : '<tr><td colspan="2" class="text-center text-muted">No hay datos de productos.</td></tr>';
            document.getElementById('productos-tbody').innerHTML = productosHtml;

            
            const paquetesHtml = data.paquetes.length > 0
                ? data.paquetes.map(paq => {
                    const badgeClass = paq.dias_entrega > 7 ? 'danger' : (paq.dias_entrega > 3 ? 'warning' : 'success');
                    return `
                        <tr>
                            <td><a href="/paquete/${paq.id_paquete}">#${paq.id_paquete}</a></td>
                            <td>${paq.fecha_create}</td>
                            <td>${paq.fecha_entrega}</td>
                            <td class="text-right">
                                <span class="badge badge-${badgeClass}">
                                    ${Math.round(paq.dias_entrega * 10) / 10} días
                                </span>
                            </td>
                        </tr>
                    `;
                }).join('')
                : '<tr><td colspan="4" class="text-center text-muted">No hay paquetes con fechas de entrega.</td></tr>';
            document.getElementById('paquetes-tbody').innerHTML = paquetesHtml;

            
            if (data.topVoluntariosPaquetes) {
                const voluntariosHtml = data.topVoluntariosPaquetes.length > 0
                    ? data.topVoluntariosPaquetes.map(v => `
                        <tr>
                            <td>${v.nombre}<br><small class="text-muted">CI: ${v.ci}</small></td>
                            <td class="text-right"><span class="badge badge-primary">${v.total}</span></td>
                        </tr>
                    `).join('')
                    : '<tr><td colspan="2" class="text-center text-muted">Sin datos</td></tr>';
                const voluntariosTbody = document.getElementById('voluntarios-paquetes-tbody');
                if (voluntariosTbody) voluntariosTbody.innerHTML = voluntariosHtml;
            }

          
            icon.classList.remove('fa-spin');
            btn.disabled = false;
        })
        .catch(error => {
            console.error('Error refreshing dashboard:', error);
            icon.classList.remove('fa-spin');
            btn.disabled = false;
        });
    });
});
</script>
@stop
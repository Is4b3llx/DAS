<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SolicitudController;
use App\Http\Controllers\PaqueteController;
use App\Http\Controllers\EstadoController;
use App\Http\Controllers\SolicitanteController;
use App\Http\Controllers\UbicacionController;
use App\Http\Controllers\DestinoController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\HistorialSeguimientoDonacioneController;
use App\Http\Controllers\TipoLicenciaController;
use App\Http\Controllers\ConductorController;
use App\Http\Controllers\TipoVehiculoController;
use App\Http\Controllers\VehiculoController;
use App\Http\Controllers\TipoEmergenciaController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\UserAdminController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TrazabilidadController;
use App\Http\Controllers\GeoController;

use App\Http\Controllers\Auth\RegistroSimpleController;
//GATEWAY BUSQUEDA DE USUARIOS
Route::get('registro/ci/{ci}', [RegistroSimpleController::class, 'showByCi']);
//Reverse proxy para evitar cors en mapa
Route::get('/geo/reverse', [GeoController::class, 'reverse'])->name('geo.reverse');

//PUBLICOS
Route::get('/health', function () {
    return response()->json([
        'status'  => 'ok',
        'service' => 'logistica',
    ]);
});
Route::post('login', [LoginController::class, 'apiLogin']);

Route::prefix('trazabilidad')->group(function () {
    Route::get('/voluntario/{ci}', [TrazabilidadController::class, 'porVoluntario']);
    Route::get('/paquete/{codigo}', [TrazabilidadController::class, 'porCodigoPaquete']);
    Route::get('/vehiculo/{placa}', [TrazabilidadController::class, 'porVehiculo']);
    Route::get('/solicitante/{ci}', [TrazabilidadController::class, 'porSolicitante']);
    Route::get('/provincia/{provincia}', [TrazabilidadController::class, 'porProvincia']);
    Route::get('/solicitudes/codigos', [TrazabilidadController::class, 'codigosSolicitudes']);
    Route::get('/vehiculos/placas', [TrazabilidadController::class, 'placasVehiculos']);
});
Route::get('/users/ci', [TrazabilidadController::class, 'exponerUsuarios']);

Route::get('/paquetes/destino-voluntario/{codigo}', [PaqueteController::class, 'showDestinoVoluntario']);

//PARA BRIGADAS - MOCHILA DE BOMBEROS
Route::post('solicitud-publica', action: [SolicitudController::class, 'store']);

 //INVENTARIO - ARMADO DE PAQUETES GET Y PUT Y PATCH
    Route::get('paquetes/pendientes', [PaqueteController::class, 'pendientes'])->name('api.paquetes.pendientes');
    Route::put('paquetes/{paquete}/armar', [PaqueteController::class, 'marcarArmado'])->name('api.paquetes.armar');
    Route::patch('paquetes/{paquete}/armar', [PaqueteController::class, 'marcarArmado'])->name('api.paquetes.armar.patch');

Route::middleware(['auth:sanctum', 'activo'])->group(function () {
    Route::apiResource('solicitud', SolicitudController::class)->except(['store'])->names('api.solicitud');
    Route::apiResource('paquete', PaqueteController::class)->names('api.paquete');
    Route::apiResource('estado', EstadoController::class)->names('api.estado');
    Route::apiResource('solicitante', SolicitanteController::class)->names('api.solicitante');
    Route::apiResource('ubicacion', UbicacionController::class)->names('api.ubicacion');
    Route::apiResource('destino', DestinoController::class)->names('api.destino');
    Route::apiResource('reporte', ReporteController::class)->names('api.reporte');
    Route::apiResource('seguimiento', HistorialSeguimientoDonacioneController::class)->names('api.seguimiento');
    Route::apiResource('tipo-licencia', TipoLicenciaController::class)->names('api.licencia');
    Route::apiResource('conductor', ConductorController::class)->names('api.conductor');
    Route::apiResource('tipo-vehiculo', TipoVehiculoController::class)->names('api.tipovehiculo');
    Route::apiResource('vehiculo', VehiculoController::class)->names('api.vehiculo');
    Route::apiResource('tipo-emergencia', TipoEmergenciaController::class)->names('api.marca');
    Route::apiResource('marca', MarcaController::class)->names('api.marca');
    Route::apiResource('rol', RolController::class)->names('api.rol');
    Route::apiResource('user', UserAdminController::class);
    Route::get('/user', function (Request $request) {
        return $request->user();
    })->middleware('auth:sanctum');
    
    Route::post('solicitud/{id}/aprobar', [SolicitudController::class, 'aprobar']);
    Route::post('solicitud/{id}/negar', [SolicitudController::class, 'negar']);

    Route::post('paquete/{paquete}/entrega/send-code', [PaqueteController::class, 'sendEntregaCode'])
        ->name('api.paquete.entrega.send-code');

    Route::post('paquete/{paquete}/entrega/verify-code', [PaqueteController::class, 'verifyEntregaCode'])
        ->name('api.paquete.entrega.verify-code');
    Route::get('usuario', [UserAdminController::class, 'index']);

    
});
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::post('usuario/{id}/toggle-admin', [UserAdminController::class, 'toggleAdmin']);
    Route::post('usuario/{id}/toggle-activo', [UserAdminController::class, 'toggleActivo']);
    Route::post('usuario/{id}/cambiar-rol', [UserAdminController::class, 'cambiarRol']);
});

Route::middleware(['auth:sanctum', 'activo'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('api.dashboard');
});
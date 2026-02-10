<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\MedicamentoController;
use App\Http\Controllers\Api\RecordatorioMedicamentoController;
use App\Http\Controllers\Api\CalendarioMujerController;
use App\Http\Controllers\Api\ImcController;
use App\Http\Controllers\Api\TestSaludController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\ReporteFallaController;
use App\Http\Controllers\Api\VisitaDomiciliariaController;
use App\Http\Controllers\Api\GoogleCalendarController;
use App\Http\Controllers\Api\OpenFdaController;

/*
|--------------------------------------------------------------------------
| API Routes - Family Integral Backend
|--------------------------------------------------------------------------
*/

// ====================================
// RUTAS PÚBLICAS (Sin autenticación)
// ====================================
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Ruta pública para el callback de Google Calendar
Route::get('/google/callback', [GoogleCalendarController::class, 'callback']);

// Ruta de prueba
Route::get('/test', function () {
    return response()->json([
        'message' => 'Family Integral API funcionando correctamente',
        'version' => '1.0.0',
        'timestamp' => now(),
    ]);
});

// ====================================
// RUTAS PROTEGIDAS (Requieren autenticación)
// ====================================
Route::middleware('auth:sanctum')->group(function () {
    
    // ---------- AUTENTICACIÓN ----------
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });

    // ---------- USUARIOS ----------
    Route::prefix('usuarios')->group(function () {
        Route::get('/', [UsuarioController::class, 'index']);
        Route::get('/{id}', [UsuarioController::class, 'show']);
        Route::put('/{id}', [UsuarioController::class, 'update']);
        Route::delete('/{id}', [UsuarioController::class, 'destroy']);
        
        Route::post('/miembros-familiares', [UsuarioController::class, 'agregarMiembroFamiliar']);
        Route::get('/mis-miembros/lista', [UsuarioController::class, 'miembrosFamiliares']);
    });

    // ---------- MEDICAMENTOS ----------
    Route::prefix('medicamentos')->group(function () {
        Route::get('/', [MedicamentoController::class, 'index']);
        Route::post('/', [MedicamentoController::class, 'store']);
        Route::get('/{id}', [MedicamentoController::class, 'show']);
        Route::put('/{id}', [MedicamentoController::class, 'update']);
        Route::delete('/{id}', [MedicamentoController::class, 'destroy']);
    });

    // ---------- RECORDATORIOS DE MEDICAMENTOS ----------
    Route::prefix('recordatorios')->group(function () {
        Route::get('/', [RecordatorioMedicamentoController::class, 'index']);
        Route::get('/hoy', [RecordatorioMedicamentoController::class, 'recordatoriosHoy']);
        Route::get('/historial', [RecordatorioMedicamentoController::class, 'historial']);
        Route::put('/{id}/tomado', [RecordatorioMedicamentoController::class, 'marcarTomado']);
        Route::put('/{id}/omitido', [RecordatorioMedicamentoController::class, 'marcarOmitido']);
    });

    // ---------- CALENDARIO DE LA MUJER ----------
    Route::prefix('calendario-mujer')->group(function () {
        Route::get('/', [CalendarioMujerController::class, 'index']);
        Route::post('/', [CalendarioMujerController::class, 'store']);
        Route::get('/prediccion', [CalendarioMujerController::class, 'prediccion']);
        Route::put('/{id}', [CalendarioMujerController::class, 'update']);
        Route::delete('/{id}', [CalendarioMujerController::class, 'destroy']);
    });

    // ---------- CALCULADORA IMC ----------
    Route::prefix('imc')->group(function () {
        Route::get('/', [ImcController::class, 'index']);
        Route::post('/', [ImcController::class, 'store']);
        Route::get('/historial', [ImcController::class, 'historial']);
        Route::get('/{id}', [ImcController::class, 'show']);
        Route::delete('/{id}', [ImcController::class, 'destroy']);
    });

    // ---------- TESTS DE SALUD ----------
    Route::prefix('tests-salud')->group(function () {
        Route::get('/', [TestSaludController::class, 'index']);
        Route::post('/', [TestSaludController::class, 'store']);
        Route::get('/tipo/{tipo}', [TestSaludController::class, 'porTipo']);
        Route::get('/{id}', [TestSaludController::class, 'show']);
        Route::delete('/{id}', [TestSaludController::class, 'destroy']);
    });

    // ---------- CHAT ----------
    Route::prefix('chats')->group(function () {
        Route::get('/', [ChatController::class, 'index']);
        Route::post('/', [ChatController::class, 'store']);
        Route::get('/no-leidos', [ChatController::class, 'noLeidos']);
        Route::get('/{id}', [ChatController::class, 'show']);
        Route::post('/{id}/mensaje', [ChatController::class, 'enviarMensaje']);
        Route::get('/{id}/mensajes', [ChatController::class, 'mensajes']);
        Route::put('/{id}/cerrar', [ChatController::class, 'cerrar']);
    });

    // ---------- REPORTES DE FALLAS ----------
    Route::prefix('reportes-fallas')->group(function () {
        Route::get('/', [ReporteFallaController::class, 'index']);
        Route::post('/', [ReporteFallaController::class, 'store']);
        Route::get('/todos', [ReporteFallaController::class, 'todos']);
        Route::get('/estadisticas', [ReporteFallaController::class, 'estadisticas']);
        Route::get('/{id}', [ReporteFallaController::class, 'show']);
        Route::put('/{id}/estado', [ReporteFallaController::class, 'actualizarEstado']);
        Route::delete('/{id}', [ReporteFallaController::class, 'destroy']);
    });

    // ---------- VISITAS DOMICILIARIAS ----------
    Route::prefix('visitas-domiciliarias')->group(function () {
        Route::get('/', [VisitaDomiciliariaController::class, 'index']);
        Route::post('/', [VisitaDomiciliariaController::class, 'store']);
        Route::get('/todas', [VisitaDomiciliariaController::class, 'todas']);
        Route::get('/{id}', [VisitaDomiciliariaController::class, 'show']);
        Route::put('/{id}/confirmar', [VisitaDomiciliariaController::class, 'confirmar']);
        Route::put('/{id}/estado', [VisitaDomiciliariaController::class, 'actualizarEstado']);
        Route::put('/{id}/cancelar', [VisitaDomiciliariaController::class, 'cancelar']);
        Route::delete('/{id}', [VisitaDomiciliariaController::class, 'destroy']);
    });

    // ---------- GOOGLE CALENDAR (Rutas protegidas) ----------
    Route::prefix('google')->group(function () {
        Route::get('/authorize', [GoogleCalendarController::class, 'authorize']);
        Route::post('/save-tokens', [GoogleCalendarController::class, 'saveTokens']);
        Route::post('/events', [GoogleCalendarController::class, 'createEvent']);
        Route::get('/events', [GoogleCalendarController::class, 'listEvents']);
        Route::delete('/disconnect', [GoogleCalendarController::class, 'disconnect']);
    });

    // ---------- OPENFDA API (Información de Medicamentos) ----------
    Route::prefix('openfda')->group(function () {
        Route::get('/medicamento', [App\Http\Controllers\Api\OpenFdaController::class, 'buscarMedicamento']);
        Route::post('/interacciones', [App\Http\Controllers\Api\OpenFdaController::class, 'verificarInteracciones']);
        Route::get('/eventos-adversos', [App\Http\Controllers\Api\OpenFdaController::class, 'eventosAdversos']);
    }); 

});
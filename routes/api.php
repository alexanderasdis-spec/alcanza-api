<?php

use App\Http\Controllers\AbonosController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MetasController;
use App\Http\Controllers\PerfilController;
use Illuminate\Support\Facades\Route;

// ── Rutas públicas: cuentas ────────────────────────────────────────────────
// throttle:cuentas = máximo 10 intentos por minuto en cada ruta (ver AppServiceProvider),
// para frenar a quien intente adivinar contraseñas.
Route::middleware('throttle:cuentas')->group(function () {
    Route::post('registro', [AuthController::class, 'registro']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('olvide-contrasena', [AuthController::class, 'olvideContrasena']);
    Route::post('restablecer-contrasena', [AuthController::class, 'restablecerContrasena']);
});

// ── Rutas protegidas: exigen el token (Authorization: Bearer <token>) ──────
// Sin token válido, Sanctum responde 401.
Route::middleware('auth:sanctum')->group(function () {
    Route::get('usuario', [AuthController::class, 'usuario']);
    Route::post('logout', [AuthController::class, 'logout']);

    // Perfil del usuario: consultar y guardar.
    Route::get('perfil', [PerfilController::class, 'show']);
    Route::post('perfil', [PerfilController::class, 'store']);

    // Metas de ahorro: GET, POST, GET {id}, PUT/PATCH {id}, DELETE {id}.
    Route::apiResource('metas', MetasController::class);

    // Abonos de una meta.
    Route::post('metas/{meta}/abonos', [AbonosController::class, 'store']);
});

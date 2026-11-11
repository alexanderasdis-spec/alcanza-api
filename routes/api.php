<?php

use App\Http\Controllers\AbonosController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MetasController;
use App\Http\Controllers\PerfilController;
use Illuminate\Support\Facades\Route;

// Límites de peticiones (config/limites.php). Al pasarse: 429.
// - LimiteTemprano (bootstrap/app.php)  toda la API, por IP y por token, antes de todo lo demás
// - throttle:por_usuario                rutas con sesión, por usuario
// - throttle:escrituras                 crear, cambiar, borrar y abonar, por usuario
// - throttle:cuentas                    registro, login y contraseñas, por IP y ruta
Route::group([], function () {

    // ── Rutas públicas: cuentas ──────────────────────────────────────────────
    Route::middleware('throttle:cuentas')->group(function () {
        Route::post('registro', [AuthController::class, 'registro']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('olvide-contrasena', [AuthController::class, 'olvideContrasena']);
        Route::post('restablecer-contrasena', [AuthController::class, 'restablecerContrasena']);
    });

    // ── Rutas protegidas: exigen el token (Authorization: Bearer <token>) ────
    // Sin token válido, Sanctum responde 401. El límite por usuario va después del token,
    // porque necesita saber quién es.
    Route::middleware(['auth:sanctum', 'throttle:por_usuario'])->group(function () {
        Route::get('usuario', [AuthController::class, 'usuario']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::put('contrasena', [AuthController::class, 'cambiarContrasena'])->middleware('throttle:cuentas');

        // Consultas: perfil y metas.
        Route::get('perfil', [PerfilController::class, 'show']);
        Route::apiResource('metas', MetasController::class)->only(['index', 'show']);

        // Escrituras: tienen un límite más bajo porque cada una guarda en la base de datos.
        Route::middleware('throttle:escrituras')->group(function () {
            Route::post('perfil', [PerfilController::class, 'store']);
            Route::post('perfil/foto', [PerfilController::class, 'subirFoto'])->middleware('throttle:cuentas');
            Route::delete('perfil/foto', [PerfilController::class, 'eliminarFoto']);

            // Metas de ahorro: POST, PUT/PATCH {id} y DELETE {id}.
            Route::apiResource('metas', MetasController::class)->only(['store', 'update', 'destroy']);

            // Abonos de una meta.
            Route::post('metas/{meta}/abonos', [AbonosController::class, 'store']);
        });
    });
});

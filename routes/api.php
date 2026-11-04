<?php

use App\Http\Controllers\AbonosController;
use App\Http\Controllers\MetasController;
use App\Http\Controllers\PerfilController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Perfil rápido (sin registro): consultar y guardar.
Route::get('perfil', [PerfilController::class, 'show']);
Route::post('perfil', [PerfilController::class, 'store']);

// Metas de ahorro: GET, POST, GET {id}, PUT/PATCH {id}, DELETE {id}.
Route::apiResource('metas', MetasController::class);

// Abonos de una meta.
Route::post('metas/{meta}/abonos', [AbonosController::class, 'store']);

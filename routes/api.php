<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\ServicioController;
use App\Http\Controllers\Api\AuthController;

// Healthcheck
Route::get('ping', fn () => response()->json(['pong' => true]));

// ---------- Usuarios (público por ahora) ----------
Route::apiResource('usuarios', UsuarioController::class);

// ---------- Auth ----------
Route::post('auth/login', [AuthController::class, 'login'])->middleware('throttle:6,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('auth/me', [AuthController::class, 'me']);
    Route::post('auth/logout', [AuthController::class, 'logout']);
});

// ---------- Servicios ----------
/**
 * Rutas públicas: listar y ver detalle.
 * Importante: definirlas ANTES del grupo protegido para evitar sombras/colisiones.
 */
Route::apiResource('servicios', ServicioController::class)->only(['index','show']);

/**
 * Rutas protegidas: crear/actualizar/eliminar.
 * No repitas apiResource completo ni mezcles fuera del group.
 */
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('servicios', ServicioController::class)->only(['store','update','destroy']);
});

Route::middleware('auth:sanctum')->group(function () {
  Route::post('servicios',  [ServicioController::class, 'store'])->can('create', \App\Models\Servicio::class);
  Route::patch('servicios/{servicio}', [ServicioController::class, 'update'])->can('update', 'servicio');
  Route::delete('servicios/{servicio}',[ServicioController::class, 'destroy'])->can('delete', 'servicio');
});
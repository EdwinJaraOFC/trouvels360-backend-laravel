<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\ServicioController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HotelController;
use App\Http\Controllers\Api\HabitacionController;
use App\Http\Controllers\Api\ReservaHabitacionController;

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

// Hoteles (detalle y disponibilidad) – listar hoteles se hace por /servicios?tipo=hotel
Route::get('hoteles/{servicio_id}', [HotelController::class, 'show']); // público
Route::get('hoteles/{servicio_id}/disponibilidad', [HotelController::class, 'disponibilidad']); // público

// Mutaciones protegidas (solo proveedor dueño)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('hoteles', [HotelController::class, 'store']); // crear detalle de hotel para un servicio tipo=hotel
    Route::put('hoteles/{servicio_id}', [HotelController::class, 'update']);
    Route::delete('hoteles/{servicio_id}', [HotelController::class, 'destroy']);

    // Habitaciones (CRUD)
    Route::post('hoteles/{servicio_id}/habitaciones', [HabitacionController::class, 'store']);
    Route::put('habitaciones/{habitacion}', [HabitacionController::class, 'update']);
    Route::delete('habitaciones/{habitacion}', [HabitacionController::class, 'destroy']);

    // Reservas de habitaciones (viajero) – crear/cancelar
    Route::post('reservas-habitaciones', [ReservaHabitacionController::class, 'store']); // crear reserva
    Route::post('reservas-habitaciones/{reserva}/cancelar', [ReservaHabitacionController::class, 'cancelar']);
    Route::get('mis-reservas', [ReservaHabitacionController::class, 'misReservas']); // viajero autenticado
});
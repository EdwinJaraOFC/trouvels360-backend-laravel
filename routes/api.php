<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\ServicioController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HotelController;
use App\Http\Controllers\Api\HabitacionController;
use App\Http\Controllers\Api\ReservaHabitacionController;
use App\Http\Controllers\Api\TourController;
use App\Http\Controllers\Api\TourSalidaController;
use App\Http\Controllers\Api\TourActividadController;
use App\Http\Controllers\Api\ReservaTourController;

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

// (Estas 3 ya están dentro del group de arriba; si las mantienes, añade constraints numéricas)
Route::middleware('auth:sanctum')->group(function () {
  Route::post('servicios',  [ServicioController::class, 'store'])->can('create', \App\Models\Servicio::class);
  Route::patch('servicios/{servicio}', [ServicioController::class, 'update'])->can('update', 'servicio')->whereNumber('servicio');
  Route::delete('servicios/{servicio}',[ServicioController::class, 'destroy'])->can('delete', 'servicio')->whereNumber('servicio');
});

// Hoteles (detalle y disponibilidad) – listar hoteles se hace por /servicios?tipo=hotel
Route::apiResource('hoteles', HotelController::class);
Route::get('hoteles/{servicio_id}', [HotelController::class, 'show'])->whereNumber('servicio_id'); // público
Route::get('hoteles/{servicio_id}/disponibilidad', [HotelController::class, 'disponibilidad'])->whereNumber('servicio_id'); // público

// Mutaciones protegidas (solo proveedor dueño)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('hoteles', [HotelController::class, 'store']); // crear detalle de hotel para un servicio tipo=hotel
    Route::put('hoteles/{servicio_id}', [HotelController::class, 'update'])->whereNumber('servicio_id');
    Route::delete('hoteles/{servicio_id}', [HotelController::class, 'destroy'])->whereNumber('servicio_id');

    // Habitaciones (CRUD)
    Route::post('hoteles/{servicio_id}/habitaciones', [HabitacionController::class, 'store'])->whereNumber('servicio_id');
    Route::put('habitaciones/{habitacion}', [HabitacionController::class, 'update'])->whereNumber('habitacion');
    Route::delete('habitaciones/{habitacion}', [HabitacionController::class, 'destroy'])->whereNumber('habitacion');

    // Reservas de habitaciones (viajero) – crear/cancelar
    Route::post('reservas-habitaciones', [ReservaHabitacionController::class, 'store']); // crear reserva
    Route::post('reservas-habitaciones/{reserva}/cancelar', [ReservaHabitacionController::class, 'cancelar'])->whereNumber('reserva');
    Route::get('mis-reservas', [ReservaHabitacionController::class, 'misReservas']); // viajero autenticado
});

// --------- TOURS ---------
// Listado público
Route::get('tours', [TourController::class, 'index']);
Route::get('tours/{tour}', [TourController::class, 'show'])->whereNumber('tour');
Route::get('tours/{tour}/salidas', [TourSalidaController::class, 'index'])->whereNumber('tour');
Route::get('tours/{tour}/actividades', [TourActividadController::class, 'index'])->whereNumber('tour');

// Protegido
Route::middleware('auth:sanctum')->group(function () {
    // Tours (solo proveedor dueño)
    Route::post('tours', [TourController::class, 'store']);
    Route::put('tours/{tour}', [TourController::class, 'update'])->whereNumber('tour');
    Route::patch('tours/{tour}', [TourController::class, 'update'])->whereNumber('tour');
    Route::delete('tours/{tour}', [TourController::class, 'destroy'])->whereNumber('tour');

    // Salidas (solo proveedor dueño del tour)
    Route::post('tours/{tour}/salidas', [TourSalidaController::class, 'store'])->whereNumber('tour');
    Route::put('tours/{tour}/salidas/{salida}', [TourSalidaController::class, 'update'])->whereNumber('tour')->whereNumber('salida');
    Route::patch('tours/{tour}/salidas/{salida}', [TourSalidaController::class, 'update'])->whereNumber('tour')->whereNumber('salida');
    Route::delete('tours/{tour}/salidas/{salida}', [TourSalidaController::class, 'destroy'])->whereNumber('tour')->whereNumber('salida');

    // Actividades (solo proveedor dueño del tour)
    Route::post('tours/{tour}/actividades', [TourActividadController::class, 'store'])->whereNumber('tour');
    Route::put('tours/{tour}/actividades/{actividad}', [TourActividadController::class, 'update'])->whereNumber('tour')->whereNumber('actividad');
    Route::patch('tours/{tour}/actividades/{actividad}', [TourActividadController::class, 'update'])->whereNumber('tour')->whereNumber('actividad');
    Route::delete('tours/{tour}/actividades/{actividad}', [TourActividadController::class, 'destroy'])->whereNumber('tour')->whereNumber('actividad');

    // Reservas de tour (viajero)
    Route::post('tours/salidas/{salida}/reservas', [ReservaTourController::class, 'store'])->whereNumber('salida');
    Route::post('tours/reservas/{reserva}/cancelar', [ReservaTourController::class, 'cancelar'])->whereNumber('reserva');

    // ⚠️ Esta ruta literal no debe ser capturada por {tour}; con whereNumber ya no choca:
    Route::get('tours/mis-reservas', [ReservaTourController::class, 'misReservas']);
});

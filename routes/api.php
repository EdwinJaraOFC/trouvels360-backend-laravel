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
use App\Http\Controllers\Api\ReviewController;

// Healthcheck
Route::get('ping', fn () => response()->json(['pong' => true]));

// ---------- Usuarios ----------

// Público: solo listar y ver detalle
Route::apiResource('usuarios', UsuarioController::class)->only(['index','show']);

// Protegido: actualizar o eliminar la propia cuenta
Route::middleware('auth:sanctum')->group(function () {
    // Actualizar usuario autenticado
    Route::patch('usuarios/me', [UsuarioController::class, 'updateMe']);
    Route::put('usuarios/me',   [UsuarioController::class, 'updateMe']);

    // Eliminar su propia cuenta
    Route::delete('usuarios/me', [UsuarioController::class, 'destroyMe']);
});

// ---------- Auth ----------
Route::post('auth/login',    [AuthController::class, 'login'])->middleware('throttle:6,1');
Route::post('auth/register', [AuthController::class, 'register'])->middleware('throttle:10,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('auth/me',     [AuthController::class, 'me']);
    Route::post('auth/logout',[AuthController::class, 'logout']);
});

// ---------- Servicios ----------
// Público: listar y ver detalle
Route::apiResource('servicios', ServicioController::class)->only(['index','show']);

// Protegido: crear/actualizar/eliminar
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('servicios', ServicioController::class)->only(['store','update','destroy']);
    Route::get('proveedor/servicios', [ServicioController::class, 'indexMine']);
});

// ---------- Hoteles ----------
// Público
Route::get('hoteles', [HotelController::class, 'index']); // listado
Route::get('hoteles/{servicio_id}', [HotelController::class, 'show'])->whereNumber('servicio_id'); // detalle
Route::get('hoteles/{servicio_id}/disponibilidad', [HotelController::class, 'disponibilidad'])->whereNumber('servicio_id'); // disponibilidad

// Protegido (solo proveedor dueño vía Request authorize)
Route::middleware('auth:sanctum')->group(function () {
    // Crear Servicio(tipo=hotel) + Hotel
    Route::post('hoteles', [HotelController::class, 'store']);

    // Actualizar / Eliminar (usa UpdateHotelRequest->authorize)
    Route::put('hoteles/{servicio_id}', [HotelController::class, 'update'])->whereNumber('servicio_id');
    Route::delete('hoteles/{servicio_id}', [HotelController::class, 'destroy'])->whereNumber('servicio_id');

    // Habitaciones (CRUD)
    Route::post('hoteles/{servicio_id}/habitaciones', [HabitacionController::class, 'store'])->whereNumber('servicio_id');
    Route::put('habitaciones/{habitacion}', [HabitacionController::class, 'update'])->whereNumber('habitacion');
    Route::delete('habitaciones/{habitacion}', [HabitacionController::class, 'destroy'])->whereNumber('habitacion');

    // Reservas de habitaciones (viajero)
    Route::post('reservas-habitaciones', [ReservaHabitacionController::class, 'store']);
    Route::post('reservas-habitaciones/{reserva}/cancelar', [ReservaHabitacionController::class, 'cancelar'])->whereNumber('reserva');
    Route::get('mis-reservas', [ReservaHabitacionController::class, 'misReservas']);
});

// --------- TOURS ---------
// Público
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

    // Salidas
    Route::post('tours/{tour}/salidas', [TourSalidaController::class, 'store'])->whereNumber('tour');
    Route::put('tours/{tour}/salidas/{salida}', [TourSalidaController::class, 'update'])->whereNumber('tour')->whereNumber('salida');
    Route::patch('tours/{tour}/salidas/{salida}', [TourSalidaController::class, 'update'])->whereNumber('tour')->whereNumber('salida');
    Route::delete('tours/{tour}/salidas/{salida}', [TourSalidaController::class, 'destroy'])->whereNumber('tour')->whereNumber('salida');

    // Actividades
    Route::post('tours/{tour}/actividades', [TourActividadController::class, 'store'])->whereNumber('tour');
    Route::put('tours/{tour}/actividades/{actividad}', [TourActividadController::class, 'update'])->whereNumber('tour')->whereNumber('actividad');
    Route::patch('tours/{tour}/actividades/{actividad}', [TourActividadController::class, 'update'])->whereNumber('tour')->whereNumber('actividad');
    Route::delete('tours/{tour}/actividades/{actividad}', [TourActividadController::class, 'destroy'])->whereNumber('tour')->whereNumber('actividad');

    // Reservas de tour (viajero)
    Route::post('tours/salidas/{salida}/reservas', [ReservaTourController::class, 'store'])->whereNumber('salida');
    Route::post('tours/reservas/{reserva}/cancelar', [ReservaTourController::class, 'cancelar'])->whereNumber('reserva');

    // No colisiona con {tour} gracias a whereNumber en las anteriores
    Route::get('tours/mis-reservas', [ReservaTourController::class, 'misReservas']);
});

// -----RESEÑAS (Reviews)------
// Público
Route::get('reviews', [ReviewController::class, 'index']);

//Protegido
Route::middleware('auth:sanctum')->group(function () {
    Route::post('reviews', [ReviewController::class, 'store']);
    Route::put('reviews/{review}', [ReviewController::class, 'update']);
    Route::patch('reviews/{review}', [ReviewController::class, 'update']);
    Route::delete('reviews/{review}', [ReviewController::class, 'destroy']);
});

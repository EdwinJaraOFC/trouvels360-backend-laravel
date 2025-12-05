<?php

use Illuminate\Support\Facades\Route;

// Controladores
use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\ServicioController;
use App\Http\Controllers\Api\AuthController; // ← ya unificado JWT
use App\Http\Controllers\Api\ReservaController;
use App\Http\Controllers\Api\HotelController;
use App\Http\Controllers\Api\HabitacionController;
use App\Http\Controllers\Api\ReservaHabitacionController;
use App\Http\Controllers\Api\TourController;
use App\Http\Controllers\Api\TourSalidaController;
use App\Http\Controllers\Api\TourActividadController;
use App\Http\Controllers\Api\ReservaTourController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\ImageSearchController;

// ---------------------------------------------------------
// Healthcheck
// ---------------------------------------------------------
Route::get('ping', fn () => response()->json(['pong' => true]));

//Route::get('/sanctum/csrf-cookie', function () {
//    return response()->json(['status' => 'ok']);
//});

// ---------------------------------------------------------
// AUTH (JWT con cookies HttpOnly + CSRF)  => /api/auth/*
// ---------------------------------------------------------
Route::prefix('auth')->group(function () {
    // 1) Entrega cookie XSRF-TOKEN (NO HttpOnly) y token en JSON
    Route::get('/csrf', [AuthController::class, 'csrf']);

    // 2) Registro (opcional). Requiere CSRF.
    Route::post('/register', [AuthController::class, 'register'])
        ->middleware(['csrf.api','throttle:10,1']);

    // 3) Login: setea cookie HttpOnly 'access_token' — requiere CSRF
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware(['throttle:6,1']);

    // 3.5) 🔐 NUEVO: Obtener el JWT leyendo la cookie HttpOnly actual
    Route::get('/token', [AuthController::class, 'tokenFromCookie'])
        ->middleware(['jwt.cookie','jwt.auth']);

    // 4) Refresh: fuera de jwt.auth (acepta token expirado dentro de refresh_ttl)
    Route::post('/refresh', [AuthController::class, 'refresh'])
        ->middleware(['jwt.cookie', 'csrf.api']);

    // 5) Rutas protegidas por JWT vigente
    Route::middleware(['jwt.cookie','jwt.auth'])->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout'])->middleware('csrf.api');
    });
});

// ---------------------------------------------------------
// USUARIOS
// ---------------------------------------------------------

// Público: listar y ver detalle
Route::apiResource('usuarios', UsuarioController::class)->only(['index','show']);

// Protegido: actualizar/eliminar la propia cuenta (mutadores → CSRF)
Route::middleware(['jwt.cookie','jwt.auth'])->group(function () {
    Route::patch('usuarios/me', [UsuarioController::class, 'updateMe'])->middleware('csrf.api');
    Route::put('usuarios/me',   [UsuarioController::class, 'updateMe'])->middleware('csrf.api');
    Route::delete('usuarios/me',[UsuarioController::class, 'destroyMe'])->middleware('csrf.api');
});

// ---------------------------------------------------------
// SERVICIOS
// ---------------------------------------------------------

// Público
Route::apiResource('servicios', ServicioController::class)->only(['index','show']);
Route::get('/eliminados', [ServicioController::class, 'eliminados']);

// Protegido (mutadores → CSRF; GET no necesita CSRF)
Route::middleware(['jwt.cookie','jwt.auth'])->group(function () {
    Route::apiResource('servicios', ServicioController::class)->only(['store','update','destroy'])
         ->middleware('csrf.api');

    Route::get('proveedor/servicios', [ServicioController::class, 'indexMine']);
    Route::get('proveedor/servicios/{id}/reservas', [ServicioController::class, 'reservasPorServicio']);
});

// ---------------------------------------------------------
// HOTELES
// ---------------------------------------------------------

// Público
Route::get('hoteles', [HotelController::class, 'index']);
Route::get('hoteles/{servicio_id}', [HotelController::class, 'show'])->whereNumber('servicio_id');
Route::get('hoteles/{servicio_id}/disponibilidad', [HotelController::class, 'disponibilidad'])->whereNumber('servicio_id');

// Protegido (mutadores → CSRF)
Route::middleware(['jwt.cookie','jwt.auth'])->group(function () {
    Route::post('hoteles', [HotelController::class, 'store'])->middleware('csrf.api');

    Route::put('hoteles/{servicio_id}', [HotelController::class, 'update'])
         ->whereNumber('servicio_id')->middleware('csrf.api');

    Route::delete('hoteles/{servicio_id}', [HotelController::class, 'destroy'])
         ->whereNumber('servicio_id')->middleware('csrf.api');

    Route::post('hoteles/{servicio_id}/habitaciones', [HabitacionController::class, 'store'])
         ->whereNumber('servicio_id')->middleware('csrf.api');

    Route::put('habitaciones/{habitacion}', [HabitacionController::class, 'update'])
         ->whereNumber('habitacion')->middleware('csrf.api');

    Route::delete('habitaciones/{habitacion}', [HabitacionController::class, 'destroy'])
         ->whereNumber('habitacion')->middleware('csrf.api');

    // Reservas de habitaciones (viajero)
    Route::post('reservas-habitaciones', [ReservaHabitacionController::class, 'store'])->middleware('csrf.api');
    Route::post('reservas-habitaciones/{reserva}/cancelar', [ReservaHabitacionController::class, 'cancelar'])
         ->whereNumber('reserva')->middleware('csrf.api');

    // GET protegido (no requiere CSRF)
    Route::get('mis-reservas', [ReservaHabitacionController::class, 'misReservas']);
});

// ---------------------------------------------------------
// TOURS
// ---------------------------------------------------------

// Público
Route::get('tours', [TourController::class, 'index']);
Route::get('tours/{tour}', [TourController::class, 'show'])->whereNumber('tour');
Route::get('tours/{tour}/salidas', [TourSalidaController::class, 'index'])->whereNumber('tour');
Route::get('tours/{tour}/actividades', [TourActividadController::class, 'index'])->whereNumber('tour');

// Protegido (mutadores → CSRF)
Route::middleware(['jwt.cookie','jwt.auth'])->group(function () {
    Route::post('tours', [TourController::class, 'store'])->middleware('csrf.api');

    Route::put('tours/{tour}', [TourController::class, 'update'])
         ->whereNumber('tour')->middleware('csrf.api');
    Route::patch('tours/{tour}', [TourController::class, 'update'])
         ->whereNumber('tour')->middleware('csrf.api');

    Route::delete('tours/{tour}', [TourController::class, 'destroy'])
         ->whereNumber('tour')->middleware('csrf.api');

    Route::post('tours/{tour}/salidas', [TourSalidaController::class, 'store'])
         ->whereNumber('tour')->middleware('csrf.api');

    Route::put('tours/{tour}/salidas/{salida}', [TourSalidaController::class, 'update'])
         ->whereNumber('tour')->whereNumber('salida')->middleware('csrf.api');

    Route::patch('tours/{tour}/salidas/{salida}', [TourSalidaController::class, 'update'])
         ->whereNumber('tour')->whereNumber('salida')->middleware('csrf.api');

    Route::delete('tours/{tour}/salidas/{salida}', [TourSalidaController::class, 'destroy']
         )->whereNumber('tour')->whereNumber('salida')->middleware('csrf.api');

    Route::post('tours/{tour}/actividades', [TourActividadController::class, 'store'])
         ->whereNumber('tour')->middleware('csrf.api');

    Route::put('tours/{tour}/actividades/{actividad}', [TourActividadController::class, 'update'])
         ->whereNumber('tour')->whereNumber('actividad')->middleware('csrf.api');

    Route::patch('tours/{tour}/actividades/{actividad}', [TourActividadController::class, 'update'])
         ->whereNumber('tour')->whereNumber('actividad')->middleware('csrf.api');

    Route::delete('tours/{tour}/actividades/{actividad}', [TourActividadController::class, 'destroy'])
         ->whereNumber('tour')->whereNumber('actividad')->middleware('csrf.api');

    Route::post('tours/salidas/{salida}/reservas', [ReservaTourController::class, 'store'])
         ->whereNumber('salida')->middleware('csrf.api');

    Route::post('tours/reservas/{reserva}/cancelar', [ReservaTourController::class, 'cancelar'])
         ->whereNumber('reserva')->middleware('csrf.api');

    // GET protegido
    Route::get('tours/mis-reservas', [ReservaTourController::class, 'misReservas']);
});

// ---------------------------------------------------------
// RESEÑAS
// ---------------------------------------------------------

// Público
Route::get('reviews', [ReviewController::class, 'index']);

// Protegido (mutadores → CSRF)
Route::middleware(['jwt.cookie','jwt.auth'])->group(function () {
    Route::post('reviews', [ReviewController::class, 'store'])->middleware('csrf.api');
    Route::put('reviews/{review}', [ReviewController::class, 'update'])->middleware('csrf.api');
    Route::patch('reviews/{review}', [ReviewController::class, 'update'])->middleware('csrf.api');
    Route::delete('reviews/{review}', [ReviewController::class, 'destroy'])->middleware('csrf.api');
});

// ---------------------------------------------------------
// IMÁGENES / UNSPLASH
// ---------------------------------------------------------

Route::middleware(['jwt.cookie','jwt.auth'])->group(function () {
     Route::get('images/search', [ImageSearchController::class, 'search'])
         ->name('images.search');
});
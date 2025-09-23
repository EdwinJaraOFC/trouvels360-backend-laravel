<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\HotelController;
use App\Http\Controllers\Api\ReservaController;
use App\Http\Controllers\Api\ServicioController;
use App\Http\Controllers\Api\TourController;

Route::get('ping', fn () => response()->json(['pong' => true]));
Route::apiResource('usuarios', UsuarioController::class);
Route::apiResource('hoteles', HotelController::class);
Route::apiResource('reservas', ReservaController::class);

// Rutas adicionales para reservas
Route::patch('reservas/{reserva}/estado', [ReservaController::class, 'actualizarEstado']);
Route::post('reservas/{reserva}/cancelar', [ReservaController::class, 'cancelar']);
Route::get('usuarios/{usuario_id}/reservas', [ReservaController::class, 'porUsuario']);
Route::get('servicios/{servicio_id}/reservas', [ReservaController::class, 'porServicio']);
Route::get('reservas/buscar/{codigo}', [ReservaController::class, 'buscarPorCodigo']);
Route::apiResource('servicios', ServicioController::class);
Route::apiResource('tours', TourController::class);


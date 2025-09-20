<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\ServicioController;
use App\Http\Controllers\Api\TourController;

Route::get('ping', fn () => response()->json(['pong' => true]));
Route::apiResource('usuarios', UsuarioController::class);
Route::apiResource('servicios', ServicioController::class);
Route::apiResource('tours', TourController::class);


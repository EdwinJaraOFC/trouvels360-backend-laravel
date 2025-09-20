<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\ServicioController;

Route::get('ping', fn () => response()->json(['pong' => true]));
Route::apiResource('usuarios', UsuarioController::class);
Route::apiResource('servicios', ServicioController::class);


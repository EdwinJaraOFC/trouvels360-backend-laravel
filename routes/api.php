<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UsuarioController;

Route::get('ping', fn () => response()->json(['pong' => true]));
Route::apiResource('usuarios', UsuarioController::class);


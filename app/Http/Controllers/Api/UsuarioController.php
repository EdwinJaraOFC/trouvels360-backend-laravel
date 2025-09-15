<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreUsuarioRequest;
use App\Http\Requests\UpdateUsuarioRequest;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;

class UsuarioController extends Controller
{
    // GET /api/usuarios
    public function index(): JsonResponse
    {
        $usuarios = Usuario::select('id','nombre','apellido','email','rol','created_at')
                    ->get();

        return response()->json($usuarios);
    }

    // POST /api/usuarios
    public function store(StoreUsuarioRequest $request): JsonResponse
    {
        $usuario = Usuario::create($request->validated());
        return response()->json([
            'message' => 'Usuario creado correctamente.',
            'data'    => $usuario->only('id','nombre','apellido','email','rol','created_at'),
        ], 201);
    }

    // GET /api/usuarios/{usuario}
    public function show(Usuario $usuario): JsonResponse
    {
        return response()->json(
            $usuario->only('id','nombre','apellido','email','rol','created_at','updated_at')
        );
    }

    // PUT/PATCH /api/usuarios/{usuario}
    public function update(UpdateUsuarioRequest $request, Usuario $usuario): JsonResponse
    {
        $usuario->update($request->validated());
        return response()->json([
            'message' => 'Usuario actualizado correctamente.',
            'data'    => $usuario->only('id','nombre','apellido','email','rol','updated_at'),
        ]);
    }

    // DELETE /api/usuarios/{usuario}
    public function destroy(Usuario $usuario): JsonResponse
    {
        $usuario->delete();
        return response()->json(null, 204);
    }
}
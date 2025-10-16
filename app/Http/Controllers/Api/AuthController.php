<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUsuarioRequest;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // POST /api/auth/register
    public function register(StoreUsuarioRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Limpia campos según el rol (una sola tabla)
        if ($data['rol'] === 'viajero') {
            $data['empresa_nombre'] = null;
            $data['telefono'] = null;
            $data['ruc'] = null;
        } else { // proveedor
            $data['nombre'] = null;
            $data['apellido'] = null;
        }

        // Crea el usuario (el modelo hashea la contraseña automáticamente)
        $user = Usuario::create($data);

        // Token opcional: inicia sesión inmediatamente tras el registro
        $token = $user->createToken($request->input('device_name', 'api'))->plainTextToken;

        return response()->json([
            'message' => 'Registro exitoso.',
            'data' => [
                'user' => [
                    'id'              => $user->id,
                    'rol'             => $user->rol,
                    'nombre'          => $user->nombre,
                    'apellido'        => $user->apellido,
                    'empresa_nombre'  => $user->empresa_nombre,
                    'telefono'        => $user->telefono,
                    'ruc'             => $user->ruc,
                    'email'           => $user->email,
                    'created_at'      => $user->created_at,
                ],
                // 'token' => $token, // si no quieres autologin, elimina esta línea
            ],
        ], 201);
    }

    // POST /api/auth/login
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required','email','max:150'],
            'password' => ['required','string','min:6'],
            'device_name' => ['sometimes','string','max:100'], // opcional: Postman, Web, Android
        ]);

        $email = mb_strtolower(trim($credentials['email']));
        $user = Usuario::where('email', $email)->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Credenciales inválidas.'],
            ]);
        }

        $token = $user->createToken($credentials['device_name'] ?? 'api')->plainTextToken;

        return response()->json([
            'message' => 'Login exitoso.',
            'token'   => $token,
            'user'    => $user->only('id','nombre','apellido','email','rol','created_at'),
        ], 200);
    }

    // GET /api/auth/me  (protegido)
    public function me(Request $request): JsonResponse
    {
        $u = $request->user();

        return response()->json([
            'data' => $u->only('id','nombre','apellido','email','rol','created_at','updated_at'),
        ], 200);
    }

    // POST /api/auth/logout  (protegido)
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión cerrada.'], 200);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
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

        // (Opcional) exigir email verificado:
        // if (is_null($user->email_verified_at)) {
        //     return response()->json(['message' => 'Verifica tu correo antes de iniciar sesión.'], 403);
        // }

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

    // POST /api/auth/logout  (protegido) — invalida el token actual
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión cerrada.'], 200);
    }
}

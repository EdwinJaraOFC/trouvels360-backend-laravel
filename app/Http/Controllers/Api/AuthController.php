<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUsuarioRequest;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Log; // <--- AGREGA ESTA LÍNEA

class AuthController extends Controller
{
    /**
     * Devuelve un token CSRF y setea cookie 'XSRF-TOKEN' (NO HttpOnly).
     * El frontend debe enviar ese valor en el header 'X-XSRF-TOKEN'
     * en toda petición que cambie estado (POST/PUT/PATCH/DELETE).
     */
    public function csrf()
    {
        $token = bin2hex(random_bytes(32));

        return response()->json(['csrf_token' => $token])->cookie(
            'XSRF-TOKEN',                // name
            $token,                      // value
            60,                          // minutes
            '/',                         // path
            $this->cookieDomain(),       // domain
            $this->cookieSecure(),       // secure
            false,                       // httpOnly (debe ser legible por JS)
            false,                       // raw
            $this->cookieSameSite()      // sameSite
        );
    }

    /**
     * Registro de usuario (opcional: auto-login emitiendo cookie HttpOnly).
     * Requiere CSRF en la ruta.
     */
    public function register(StoreUsuarioRequest $request)
    {
        $data = $request->validated();

        // Limpiar campos según rol (una sola tabla)
        if (($data['rol'] ?? null) === 'viajero') {
            $data['empresa_nombre'] = null;
            $data['telefono']       = null;
            $data['ruc']            = null;
        } else { // proveedor
            $data['nombre']   = null;
            $data['apellido'] = null;
        }

        // El modelo 'Usuario' tiene cast 'password' => 'hashed'
        $user = Usuario::create($data);

        // Auto-login inmediato (si no deseas, comenta estas 6 líneas)
        $token = auth('api')->login($user);
        $ttl   = auth('api')->factory()->getTTL();

        $cookie = $this->makeAccessCookie($token, $ttl);

        return response()->json([
            'message'    => 'Registro exitoso.',
            'expires_in' => $ttl * 60,
            'data'       => [
                'user' => [
                    'id'             => $user->id,
                    'rol'            => $user->rol,
                    'nombre'         => $user->nombre,
                    'apellido'       => $user->apellido,
                    'empresa_nombre' => $user->empresa_nombre,
                    'telefono'       => $user->telefono,
                    'ruc'            => $user->ruc,
                    'email'          => $user->email,
                    'created_at'     => $user->created_at,
                ],
            ],
        ])->withCookie($cookie);
    }

    /**
     * Login: valida credenciales, genera JWT y setea cookie HttpOnly 'access_token'.
     * Requiere CSRF (agregado por el middleware 'csrf.api' en la ruta).
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->only('email', 'password'), [
            'email'    => ['required','email'],
            'password' => ['required','string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Datos inválidos',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Intenta autenticar con el guard 'api' (driver jwt)
        if (!$token = auth('api')->attempt($validator->validated())) {
            return response()->json(['message' => 'Credenciales inválidas'], 401);
        }

        $ttl = auth('api')->factory()->getTTL();
        $cookie = $this->makeAccessCookie($token, $ttl);

        return response()->json([
            'message'    => 'Login OK',
            'expires_in' => $ttl * 60, // segundos
            'data'       => [
                'user' => auth('api')->user(),
            ],
        ])->withCookie($cookie);
    }

    /**
     * Usuario autenticado (requiere jwt.cookie + jwt.auth).
     */
    public function me()
    {
        return response()->json(['data' => auth('api')->user()]);
    }

    /**
     * Refresca el JWT y setea nueva cookie HttpOnly 'access_token'.
     * Requiere CSRF.
     */
    public function refresh()
    {
        $newToken = auth('api')->refresh();
        $ttl      = auth('api')->factory()->getTTL();

        $cookie = $this->makeAccessCookie($newToken, $ttl);

        return response()->json([
            'message'    => 'Token refrescado',
            'expires_in' => $ttl * 60,
        ])->withCookie($cookie);
    }

    /**
     * Invalida el token actual (blacklist) y borra la cookie 'access_token'.
     * Requiere CSRF.
     */
    public function logout()
    {
        try {
            auth('api')->invalidate(true); // blacklist si está habilitada
        } catch (\Throwable $e) {
            // si ya expiró/no válido, igual borramos cookie
        }

        $forget = cookie(
            'access_token', '', -1, '/', $this->cookieDomain(),
            $this->cookieSecure(), true, false, $this->cookieSameSite()
        );

        return response()->json(['message' => 'Logout OK'])->withCookie($forget);
    }

    // ===================== Helpers de cookies =====================

    private function makeAccessCookie(string $token, int $ttlMinutes)
    {
        return cookie(
            'access_token',               // name
            $token,                       // value
            $ttlMinutes,                  // minutes
            '/',                          // path
            $this->cookieDomain(),        // domain
            $this->cookieSecure(),        // secure (true en prod/https)
            true,                         // httpOnly
            false,                        // raw
            $this->cookieSameSite()       // sameSite
        );
    }

    private function cookieSameSite(): string
    {
        return env('COOKIE_SAMESITE', 'Strict'); // 'Strict' | 'Lax' | 'None'
    }

    private function cookieDomain(): ?string
    {
        $domain = env('COOKIE_DOMAIN');
        return $domain !== null && $domain !== '' ? $domain : null;
    }

    private function cookieSecure(): bool
    {
        return filter_var(env('COOKIE_SECURE', app()->environment('production')), FILTER_VALIDATE_BOOL);
    }

    public function tokenFromCookie(Request $request)
    {
        // 1) Asegurarnos de que hay cookie 'access_token'
        $cookieToken = $request->cookie('access_token');
    
        if (!$cookieToken) {
            return response()->json([
                'message' => 'No hay token en la cookie'
            ], 401);
        }
    
        try {
            // 2) Gracias a los middlewares jwt.cookie + jwt.auth,
            // aquí ya debería haber un usuario autenticado.
            $user = auth('api')->user();
    
            if (!$user) {
                return response()->json([
                    'message' => 'Token inválido o usuario no encontrado'
                ], 401);
            }
    
            // 3) Construimos el payload específico para FastAPI
            $now = time();
            $ttlMs = (int) env('JWT_MS_TTL', 60); // minutos
    
            $payload = [
                'sub'   => (string) $user->id,
                'email' => $user->email,
                'rol'   => $user->rol ?? null,
    
                // Buenas prácticas de JWT:
                'iss' => config('app.url') ?? 'laravel-api',
                'aud' => 'fastapi-itinerarios',
                'iat' => $now,
                'nbf' => $now,
                'exp' => $now + ($ttlMs * 60),
            ];
    
            // 4) Firmamos el token con OTRA clave (solo para FastAPI)
            $msSecret = env('JWT_MS_SECRET');
            if (!$msSecret) {
                return response()->json([
                    'message' => 'Falta configurar JWT_MS_SECRET en el .env'
                ], 500);
            }
    
            $msToken = JWT::encode($payload, $msSecret, 'HS256');
    
            // 5) Devolvemos SOLO este token (no el de la cookie)
            return response()->json([
                'access_token' => $msToken,
                'token_type'   => 'Bearer',
                'expires_in'   => $ttlMs * 60,
                'user'         => [
                    'id'     => $user->id,
                    'email'  => $user->email,
                    'rol'    => $user->rol ?? null,
                    'nombre' => $user->nombre ?? null,
                ],
            ]);
    
        } catch (\Throwable $e) {
            Log::error("Error generando token para microservicio: " . $e->getMessage());

            return response()->json([
                'message' => 'Token inválido'
            ], 401);
        }
    }
        
}

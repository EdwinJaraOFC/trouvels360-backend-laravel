<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class JwtAuthController extends Controller
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
            // name
            'XSRF-TOKEN',
            // value
            $token,
            // minutes
            60, // puedes ajustarlo
            // path
            '/',
            // domain
            $this->cookieDomain(),
            // secure
            $this->cookieSecure(),
            // httpOnly
            false, // debe ser legible por JS para doble-submit
            // raw
            false,
            // sameSite
            $this->cookieSameSite()
        );
    }

    /**
     * Inicia sesión: valida credenciales, genera JWT y setea cookie HttpOnly 'access_token'.
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

        // TTL en minutos (desde config/jwt.php o env)
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
     * Retorna el usuario autenticado (usando cookie HttpOnly + middleware jwt.cookie).
     */
    public function me()
    {
        return response()->json([
            'data' => auth('api')->user(),
        ]);
    }

    /**
     * Rota el token (refresh) y setea una nueva cookie HttpOnly 'access_token'.
     * Requiere CSRF.
     */
    public function refresh()
    {
        // Nota: refresh() falla si el token no es refrescable (expirado fuera de refresh_ttl, etc.)
        $newToken = auth('api')->refresh();
        $ttl = auth('api')->factory()->getTTL();

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
            auth('api')->invalidate(true); // coloca token en blacklist (si está habilitada)
        } catch (\Throwable $e) {
            // Si ya expiró/no es válido, continuamos a borrar cookie igual
        }

        // Borrar cookie 'access_token' (expirarla en pasado)
        $forget = cookie(
            'access_token',
            '',
            -1, // expira ya
            '/',
            $this->cookieDomain(),
            $this->cookieSecure(),
            true, // HttpOnly
            false,
            $this->cookieSameSite()
        );

        return response()->json(['message' => 'Logout OK'])->withCookie($forget);
    }

    // ===================== Helpers de cookies =====================

    private function makeAccessCookie(string $token, int $ttlMinutes)
    {
        return cookie(
            // name
            'access_token',
            // value
            $token,
            // minutes
            $ttlMinutes,
            // path
            '/',
            // domain
            $this->cookieDomain(),
            // secure (solo HTTPS en prod)
            $this->cookieSecure(),
            // httpOnly (bloquea acceso desde JS)
            true,
            // raw
            false,
            // sameSite
            $this->cookieSameSite()
        );
    }

    /**
     * Determina SameSite para la cookie del token.
     * - Si tu SPA y API están en el mismo dominio/subdominio → 'Strict' recomendado.
     * - Si están en dominios distintos → 'None' (y Secure=true).
     */
    private function cookieSameSite(): string
    {
        // Puedes centralizar esto en config si prefieres:
        // return config('session.same_site', 'lax');
        return env('COOKIE_SAMESITE', 'Strict'); // 'Strict' | 'Lax' | 'None'
    }

    /**
     * Devuelve el dominio para la cookie según tu configuración.
     * Ejemplo: '.tudominio.com' para subdominios, o null si usas localhost.
     */
    private function cookieDomain(): ?string
    {
        $domain = env('COOKIE_DOMAIN');
        return $domain !== null && $domain !== '' ? $domain : null;
    }

    /**
     * Fuerza Secure en producción (HTTPS).
     */
    private function cookieSecure(): bool
    {
        // Si defines COOKIE_SECURE en .env lo respeta; si no, usa app()->environment('production')
        return filter_var(env('COOKIE_SECURE', app()->environment('production')), FILTER_VALIDATE_BOOL);
    }
}

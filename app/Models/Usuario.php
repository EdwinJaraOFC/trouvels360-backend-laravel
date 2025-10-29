<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// Mantener por ahora; se quitará cuando apaguemos Sanctum
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

/** Modelo 'usuarios' listo para login futuro (sin soft delete). */
class Usuario extends Authenticatable implements JWTSubject
{
    use HasApiTokens, Notifiable, HasFactory;

    protected $table = 'usuarios';

    protected $fillable = [
        'nombre', 'apellido', 'email', 'password', 'rol',
        'empresa_nombre', 'telefono', 'ruc',
    ];

    protected $hidden = ['password','remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'email_verified_at' => 'datetime',
        ];
    }

    // Normaliza email antes de guardar
    public function setEmailAttribute(string $value): void
    {
        $this->attributes['email'] = mb_strtolower(trim($value));
    }

    // Relación con reviews
    public function reviews()
    {
        return $this->hasMany(Review::class, 'usuario_id', 'id')
                    ->orderBy('created_at', 'desc');
    }

    // ==== JWTSubject ====
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [
            'rol' => $this->rol ?? null,
            'email' => $this->email ?? null,
        ];
    }
}

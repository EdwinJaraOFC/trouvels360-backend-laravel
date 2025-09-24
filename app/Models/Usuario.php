<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// Si NO usarás Sanctum aún, puedes quitar HasApiTokens:
use Laravel\Sanctum\HasApiTokens;

/** Modelo 'usuarios' listo para login futuro (sin soft delete). */
class Usuario extends Authenticatable
{
    use HasApiTokens, Notifiable, HasFactory;

    protected $table = 'usuarios';

    protected $fillable = ['nombre','apellido','email','password','rol'];

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
}

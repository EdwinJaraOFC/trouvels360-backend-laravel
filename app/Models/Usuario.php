<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasApiTokens, Notifiable, HasFactory;

    protected $table = 'usuarios';

    // IMPORTANTE: agrega aquí los campos de proveedor cuando migres
    protected $fillable = [
        'nombre', 'apellido', 'email', 'password', 'rol',
        'empresa_nombre', 'telefono', 'ruc', // <-- nuevos (nullable)
    ];

    protected $hidden = ['password','remember_token'];

    // En Laravel 10+ puedes usar este método o la propiedad $casts: ambos sirven
    protected function casts(): array
    {
        return [
            'password' => 'hashed',          // hashea automáticamente al asignar
            'email_verified_at' => 'datetime',
        ];
    }

    // Normaliza email antes de guardar
    public function setEmailAttribute(string $value): void
    {
        $this->attributes['email'] = mb_strtolower(trim($value));
    }
}
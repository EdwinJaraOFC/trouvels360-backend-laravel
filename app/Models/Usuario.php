<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @use HasFactory<\Database\Factories\UsuarioFactory>
 */
class Usuario extends Model
{
    use HasFactory;

    protected $table = 'usuarios'; // Nombre de la tabla en la base de datos

    /**
     * Atributos que se pueden asignar masivamente.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nombre',
        'apellido',
        'email',
        'password',
        'rol',
    ];

    /**
     * Atributos que deben permanecer ocultos al serializar (ej. JSON).
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Atributos que deben convertirse automáticamente a otro tipo.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed', // El password se encripta automáticamente
        ];
    }
}

<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Usuario>
 */
class UsuarioFactory extends Factory
{
    /**
     * Definir el estado por defecto del modelo.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre'   => $this->faker->firstName(),   // Nombre aleatorio
            'apellido' => $this->faker->lastName(),    // Apellido aleatorio
            'email'    => $this->faker->unique()->safeEmail(), // Email único
            'password' => Hash::make('password'),      // Contraseña por defecto encriptada
            'rol'      => $this->faker->randomElement(['viajero', 'proveedor']), // Rol aleatorio
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/** Factory de 'usuarios'. */
class UsuarioFactory extends Factory
{
    protected $model = Usuario::class;

    public function definition(): array
    {
        return [
            'nombre'   => $this->faker->firstName(),
            'apellido' => $this->faker->lastName(),
            'email'    => $this->faker->unique()->safeEmail(),
            'password' => 'password', // se hashea por el cast del modelo
            'rol'      => $this->faker->randomElement(['viajero', 'proveedor']),
        ];
    }

    /** Estado: viajero */
    public function viajero(): self
    {
        return $this->state(fn () => ['rol' => 'viajero']);
    }

    /** Estado: proveedor */
    public function proveedor(): self
    {
        return $this->state(fn () => ['rol' => 'proveedor']);
    }
}

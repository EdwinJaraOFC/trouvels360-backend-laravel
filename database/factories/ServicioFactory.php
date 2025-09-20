<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Servicio;
use App\Models\Usuario;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Servicio>
 */
class ServicioFactory extends Factory
{
    protected $model = Servicio::class;

    public function definition(): array
    {
        return [
            'proveedor_id' => Usuario::factory()->state(['rol' => 'proveedor']),
            'nombre'       => $this->faker->company . ' Service',
            'tipo'         => $this->faker->randomElement(['hotel', 'tour']),
            'descripcion'  => $this->faker->sentence(10),
            'ciudad'       => $this->faker->city,
            'horario_inicio' => $this->faker->time('H:i:s'),
            'horario_fin'    => $this->faker->time('H:i:s'),
            'imagen_url'   => $this->faker->imageUrl(640, 480, 'travel', true),
        ];
    }
}

<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Tour;
use App\Models\Servicio;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tour>
 */
class TourFactory extends Factory
{
    protected $model = Tour::class;

    public function definition(): array
    {
        return [
            'servicio_id'        => Servicio::factory()->state(['tipo' => 'tour']),
            'categoria'          => $this->faker->randomElement(['Aventura', 'Gastronomía', 'Cultural', 'Naturaleza']),
            'duracion'           => $this->faker->randomElement(['2 horas', '4 horas', '1 día']),
            'precio_por_persona' => $this->faker->randomFloat(2, 15, 200),
        ];
    }
}

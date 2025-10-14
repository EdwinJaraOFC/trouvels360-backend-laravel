<?php

namespace Database\Factories;

use App\Models\Tour;
use App\Models\Servicio;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tour>
 */
class TourFactory extends Factory
{
    protected $model = Tour::class;

    public function definition(): array
    {
        return [
            // Crea el Servicio asociado (tipo = 'tour') y toma su id como PK del Tour
            'servicio_id'          => Servicio::factory()->state(['tipo' => 'tour']),
            'categoria'            => $this->faker->randomElement(['Aventura','Gastronomía','Cultura','Relajación']),
            'fecha'             => $this->faker->dateTimeBetween('now', '+6 months')->format('Y-m-d'),
            'duracion'         => $this->faker->numberBetween(120, 480), // 2h a 8h
            'precio'       => $this->faker->randomFloat(2, 20, 150),
            'cupos'     => $this->faker->numberBetween(8, 40),    // default para salidas
            'cosas_para_llevar' => json_encode(['Camara', 'Protector solar','Linterna']),
        ];
    }
}

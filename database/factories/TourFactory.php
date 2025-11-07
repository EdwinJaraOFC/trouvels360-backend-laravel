<?php

namespace Database\Factories;

use App\Models\Tour;
use App\Models\Servicio;
use App\Models\TourItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class TourFactory extends Factory
{
    protected $model = Tour::class;

    public function definition(): array
    {
        return [
            // Crea el Servicio asociado (tipo = 'tour') y usa su id como PK del Tour
            'servicio_id'       => Servicio::factory()->state(['tipo' => 'tour']),
            'categoria'         => $this->faker->randomElement(['Gastronomía','Aventura','Cultura','Relajación']),
            'duracion'          => $this->faker->numberBetween(120, 480), // 2h–8h
            'precio'            => $this->faker->randomFloat(2, 20, 150),
        ];
    }
    /**
     * Hook para crear items automáticamente después de crear el tour
     */
    public function configure(): self
    {
        return $this->afterCreating(function (Tour $tour) {
            // Creamos entre 2 y 4 items por tour
            TourItem::factory()
                ->count(rand(2, 4))
                ->forServicio($tour->servicio_id)
                ->create();
        });
    }
}

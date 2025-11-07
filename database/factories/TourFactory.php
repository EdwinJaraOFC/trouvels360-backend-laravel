<?php

namespace Database\Factories;

use App\Models\Tour;
use App\Models\Servicio;
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
            // Importante: como array (el cast en el modelo lo convertirá a JSON)
            'cosas_para_llevar' => $this->faker->randomElements(
                ['Cámara', 'Bloqueador solar', 'Gorra', 'Zapatillas', 'Agua', 'Gafas de sol'],
                rand(2, 4)
            ),
        ];
    }
}

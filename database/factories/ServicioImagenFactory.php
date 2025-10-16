<?php

namespace Database\Factories;

use App\Models\ServicioImagen;
use App\Models\Servicio;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServicioImagenFactory extends Factory
{
    protected $model = ServicioImagen::class;

    public function definition(): array
    {
        return [
            'servicio_id' => Servicio::factory(),
            'url' => $this->faker->imageUrl(800, 450, $this->faker->randomElement(['travel', 'hotel', 'tour']), true),
            'alt' => $this->faker->words(3, true),
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Hotel;
use App\Models\Servicio;
use Illuminate\Database\Eloquent\Factories\Factory;

class HotelFactory extends Factory
{
    protected $model = Hotel::class;

    public function definition(): array
    {
        return [
            // Cada hotel está asociado a un servicio de tipo "hotel"
            'servicio_id' => Servicio::factory()->hotel(),
            'direccion'   => $this->faker->address(),
            'estrellas'   => $this->faker->numberBetween(1, 5),
        ];
    }
}

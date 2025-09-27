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
        // Cada hotel está asociado a un servicio de tipo "hotel"
        return [
            'servicio_id' => Servicio::factory()->state(['tipo' => 'hotel']),
            'nombre'      => $this->faker->company() . ' Hotel',
            'direccion'   => $this->faker->address(),
            'estrellas'   => $this->faker->numberBetween(1, 5),
        ];
    }
}

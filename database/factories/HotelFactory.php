<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Hotel;
use App\Models\Servicio;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Hotel>
 */
class HotelFactory extends Factory
{
    protected $model = Hotel::class;

    public function definition(): array
    {
        return [
            'servicio_id'       => Servicio::factory()->state(['tipo' => 'hotel']),
            'direccion'         => $this->faker->address,
            'estrellas'         => $this->faker->numberBetween(1, 5),
            'precio_por_noche'  => $this->faker->randomFloat(2, 30, 300),
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Tour;
use App\Models\TourSalida;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TourSalida>
 */
class TourSalidaFactory extends Factory
{
    protected $model = TourSalida::class;

    public function definition(): array
    {
        // Creamos primero un Tour (que a su vez crea el Servicio tipo 'tour')
        $tour = Tour::factory()->create();

        // Definimos cupo_total tomando por defecto el del tour o un random si es null
        $cupoTotal = $tour->capacidad_por_salida ?? $this->faker->numberBetween(8, 40);

        return [
            'servicio_id'   => $tour->servicio_id,
            'fecha'         => $this->faker->dateTimeBetween('+1 days', '+2 months')->format('Y-m-d'),
            'hora'          => $this->faker->time('H:i:00'),
            'cupo_total'    => $cupoTotal,
            'cupo_reservado'=> 0,
            'estado'        => 'programada', // programada | cerrada | cancelada
        ];
    }

    public function cerrada(): self
    {
        return $this->state(fn () => ['estado' => 'cerrada']);
    }

    public function cancelada(): self
    {
        return $this->state(fn () => ['estado' => 'cancelada']);
    }
}

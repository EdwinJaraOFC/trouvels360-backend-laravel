<?php

namespace Database\Factories;

use App\Models\Tour;
use App\Models\TourItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TourItem>
 */
class TourItemFactory extends Factory
{
    protected $model = TourItem::class;

    public function definition(): array
    {
        $tour = Tour::inRandomOrder()->first() ?? Tour::factory()->create();
        $servicioId = $tour->servicio_id;

        // Posibles items y emojis
        $items = [
            ['nombre' => 'Cámara',          'icono' => '📷'],
            ['nombre' => 'Bloqueador solar','icono' => '🧴'],
            ['nombre' => 'Gorra',           'icono' => '🧢'],
            ['nombre' => 'Zapatillas',      'icono' => '👟'],
            ['nombre' => 'Agua',            'icono' => '💧'],
            ['nombre' => 'Gafas de sol',    'icono' => '🕶️'],
        ];

        // Seleccionamos 1 item aleatorio del array
        $item = $this->faker->randomElement($items);

        return [
            'servicio_id' => $servicioId,
            'nombre'      => $item['nombre'],
            'icono'       => $item['icono'],
        ];
    }

    /**
     * Conveniencia para forzar el servicio objetivo sin crear Tour nuevo.
     */
    public function forServicio(int $servicioId): self
    {
        return $this->state(fn () => ['servicio_id' => $servicioId]);
    }
}

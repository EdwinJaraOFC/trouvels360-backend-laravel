<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Servicio;
use App\Models\Usuario;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Servicio>
 */
class ServicioFactory extends Factory
{
    protected $model = Servicio::class;

    public function definition(): array
    {
        return [
            // Crea proveedor automáticamente si no se pasa uno
            'proveedor_id' => Usuario::factory()->state(['rol' => 'proveedor']),

            'nombre'      => $this->faker->company . ' Service',
            'tipo'        => $this->faker->randomElement(['hotel', 'tour']),
            'descripcion' => $this->faker->sentence(10),
            'ciudad'      => $this->faker->city,
            'pais'        => $this->faker->country,                 // <-- agregado
            'imagen_url'  => $this->faker->imageUrl(640, 480, 'travel', true),
            'activo'      => true,
        ];
    }

    /** Estado para forzar tipo hotel */
    public function hotel(): self
    {
        return $this->state(fn () => ['tipo' => 'hotel']);
    }

    /** Estado para forzar tipo tour */
    public function tour(): self
    {
        return $this->state(fn () => ['tipo' => 'tour']);
    }
}

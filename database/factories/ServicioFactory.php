<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Servicio;
use App\Models\Usuario;
use App\Models\ServicioImagen;

class ServicioFactory extends Factory
{
    protected $model = Servicio::class;

    public function definition(): array
    {
        return [
            'proveedor_id' => Usuario::factory()->state(['rol' => 'proveedor']),
            'nombre'       => $this->faker->company . ' Service',
            'tipo'         => $this->faker->randomElement(['hotel', 'tour']),
            'descripcion'  => $this->faker->sentence(10),
            'ciudad'       => $this->faker->city,
            'pais'         => $this->faker->country,
            'imagen_url'   => $this->faker->imageUrl(800, 450, 'travel', true),
            'activo'       => true,
        ];
    }

    /** Estado: tipo hotel */
    public function hotel(): self
    {
        return $this->state(fn () => ['tipo' => 'hotel']);
    }

    /** Estado: tipo tour */
    public function tour(): self
    {
        return $this->state(fn () => ['tipo' => 'tour']);
    }

    /**
     * Crea hasta N imágenes en servicio_imagenes (1:N).
     * Uso: Servicio::factory()->withImagenes(3)->create()
     */
    public function withImagenes(int $cantidad = 3): self
    {
        $cantidad = max(1, min($cantidad, 5));

        return $this->afterCreating(function (Servicio $servicio) use ($cantidad) {
            for ($i = 1; $i <= $cantidad; $i++) {
                ServicioImagen::create([
                    'servicio_id' => $servicio->id,
                    'url'         => $this->faker->imageUrl(800, 450, 'travel', true),
                    'alt'         => $this->faker->words(3, true),
                ]);
            }
        });
    }
}

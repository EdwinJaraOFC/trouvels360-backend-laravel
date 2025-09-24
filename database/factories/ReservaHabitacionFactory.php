<?php

namespace Database\Factories;

use App\Models\ReservaHabitacion;
use App\Models\Usuario;
use App\Models\Habitacion;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReservaHabitacionFactory extends Factory
{
    protected $model = ReservaHabitacion::class;

    public function definition(): array
    {
        $fechaInicio = $this->faker->dateTimeBetween('+1 days', '+1 month');
        $fechaFin = (clone $fechaInicio)->modify('+' . $this->faker->numberBetween(1, 7) . ' days');
        $precio = $this->faker->randomFloat(2, 50, 500);
        $cantidad = $this->faker->numberBetween(1, 3);

        return [
            'codigo_reserva'  => strtoupper($this->faker->bothify('RES-####')),
            'usuario_id'      => Usuario::factory()->viajero(), // siempre viajero
            'habitacion_id'   => Habitacion::factory(),
            'fecha_inicio'    => $fechaInicio,
            'fecha_fin'       => $fechaFin,
            'cantidad'        => $cantidad,
            'estado'          => $this->faker->randomElement(['pendiente','confirmada','cancelada']),
            'precio_por_noche'=> $precio,
            'total'           => $precio * $cantidad * $this->faker->numberBetween(1, 5), // días
        ];
    }
}

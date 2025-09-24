<?php

namespace Database\Seeders;

use App\Models\Usuario;
use App\Models\Servicio;
use App\Models\Hotel;
use App\Models\Habitacion;
use App\Models\ReservaHabitacion;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // --- Usuarios fijos ---
        $proveedor = Usuario::factory()->proveedor()->create([
            'nombre'   => 'Edwin',
            'apellido' => 'Proveedor',
            'email'    => 'edwinproveedor@gmail.com',
            'password' => 'proveedor', // se hashea por el cast
        ]);

        Usuario::factory()->viajero()->create([
            'nombre'   => 'Edwin',
            'apellido' => 'Viajero',
            'email'    => 'edwinviajero@gmail.com',
            'password' => 'viajero',
        ]);

        // Lotes adicionales de usuarios
        Usuario::factory()->viajero()->count(5)->create();
        Usuario::factory()->proveedor()->count(5)->create();

        // --- Servicios / Hoteles / Habitaciones ---
        // Servicio fijo + Hotel asociado
        $servicioHotel = Servicio::factory()->create([
            'proveedor_id' => $proveedor->id,
            'nombre'       => 'Hotel Cayetano',
            'tipo'         => 'hotel',
            'ciudad'       => 'Lima',
            'descripcion'  => 'Un hotel de prueba para el seeder.',
        ]);

        $hotel = Hotel::factory()->create([
            'servicio_id' => $servicioHotel->id,
        ]);

        // Habitaciones del hotel
        $habitaciones = Habitacion::factory()->count(5)->create([
            'servicio_id' => $hotel->servicio_id,
        ]);

        // Reservas de esas habitaciones
        $habitaciones->each(function ($habitacion) {
            ReservaHabitacion::factory()->count(3)->create([
                'habitacion_id' => $habitacion->id,
            ]);
        });

        // --- Servicios adicionales ---
        // Lote aleatorio de hoteles y tours
        Servicio::factory()->count(10)->create();
    }
}

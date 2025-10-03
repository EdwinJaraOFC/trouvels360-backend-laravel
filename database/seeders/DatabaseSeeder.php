<?php

namespace Database\Seeders;

use App\Models\Usuario;
use App\Models\Servicio;
use App\Models\Hotel;
use App\Models\Habitacion;
use App\Models\ReservaHabitacion;
use App\Models\Tour;
use App\Models\TourSalida;
use App\Models\TourActividad;
use App\Models\ReservaTour;
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
            'password' => 'proveedor', // asumiendo mutator que hashea
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
        $servicioHotel = Servicio::factory()->create([
            'proveedor_id' => $proveedor->id,
            'nombre'       => 'Hotel Cayetano',
            'tipo'         => 'hotel',
            'ciudad'       => 'Lima',
            'pais'         => 'Perú', // <-- añadido por nuevo esquema
            'descripcion'  => 'Un hotel de prueba para el seeder.',
        ]);

        $hotel = Hotel::factory()->create([
            'servicio_id' => $servicioHotel->id,
            // HotelFactory ya pone direccion y estrellas
        ]);

        $habitaciones = Habitacion::factory()->count(5)->create([
            'servicio_id' => $hotel->servicio_id,
        ]);

        $habitaciones->each(function ($habitacion) {
            ReservaHabitacion::factory()->count(3)->create([
                'habitacion_id' => $habitacion->id,
            ]);
        });

        // --- Servicios adicionales (mezcla hotel/tour aleatoria) ---
        Servicio::factory()->count(10)->create();

        // --- Tours (no tocar, lo de tours queda igual) ---
        $tour = Tour::factory()->create();

        TourSalida::factory()->count(3)->create([
            'servicio_id' => $tour->servicio_id,
        ]);

        for ($i = 1; $i <= 4; $i++) {
            TourActividad::factory()->orden($i)->create([
                'servicio_id' => $tour->servicio_id,
            ]);
        }

        TourSalida::factory()->count(2)->create()->each(function ($salida) {
            ReservaTour::factory()->count(3)->create([
                'salida_id' => $salida->id,
            ]);
        });
    }
}

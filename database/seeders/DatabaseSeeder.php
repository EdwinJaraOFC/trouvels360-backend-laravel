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
            // Para PROVEEDOR: nombre/apellido en null; usa empresa + teléfono + RUC
            'nombre'          => null,
            'apellido'        => null,
            'empresa_nombre'  => 'Edwin Proveedor SAC',
            'telefono'        => '+51 9' . fake()->numerify('########'), // +51 9########
            'ruc'             => (string) fake()->numerify('2##########'), // 11 dígitos
            'email'           => 'edwinproveedor@gmail.com',
            'password'        => 'proveedor', // se hashea por el cast del modelo
        ]);

        Usuario::factory()->viajero()->create([
            // Para VIAJERO: empresa/teléfono/RUC en null; usa nombre/apellido
            'nombre'          => 'Edwin',
            'apellido'        => 'Viajero',
            'empresa_nombre'  => null,
            'telefono'        => null,
            'ruc'             => null,
            'email'           => 'edwinviajero@gmail.com',
            'password'        => 'viajero',
        ]);

        // Lotes adicionales de usuarios (las factories ya generan los campos correctos)
        Usuario::factory()->viajero()->count(5)->create();
        Usuario::factory()->proveedor()->count(5)->create();


        // ---------------------------------------------------------
        // HOTELS PACK: 3 hoteles con varios tipos de habitaciones
        // ---------------------------------------------------------
        $hotelesConfig = [
            [
                'servicio' => [
                    'nombre'      => 'Hotel Cayetano',
                    'ciudad'      => 'Lima',
                    'pais'        => 'Perú',
                    'descripcion' => 'Un hotel de prueba para el seeder en Lima.',
                    'imagen_url'  => 'https://picsum.photos/seed/hotel-cayetano/640/480',
                ],
                'hotel' => [
                    'estrellas' => 4,
                ],
                'habitaciones' => [
                    ['nombre' => 'Simple',  'cap_adultos' => 1, 'cap_ninos' => 0, 'cantidad' => 12, 'precio' => 120.00, 'desc' => 'Ideal para viajeros solos.'],
                    ['nombre' => 'Doble',   'cap_adultos' => 2, 'cap_ninos' => 1, 'cantidad' => 15, 'precio' => 180.50, 'desc' => 'Cómoda para parejas.'],
                    ['nombre' => 'Familiar','cap_adultos' => 2, 'cap_ninos' => 2, 'cantidad' => 8,  'precio' => 250.00, 'desc' => 'Perfecta para familias pequeñas.'],
                    ['nombre' => 'Suite',   'cap_adultos' => 3, 'cap_ninos' => 1, 'cantidad' => 5,  'precio' => 350.00, 'desc' => 'Suite con sala y vista.'],
                ],
            ],
            [
                'servicio' => [
                    'nombre'      => 'Andes Boutique',
                    'ciudad'      => 'Cusco',
                    'pais'        => 'Perú',
                    'descripcion' => 'Boutique acogedor cerca del centro histórico.',
                    'imagen_url'  => 'https://picsum.photos/seed/andes-boutique/640/480',
                ],
                'hotel' => [
                    'estrellas' => 3,
                ],
                'habitaciones' => [
                    ['nombre' => 'Económica','cap_adultos' => 2, 'cap_ninos' => 0, 'cantidad' => 10, 'precio' => 90.00,  'desc' => 'Básica y funcional.'],
                    ['nombre' => 'Doble',    'cap_adultos' => 2, 'cap_ninos' => 1, 'cantidad' => 14, 'precio' => 140.00, 'desc' => 'Con desayuno incluido.'],
                    ['nombre' => 'Triple',   'cap_adultos' => 3, 'cap_ninos' => 0, 'cantidad' => 7,  'precio' => 190.00, 'desc' => 'Buen espacio para 3.'],
                ],
            ],
            [
                'servicio' => [
                    'nombre'      => 'Costa del Sol Arequipa',
                    'ciudad'      => 'Arequipa',
                    'pais'        => 'Perú',
                    'descripcion' => 'Hotel con piscina y vista al Misti.',
                    'imagen_url'  => 'https://picsum.photos/seed/costa-sol-arequipa/640/480',
                ],
                'hotel' => [
                    'estrellas' => 5,
                ],
                'habitaciones' => [
                    ['nombre' => 'Deluxe',  'cap_adultos' => 2, 'cap_ninos' => 1, 'cantidad' => 10, 'precio' => 220.00, 'desc' => 'Amplia, con balcón.'],
                    ['nombre' => 'Suite',   'cap_adultos' => 2, 'cap_ninos' => 2, 'cantidad' => 6,  'precio' => 380.00, 'desc' => 'Suite con jacuzzi.'],
                    ['nombre' => 'Family',  'cap_adultos' => 3, 'cap_ninos' => 2, 'cantidad' => 5,  'precio' => 420.00, 'desc' => '2 ambientes conectados.'],
                ],
            ],
        ];

        foreach ($hotelesConfig as $cfg) {
            // 1) Servicio tipo hotel
            $servicio = Servicio::factory()->create([
                'proveedor_id' => $proveedor->id,
                'nombre'       => $cfg['servicio']['nombre'],
                'tipo'         => 'hotel',
                'ciudad'       => $cfg['servicio']['ciudad'],
                'pais'         => $cfg['servicio']['pais'],
                'descripcion'  => $cfg['servicio']['descripcion'],
                'imagen_url'   => $cfg['servicio']['imagen_url'],
                'activo'       => true,
            ]);

            // 2) Hotel (detalle)
            $hotel = Hotel::factory()->create([
                'servicio_id' => $servicio->id,
                'estrellas'   => $cfg['hotel']['estrellas'],
                // 'direccion' la pone el factory; puedes setearla aquí si quieres
            ]);

            // 3) Habitaciones (varios tipos) para este hotel
            foreach ($cfg['habitaciones'] as $h) {
                $habitacion = Habitacion::factory()->create([
                    'servicio_id'       => $hotel->servicio_id,
                    'nombre'            => $h['nombre'] . ' Room',
                    'capacidad_adultos' => $h['cap_adultos'],
                    'capacidad_ninos'   => $h['cap_ninos'],
                    'cantidad'          => $h['cantidad'],
                    'precio_por_noche'  => $h['precio'],
                    'descripcion'       => $h['desc'],
                ]);

                // 4) Algunas reservas “de muestra” para cada tipo de habitación
                //    (te da datos para probar la disponibilidad)
                ReservaHabitacion::factory()->count(2)->create([
                    'habitacion_id' => $habitacion->id,
                ]);
            }
        }

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

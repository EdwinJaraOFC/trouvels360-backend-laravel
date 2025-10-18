<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Carbon\Carbon;

use App\Models\Usuario;
use App\Models\Servicio;
use App\Models\Hotel;
use App\Models\Habitacion;
use App\Models\ReservaHabitacion;
use App\Models\Tour;
use App\Models\TourSalida;
use App\Models\TourActividad;
use App\Models\ReservaTour;
use App\Models\Review;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // --- Usuarios fijos ---
        $proveedor = Usuario::factory()->proveedor()->create([
            'nombre'          => null,
            'apellido'        => null,
            'empresa_nombre'  => 'Edwin Proveedor SAC',
            'telefono'        => '+51 9' . fake()->numerify('########'),
            'ruc'             => (string) fake()->numerify('2##########'),
            'email'           => 'edwinproveedor@gmail.com',
            'password'        => 'proveedor',
        ]);

        Usuario::factory()->viajero()->create([
            'nombre'          => 'Edwin',
            'apellido'        => 'Viajero',
            'empresa_nombre'  => null,
            'telefono'        => null,
            'ruc'             => null,
            'email'           => 'edwinviajero@gmail.com',
            'password'        => 'viajero',
        ]);

        Usuario::factory()->viajero()->count(5)->create();
        Usuario::factory()->proveedor()->count(5)->create();

        // ---------------------------------------------------------
        // HOTELS PACK: 5 hoteles con varios tipos de habitaciones
        // ---------------------------------------------------------
        $hotelesConfig = [
            [
                'servicio' => [
                    'nombre'      => 'Hotel La Hacienda',
                    'ciudad'      => 'Lima',
                    'pais'        => 'Perú',
                    'descripcion' => 'Un hotel de prueba para el seeder en Lima.',
                    'imagen_url'  => 'https://image-tc.galaxy.tf/wijpeg-3ccyhqat2arc8mjzjlbe07ac7/miraflores.jpg?rotate=0&crop=0%2C148%2C632%2C606&width=1920',
                ],
                'hotel' => ['estrellas' => 4],
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
                    'imagen_url'  => 'https://dynamic-media-cdn.tripadvisor.com/media/photo-o/06/a1/51/71/best-western-los-andes.jpg?w=900&h=500&s=1',
                ],
                'hotel' => ['estrellas' => 3],
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
                    'imagen_url'  => 'https://cf.bstatic.com/xdata/images/hotel/max1024x768/448971419.jpg?k=3d2bb1cab24e3f17219c630d7d6a1cf36671d765075c7d1f487972a1530832e7&o=&hp=1',
                ],
                'hotel' => ['estrellas' => 5],
                'habitaciones' => [
                    ['nombre' => 'Deluxe',  'cap_adultos' => 2, 'cap_ninos' => 1, 'cantidad' => 10, 'precio' => 220.00, 'desc' => 'Amplia, con balcón.'],
                    ['nombre' => 'Suite',   'cap_adultos' => 2, 'cap_ninos' => 2, 'cantidad' => 6,  'precio' => 380.00, 'desc' => 'Suite con jacuzzi.'],
                    ['nombre' => 'Family',  'cap_adultos' => 3, 'cap_ninos' => 2, 'cantidad' => 5,  'precio' => 420.00, 'desc' => '2 ambientes conectados.'],
                ],
            ],
            // NUEVO 4: Trujillo
            [
                'servicio' => [
                    'nombre'      => 'Costa Verde Trujillo',
                    'ciudad'      => 'Trujillo',
                    'pais'        => 'Perú',
                    'descripcion' => 'Cerca a la playa de Huanchaco y al centro histórico.',
                    'imagen_url'  => 'https://cf.bstatic.com/xdata/images/hotel/max1024x768/219692318.jpg?k=9f1b51d5b9d3e2b2d9e4e5d3c9f8a111a3c94f0f6b2b3b1a',
                ],
                'hotel' => ['estrellas' => 4],
                'habitaciones' => [
                    ['nombre' => 'Standard', 'cap_adultos' => 2, 'cap_ninos' => 0, 'cantidad' => 18, 'precio' => 160.00, 'desc' => 'Confortable y funcional.'],
                    ['nombre' => 'Doble',    'cap_adultos' => 2, 'cap_ninos' => 1, 'cantidad' => 12, 'precio' => 210.00, 'desc' => 'Vista a la ciudad.'],
                    ['nombre' => 'Suite',    'cap_adultos' => 3, 'cap_ninos' => 1, 'cantidad' => 6,  'precio' => 360.00, 'desc' => 'Suite con sala y balcón.'],
                ],
            ],
            // NUEVO 5: Piura
            [
                'servicio' => [
                    'nombre'      => 'Piura Sun Resort',
                    'ciudad'      => 'Piura',
                    'pais'        => 'Perú',
                    'descripcion' => 'Resort con clima cálido todo el año y piscina.',
                    'imagen_url'  => 'https://cf.bstatic.com/xdata/images/hotel/max1024x768/122233123.jpg?k=4a2c0a0102030405060708090a0b0c0d',
                ],
                'hotel' => ['estrellas' => 4],
                'habitaciones' => [
                    ['nombre' => 'Bungalow', 'cap_adultos' => 2, 'cap_ninos' => 2, 'cantidad' => 10, 'precio' => 300.00, 'desc' => 'Bungalow privado con terraza.'],
                    ['nombre' => 'Doble',    'cap_adultos' => 2, 'cap_ninos' => 1, 'cantidad' => 14, 'precio' => 190.00, 'desc' => 'Con vista a la piscina.'],
                    ['nombre' => 'Familiar', 'cap_adultos' => 3, 'cap_ninos' => 2, 'cantidad' => 8,  'precio' => 280.00, 'desc' => 'Espaciosa para familias.'],
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
            ]);

            // 3) Habitaciones + reservas de muestra
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

                ReservaHabitacion::factory()->count(2)->create([
                    'habitacion_id' => $habitacion->id,
                ]);
            }
        }

        // ---------------------------------------------------------
        // Servicios adicionales (mezcla hotel/tour) – el factory completa imágenes
        // ---------------------------------------------------------
        Servicio::factory()
            ->count(10)
            ->create();

        // ---------------------------------------------------------
        // TOUR PACK: 1 tour con salidas, actividades, reservas (demo)
        // ---------------------------------------------------------
        $tour = Tour::factory()->create();

        // Salidas del tour “manual”
        TourSalida::factory()->count(3)->create([
            'servicio_id' => $tour->servicio_id,
        ]);

        // Actividades ordenadas
        for ($i = 1; $i <= 4; $i++) {
            TourActividad::factory()->orden($i)->create([
                'servicio_id' => $tour->servicio_id,
            ]);
        }

        // Reservas de tour ligadas a salidas recientes (demo)
        TourSalida::factory()->count(2)->create()->each(function ($salida) {
            ReservaTour::factory()->count(3)->create([
                'salida_id' => $salida->id,
            ]);
        });

        // ---------------------------------------------------------
        // 💡 NUEVO: Crear salidas para *todos* los tours existentes (si no tienen)
        // ---------------------------------------------------------
        $tours = Tour::query()->get();

        foreach ($tours as $t) {
            $servicioId = $t->servicio_id;
            $capacidad  = $t->capacidad_por_salida ?? 20;

            // Evitar duplicados si el tour ya tiene salidas
            $yaTiene = TourSalida::where('servicio_id', $servicioId)->exists();
            if ($yaTiene) {
                continue;
            }

            // 4 salidas semanales a partir de +3 días
            $base = Carbon::now()->addDays(3);
            $fechas = [
                $base->copy(),
                $base->copy()->addDays(7),
                $base->copy()->addDays(14),
                $base->copy()->addDays(21),
            ];

            foreach ($fechas as $f) {
                TourSalida::create([
                    'servicio_id'    => $servicioId,
                    'fecha'          => $f->format('Y-m-d'),
                    'hora'           => $f->setTime(rand(8, 15), [0, 30][rand(0,1)])->format('H:i:00'),
                    'cupo_total'     => $capacidad,
                    'cupo_reservado' => 0,
                    'estado'         => 'programada',
                ]);
            }
        }

        // ------- Reviews -------------
        $usuarioIds = Usuario::pluck('id');

        Servicio::all()->each(function ($servicio) use ($usuarioIds) {
            Review::factory()
                ->count(3)
                ->state(function () use ($servicio, $usuarioIds) {
                    return [
                        'servicio_id' => $servicio->id,
                        'usuario_id'  => $usuarioIds->random(),
                    ];
                })
                ->create();
        });
    }
}

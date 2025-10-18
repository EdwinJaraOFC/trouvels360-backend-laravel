<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\Usuario;
use App\Models\Servicio;
use App\Models\ServicioImagen;
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
        // HOTELS PACK: 3 hoteles con varios tipos de habitaciones
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
                'imagen_url'   => $cfg['servicio']['imagen_url'], // portada
                'activo'       => true,
            ]);

            // 1.1) Galería simple (3 imágenes)
            ServicioImagen::factory()->count(3)->for($servicio)->create();

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
        // Servicios adicionales (mezcla hotel/tour) con galería (3 imgs)
        // ---------------------------------------------------------
        Servicio::factory()
            ->count(10)
            ->has(ServicioImagen::factory()->count(3), 'imagenes')
            ->create();

        // ---------------------------------------------------------
        // TOUR PACK: 1 tour con salidas, actividades, reservas + galería
        // ---------------------------------------------------------
        $tour = Tour::factory()->create();

        // Galería del servicio del tour (3 imágenes simples)
        ServicioImagen::factory()->count(3)->for($tour->servicio)->create();

        // Salidas del tour
        TourSalida::factory()->count(3)->create([
            'servicio_id' => $tour->servicio_id,
        ]);

        // Actividades del tour ordenadas
        for ($i = 1; $i <= 4; $i++) {
            TourActividad::factory()->orden($i)->create([
                'servicio_id' => $tour->servicio_id,
            ]);
        }

        // Reservas de tour ligadas a salidas recientes
        TourSalida::factory()->count(2)->create()->each(function ($salida) {
            ReservaTour::factory()->count(3)->create([
                'salida_id' => $salida->id,
            ]);
        });

        // -------Reviews-------------
        $usuarioIds = Usuario::pluck('id'); 

        Servicio::all()->each(function ($servicio) use ($usuarioIds) {
            Review::factory()
                ->count(3) // generar 3 reseñas por servicio
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

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
        $proveedor = Usuario::firstOrCreate(
            ['email' => 'edwinproveedor@gmail.com'],
            [
                'nombre'          => null,
                'apellido'        => null,
                'empresa_nombre'  => 'Edwin Proveedor SAC',
                'telefono'        => '+51 9' . fake()->numerify('########'),
                'ruc'             => (string) fake()->numerify('2##########'),
                'password'        => 'proveedor',
                'rol'             => 'proveedor',
            ]
        );

        Usuario::firstOrCreate(
            ['email' => 'edwinviajero@gmail.com'],
            [
                'nombre'          => 'Edwin',
                'apellido'        => 'Viajero',
                'empresa_nombre'  => null,
                'telefono'        => null,
                'ruc'             => null,
                'password'        => 'viajero',
                'rol'             => 'viajero',
            ]
        );

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
                    'imagen_url'  => 'https://www.cataloniahotels.com/es/blog/wp-content/uploads/2016/11/catalonia-riviera-maya.jpg',
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
                    'imagen_url'  => 'https://www.turiweb.pe/wp-content/uploads/2021/10/hoteles-071021.jpg',
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
                    'imagen_url'  => 'https://www.viajes.cl/hubfs/Vista%20a%C3%A9rea%20del%20hotel%20Belmond%20Miraflores%20Park%20en%20Lima.jpg',
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
            // 1) Ahora el factory de Hotel crea el Servicio
            $hotel = Hotel::factory()->create([
                'estrellas'   => $cfg['hotel']['estrellas'],
            ]);
            // 2) Actualizar el Servicio creado automaticamente
            $hotel->servicio->update([
                'proveedor_id' => $proveedor->id,
                'nombre'       => $cfg['servicio']['nombre'],
                'tipo'         => 'hotel',
                'ciudad'       => $cfg['servicio']['ciudad'],
                'pais'         => $cfg['servicio']['pais'],
                'descripcion'  => $cfg['servicio']['descripcion'],
                'imagen_url'   => $cfg['servicio']['imagen_url'],
                'activo'       => true
            ]);

            // 3) Habitaciones + reservas de muestra
            $habitacionesCreadas = []; 

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
                $habitacionesCreadas[] = $habitacion;
                // Reservas aleatorias
                ReservaHabitacion::factory()->count(2)->create([
                    'habitacion_id' => $habitacion->id,
                ]);
            }
            // 4) Reservas específicas para el usuario fijo (ID = 2) en 3 habitaciones distintas
            $habitacionesParaUsuario = array_slice($habitacionesCreadas, 0, 3); // las primeras 3 habitaciones

            foreach ($habitacionesParaUsuario as $habitacion) {
                ReservaHabitacion::factory()->create([
                    'habitacion_id' => $habitacion->id,
                    'usuario_id'    => 2,
                ]);
            }
        }

        // ---------------------------------------------------------
        // Generar TOURS
        // ---------------------------------------------------------
        $tours = collect();
        // Crear 10 tours con Servicio e Items
        for ($i = 0; $i < 10; $i++) {
            $tour = Tour::factory()->createWithServicioAndItems();
            $tours->push($tour);
        }
        // Obtener 2 tours aleatorios y actualizar el proveedor
        $randomTours = $tours->random(2);

        foreach ($randomTours as $tour) {
            // Actualizar el servicio asociado
            $tour->servicio->update([
                'proveedor_id' => 1, // usuario fijo
            ]);
        }
        // ---------------------------------------------------------
        // Generar salidas, actividades y reservas para cada tour
        // ---------------------------------------------------------
        foreach ($tours as $tour) {
            $servicioId = $tour->servicio_id;
            $capacidad  = $tour->capacidad_por_salida ?? 20;

            // Evitar duplicados si el tour ya tiene salidas
            if (TourSalida::where('servicio_id', $servicioId)->exists()) {
                continue;
            }

            // Crear 2-3 salidas separadas por semanas
            $numSalidas = rand(2,3);
            $base = Carbon::now()->addDays(3);

            $salidas = [];
            for ($s = 0; $s < $numSalidas; $s++) {
                $fecha = $base->copy()->addDays($s*7); // separación semanal
                $hora  = $fecha->copy()->setTime(rand(8, 15), [0,30][rand(0,1)]);

                $salida = TourSalida::create([
                    'servicio_id'    => $servicioId,
                    'fecha'          => $fecha->format('Y-m-d'),
                    'hora'           => $hora->format('H:i:00'),
                    'cupo_total'     => $capacidad,
                    'cupo_reservado' => 0,
                    'estado'         => 'programada',
                ]);

                $salidas[] = $salida;
            }
            // Crear 4 actividades con orden
            for ($i = 1; $i <= 4; $i++) {
                TourActividad::factory()
                    ->orden($i)
                    ->forServicio($servicioId)
                    ->create();
            }

            // Crear reservas para cada salida
            foreach ($salidas as $salida) {
                // Crear 2-3 reservas normales
                ReservaTour::factory()->count(rand(2,3))->create([
                    'salida_id' => $salida->id,
                ]);
            }
        }

        // Crear 3 reservas fijas para el usuario 2 en salidas de tours al azar
        $salidasDisponibles = TourSalida::all()->shuffle()->take(3);
        foreach ($salidasDisponibles as $salida) {
            ReservaTour::factory()->create([
                'salida_id'  => $salida->id,
                'usuario_id' => 2,
            ]);
        }

        // ------- Reviews -------------
        $usuarioIds = Usuario::pluck('id');

        Servicio::all()->each(function ($servicio) use ($usuarioIds) {
            $numReviews = rand(0, 5); // de 0 a 5 reviews por servicio

            Review::factory()
                ->count($numReviews)
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

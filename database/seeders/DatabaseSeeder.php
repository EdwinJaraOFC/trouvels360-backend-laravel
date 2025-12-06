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
use App\Models\TourItem;
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
        // HOTELS PACK
        // ---------------------------------------------------------
        $hotelesConfig = [
            [
                'servicio' => [
                    'nombre'      => 'Hotel Casa Andina Premium Arequipa',
                    'ciudad'      => 'Arequipa',
                    'pais'        => 'Perú',
                    'descripcion' => 'Hotel colonial restaurado con vistas al volcán Misti.',
                    'imagen_url'  => 'https://images.unsplash.com/photo-1551882547-ff40c63fe5fa',
                    // 👇 Coordenadas de Miraflores, Lima
                    'latitud'     => -16.398866,
                    'longitud'    => -71.536960,
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
                    'nombre'      => 'Hotel Sonesta Posadas del Inca',
                    'ciudad'      => 'Arequipa',
                    'pais'        => 'Perú',
                    'descripcion' => 'Hotel acogedor cerca del Monasterio de Santa Catalina.',
                    'imagen_url'  => 'https://i.pinimg.com/736x/17/3c/05/173c05f44f9b2e1951092dc82508a014.jpg',
                    // 👇 Coordenadas de Plaza de Armas, Cusco
                    'latitud'     => -16.403964,
                    'longitud'    => -71.537460,
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
                    // 👇 Coordenadas de Arequipa
                    'latitud'     => -16.3988,
                    'longitud'    => -71.5350,
                ],
                'hotel' => ['estrellas' => 5],
                'habitaciones' => [
                    ['nombre' => 'Deluxe',  'cap_adultos' => 2, 'cap_ninos' => 1, 'cantidad' => 10, 'precio' => 220.00, 'desc' => 'Amplia, con balcón.'],
                    ['nombre' => 'Suite',   'cap_adultos' => 2, 'cap_ninos' => 2, 'cantidad' => 6,  'precio' => 380.00, 'desc' => 'Suite con jacuzzi.'],
                    ['nombre' => 'Family',  'cap_adultos' => 3, 'cap_ninos' => 2, 'cantidad' => 5,  'precio' => 420.00, 'desc' => '2 ambientes conectados.'],
                ],
            ],
            [
                'servicio' => [
                    'nombre'      => 'Hotel JW Marriott Lima',
                    'ciudad'      => 'Lima',
                    'pais'        => 'Perú',
                    'descripcion' => 'Hotel de lujo frente al mar en Miraflores.',
                    'imagen_url'  => 'https://i.pinimg.com/1200x/ee/60/79/ee60794865c23a30bc93c45274043a22.jpg',
                    // 👇 Coordenadas de Huanchaco, Trujillo
                    'latitud'     => -12.129158,
                    'longitud'    => -77.029980,
                ],
                'hotel' => ['estrellas' => 4],
                'habitaciones' => [
                    ['nombre' => 'Standard', 'cap_adultos' => 2, 'cap_ninos' => 0, 'cantidad' => 18, 'precio' => 160.00, 'desc' => 'Confortable y funcional.'],
                    ['nombre' => 'Doble',    'cap_adultos' => 2, 'cap_ninos' => 1, 'cantidad' => 12, 'precio' => 210.00, 'desc' => 'Vista a la ciudad.'],
                    ['nombre' => 'Suite',    'cap_adultos' => 3, 'cap_ninos' => 1, 'cantidad' => 6,  'precio' => 360.00, 'desc' => 'Suite con sala y balcón.'],
                ],
            ],
            [
                'servicio' => [
                    'nombre'      => 'Hotel Hilton Lima Miraflores',
                    'ciudad'      => 'Lima',
                    'pais'        => 'Perú',
                    'descripcion' => 'Hotel moderno ubicado en el corazón de Miraflores..',
                    'imagen_url'  => 'https://i.pinimg.com/1200x/e9/53/fa/e953fae175bdfde66e34fae0318f9dfc.jpg',
                    // 👇 Coordenadas de Piura
                    'latitud'     => -12.125205,
                    'longitud'    => -77.030151,
                ],
                'hotel' => ['estrellas' => 5],
                'habitaciones' => [
                    ['nombre' => 'Bungalow', 'cap_adultos' => 2, 'cap_ninos' => 2, 'cantidad' => 10, 'precio' => 300.00, 'desc' => 'Bungalow privado con terraza.'],
                    ['nombre' => 'Doble',    'cap_adultos' => 2, 'cap_ninos' => 2, 'cantidad' => 14, 'precio' => 190.00, 'desc' => 'Con vista a la piscina.'],
                    ['nombre' => 'Familiar', 'cap_adultos' => 3, 'cap_ninos' => 1, 'cantidad' => 8,  'precio' => 280.00, 'desc' => 'Espaciosa para familias.'],
                ],
            ],
            [
                'servicio' => [
                    'nombre'      => 'Palacio del Inka Hotel',
                    'ciudad'      => 'Cusco',
                    'pais'        => 'Perú',
                    'descripcion' => 'Hotel de lujo frente al Koricancha.',
                    'imagen_url'  => 'https://i.pinimg.com/736x/93/46/12/9346128f819e6f3f8e5502a10ba6f01a.jpg',
                    // 👇 Coordenadas de cusco
                    'latitud'     => -13.517088,
                    'longitud'    => -71.972212,
                ],
                'hotel' => ['estrellas' => 4],
                'habitaciones' => [
                    ['nombre' => 'Bungalow', 'cap_adultos' => 2, 'cap_ninos' => 2, 'cantidad' => 10, 'precio' => 310.00, 'desc' => 'Bungalow privado con terraza.'],
                    ['nombre' => 'Doble',    'cap_adultos' => 2, 'cap_ninos' => 1, 'cantidad' => 14, 'precio' => 199.00, 'desc' => 'Con vista a la piscina.'],
                    ['nombre' => 'Familiar', 'cap_adultos' => 3, 'cap_ninos' => 2, 'cantidad' => 8,  'precio' => 288.00, 'desc' => 'Espaciosa para familias.'],
                ],
            ],
            [
                'servicio' => [
                    'nombre'      => 'Casa Andina Premium Cusco',
                    'ciudad'      => 'Cusco',
                    'pais'        => 'Perú',
                    'descripcion' => 'Hotel histórico con arquitectura colonial.',
                    'imagen_url'  => 'https://i.pinimg.com/736x/c2/5a/73/c25a73d9fcdfb466236e4286cf944359.jpg',
                    // 👇 Coordenadas de cusco
                    'latitud'     => -13.521226,
                    'longitud'    => -71.968173,
                ],
                'hotel' => ['estrellas' => 3],
                'habitaciones' => [
                    ['nombre' => 'Bungalow', 'cap_adultos' => 4, 'cap_ninos' => 2, 'cantidad' => 10, 'precio' => 310.00, 'desc' => 'Bungalow privado con terraza.'],
                    ['nombre' => 'Doble',    'cap_adultos' => 1, 'cap_ninos' => 1, 'cantidad' => 14, 'precio' => 199.00, 'desc' => 'Con vista a la piscina.'],
                    ['nombre' => 'Familiar', 'cap_adultos' => 2, 'cap_ninos' => 2, 'cantidad' => 8,  'precio' => 288.00, 'desc' => 'Espaciosa para familias.'],
                ],
            ],
            [
                'servicio' => [
                    'nombre'      => 'Hotel Tierra Viva Puno Plaza',
                    'ciudad'      => 'Puno',
                    'pais'        => 'Perú',
                    'descripcion' => 'Hotel moderno cerca del Lago Titicaca.',
                    'imagen_url'  => 'https://i.pinimg.com/1200x/64/a6/6c/64a66cf8b1827fb4e7285b4b86107a8d.jpg',
                    // 👇 Coordenadas de puno
                
                    'latitud'     => -15.842231,
                    'longitud'    => -70.019951,
                ],
                'hotel' => ['estrellas' => 4],
                'habitaciones' => [
                    ['nombre' => 'Bungalow', 'cap_adultos' => 4, 'cap_ninos' => 2, 'cantidad' => 10, 'precio' => 310.00, 'desc' => 'Bungalow privado con terraza.'],
                    ['nombre' => 'Doble',    'cap_adultos' => 1, 'cap_ninos' => 1, 'cantidad' => 14, 'precio' => 199.00, 'desc' => 'Con vista a la piscina.'],
                    ['nombre' => 'Familiar', 'cap_adultos' => 2, 'cap_ninos' => 2, 'cantidad' => 8,  'precio' => 288.00, 'desc' => 'Espaciosa para familias.'],
                ],
            ],
            [
                'servicio' => [
                    'nombre'      => 'GHL Hotel Lago Titicaca',
                    'ciudad'      => 'Puno',
                    'pais'        => 'Perú',
                    'descripcion' => 'Hotel moderno de Puno',
                    'imagen_url'  => 'https://i.pinimg.com/1200x/61/38/05/613805dc14c5335a8c8cc659836f1c12.jpg',
                    // 👇 Coordenadas de puno
                
                    'latitud'     => -15.837836,
                    'longitud'    => -69.996559,
                ],
                'hotel' => ['estrellas' => 3],
                'habitaciones' => [
                    ['nombre' => 'Bungalow', 'cap_adultos' => 4, 'cap_ninos' => 2, 'cantidad' => 10, 'precio' => 310.00, 'desc' => 'Bungalow privado con terraza.'],
                    ['nombre' => 'Doble',    'cap_adultos' => 1, 'cap_ninos' => 1, 'cantidad' => 14, 'precio' => 199.00, 'desc' => 'Con vista a la piscina.'],
                    ['nombre' => 'Familiar', 'cap_adultos' => 2, 'cap_ninos' => 2, 'cantidad' => 8,  'precio' => 288.00, 'desc' => 'Espaciosa para familias.'],
                ],
            ],
            [
                'servicio' => [
                    'nombre'      => 'Hotel Casa Andina Piura',
                    'ciudad'      => 'Piura',
                    'pais'        => 'Perú',
                    'descripcion' => 'Hotel cómodo en el centro de Piura.',
                    'imagen_url'  => 'https://i.pinimg.com/736x/82/58/61/825861671abedba8b2b4a2a3277af732.jpg',
                    // 👇 Coordenadas de puno
                
                    'latitud'     => -5.194490,
                    'longitud'    => -80.632820,
                ],
                'hotel' => ['estrellas' => 5],
                'habitaciones' => [
                    ['nombre' => 'Bungalow', 'cap_adultos' => 4, 'cap_ninos' => 2, 'cantidad' => 10, 'precio' => 310.00, 'desc' => 'Bungalow privado con terraza.'],
                    ['nombre' => 'Doble',    'cap_adultos' => 1, 'cap_ninos' => 1, 'cantidad' => 14, 'precio' => 199.00, 'desc' => 'Con vista a la piscina.'],
                    ['nombre' => 'Familiar', 'cap_adultos' => 2, 'cap_ninos' => 2, 'cantidad' => 8,  'precio' => 288.00, 'desc' => 'Espaciosa para familias.'],
                ],
            ],
            [
                'servicio' => [
                    'nombre'      => 'Wyndham Costa del Sol Piura',
                    'ciudad'      => 'Piura',
                    'pais'        => 'Perú',
                    'descripcion' => 'Hotel moderno con piscina y restaurante.',
                    'imagen_url'  => 'https://i.pinimg.com/1200x/77/85/65/77856594094fa7710bfbe92778ae4f63.jpg',
                    // 👇 Coordenadas de piura

                    'latitud'     => -5.193789,
                    'longitud'    => -80.629950,
                ],
                'hotel' => ['estrellas' => 2],
                'habitaciones' => [
                    ['nombre' => 'Bungalow', 'cap_adultos' => 4, 'cap_ninos' => 2, 'cantidad' => 10, 'precio' => 310.00, 'desc' => 'Bungalow privado con terraza.'],
                    ['nombre' => 'Doble',    'cap_adultos' => 1, 'cap_ninos' => 1, 'cantidad' => 14, 'precio' => 199.00, 'desc' => 'Con vista a la piscina.'],
                    ['nombre' => 'Familiar', 'cap_adultos' => 2, 'cap_ninos' => 2, 'cantidad' => 8,  'precio' => 288.00, 'desc' => 'Espaciosa para familias.'],
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
                'latitud'      => $cfg['servicio']['latitud'] ?? null,
                'longitud'     => $cfg['servicio']['longitud'] ?? null,
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
        $toursConfig = [
            [
            'servicio' => [
                'nombre'      => 'City Tour Arequipa',
                'ciudad'      => 'Arequipa',
                'pais'        => 'Perú',
                'descripcion' => 'Recorrido por el centro histórico y miradores.',
                'imagen_url'  => 'https://i.pinimg.com/736x/29/2c/c1/292cc19fa1f63a7842a6f3ac24d2ee1c.jpg',
                'latitud'     => -16.398803,
                'longitud'    => -71.536883,
                ],
            'tour' => [
                'categoria' => 'Cultura',
                'duracion'  => 180,
                'precio'    => 90.00,
                ],
            'items' => [
                ['nombre' => 'Plaza Mayor de Arequipa', 'icono' => '🏛️'],
                ['nombre' => 'Catedral de Arequipa', 'icono' => '⛪'],
                ['nombre' => 'Recorrido calles de Arequipa', 'icono' => '🚶'],
                ]
            ],
            [
            'servicio' => [
                'nombre'      => 'Tour Cañón del Colca',
                'ciudad'      => 'Arequipa',
                'pais'        => 'Perú',
                'descripcion' => 'Excursión completa al famoso Cañón del Colca.',
                'imagen_url'  => 'https://i.pinimg.com/1200x/58/d0/e0/58d0e0f326c5844361fd066329997105.jpg',
                'latitud'     => -15.622755,
                'longitud'    => -71.964438,
                ],
            'tour' => [
                'categoria' => 'Cultura',
                'duracion'  => 190,
                'precio'    => 100.00,
                ],
            'items' => [
                ['nombre' => 'Mirador Cruz del Cóndor', 'icono' => '🦅'],
                ['nombre' => 'Pueblo de Chivay', 'icono' => '🏘️'],
                ['nombre' => 'Aguas Termales La Calera', 'icono' => '♨️'],
                ],
            ],
            [
            'servicio' => [
                'nombre'      => 'City Tour Lima',
                'ciudad'      => 'Lima',
                'pais'        => 'Perú',
                'descripcion' => 'Recorrido por el centro histórico y calles.',
                'imagen_url'  => 'https://i.pinimg.com/1200x/53/89/01/538901e7c024dd107f28d687d89ff65b.jpg',
                'latitud'     => -12.046374,
                'longitud'    => -77.042793,
                ],
            'tour' => [
                'categoria' => 'Cultura',
                'duracion'  => 100,
                'precio'    => 70.00,
                ],
            'items' => [
                ['nombre' => 'Plaza Mayor de Lima', 'icono' => '🏛️'],
                ['nombre' => 'Catedral de Lima', 'icono' => '⛪'],
                ['nombre' => 'Parque Kennedy', 'icono' => '🌳'],
                ]
            ],
            [
            'servicio' => [
                'nombre'      => 'Tour Pachacamac',
                'ciudad'      => 'Lima',
                'pais'        => 'Perú',
                'descripcion' => 'Recorrido arqueológico cercano a la ciudad.',
                'imagen_url'  => 'https://i.pinimg.com/1200x/a8/80/bd/a880bdfdd87c639088b8cbbf7c283cce.jpg',
                'latitud'     => -12.275274,
                'longitud'    => -76.878006,
                ],
            'tour' => [
                'categoria' => 'Cultura',
                'duracion'  => 30,
                'precio'    => 60.00,
                ],
            'items' => [
                ['nombre' => 'Templo de Pachacamac', 'icono' => '🏛️'],
                ['nombre' => 'Museo de Sitio', 'icono' => '🏺'],
                ['nombre' => 'Mirador del Pacífico', 'icono' => '🌊']
                ]
            ],
            [
            'servicio' => [
                'nombre'      => 'City Tour Cusco',
                'ciudad'      => 'Cusco',
                'pais'        => 'Perú',
                'descripcion' => 'Visita guiada por templos y sitios históricos.',
                'imagen_url'  => 'https://i.pinimg.com/736x/ab/a0/86/aba086e2f4e0a770ae9d309b619951df.jpg',
                'latitud'     => -13.516667,
                'longitud'    => -71.978056,
                ],
            'tour' => [
                'categoria' => 'Cultura',
                'duracion'  => 190,
                'precio'    => 200.00,
                ],
            'items' => [
                ['nombre' => 'Koricancha', 'icono' => '🏛️'],
                ['nombre' => 'Sacsayhuamán', 'icono' => '🏰'],
                ['nombre' => 'Plaza de Armas', 'icono' => '🌳'],
            ]
            ], 
            [
            'servicio' => [
                'nombre'      => 'Tour Valle Sagrado',
                'ciudad'      => 'Cusco',
                'pais'        => 'Perú',
                'descripcion' => 'Excursión a Pisac, Ollantaytambo y más.',
                'imagen_url'  => 'https://i.pinimg.com/736x/99/8c/28/998c28fa6c61cf21bb87aab25f8e5373.jpg',
                'latitud'     => -13.310536,
                'longitud'    => -72.126278,
                ],
            'tour' => [
                'categoria' => 'Cultura',
                'duracion'  => 150,
                'precio'    => 210.00,
                ],
            'items' => [
                ['nombre' => 'Ruinas de Pisac', 'icono' => '🏛️'],
                ['nombre' => 'Ollantaytambo', 'icono' => '🏰'],
                ['nombre' => 'Mercado de Urubamba', 'icono' => '🛍️'],
            ],
            ],
            [
            'servicio' => [
                'nombre'      => 'Tour Lago Titicaca',
                'ciudad'      => 'Puno',
                'pais'        => 'Perú',
                'descripcion' => 'Visita a islas flotantes y Taquile.',
                'imagen_url'  => 'https://i.pinimg.com/1200x/89/66/85/89668566dba7204547db4d2be56ed9e8.jpg',
                'latitud'     => -15.842184,
                'longitud'    => -70.020071,
                ],
            'tour' => [
                'categoria' => 'Aventura',
                'duracion'  => 150,
                'precio'    => 210.00,
                ],
            'items' => [
                ['nombre' => 'Islas Flotantes de los Uros', 'icono' => '🏝️'],
                ['nombre' => 'Isla Taquile', 'icono' => '🏞️'],
                ['nombre' => 'Pueblo de Amantani', 'icono' => '🏘️'],
            ]
            ],
            [
            'servicio' => [
                'nombre'      => 'Tour Manglares de Vice',
                'ciudad'      => 'Piura',
                'pais'        => 'Perú',
                'descripcion' => 'Tour ecológico por los manglares.',
                'imagen_url'  => 'https://i.pinimg.com/1200x/6a/ad/aa/6aadaa940e8eca986c561c7815636f96.jpg',
                'latitud'     => -4.954970,
                'longitud'    => -81.043330,
                ],
            'tour' => [
                'categoria' => 'Aventura',
                'duracion'  => 100,
                'precio'    => 85.60,
                ],
            'items' => [
                ['nombre' => 'Paseo en bote por manglares', 'icono' => '🚤'],
                ['nombre' => 'Avistamiento de aves', 'icono' => '🦜'],
                ['nombre' => 'Visita a la comunidad local', 'icono' => '🏘️'],
            ]
            ]

        ];

        $tours = [];
        foreach ($toursConfig as $cfg) {

            // 1) Crear el Servicio
            $servicio = Servicio::create([
                'proveedor_id' => $proveedor->id,
                'nombre'       => $cfg['servicio']['nombre'],
                'tipo'         => 'tour',
                'ciudad'       => $cfg['servicio']['ciudad'],
                'pais'         => $cfg['servicio']['pais'],
                'descripcion'  => $cfg['servicio']['descripcion'],
                'imagen_url'   => $cfg['servicio']['imagen_url'],
                'latitud'      => $cfg['servicio']['latitud'],
                'longitud'     => $cfg['servicio']['longitud'],
                'activo'       => true
            ]);

            // 2) Crear el Tour
            $tour = Tour::create([
                'servicio_id' => $servicio->id,
                'categoria'   => $cfg['tour']['categoria'],
                'duracion'    => $cfg['tour']['duracion'],
                'precio'      => $cfg['tour']['precio']
            ]);

            $tours[] = $tour;

            // 3) Crear los items del tour (según tu migración REAL)
            foreach ($cfg['items'] as $item) {
                TourItem::create([
                    'servicio_id' => $servicio->id,
                    'nombre'      => $item['nombre'],  // campo correcto
                    'icono'       => $item['icono'] ?? null,
                ]);
            }
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

        // ------- Crear reservas pasadas -------------
        // Reservas pasadas para habitaciones
        $habitaciones = Habitacion::all();
        if ($habitaciones->isNotEmpty()) {
            foreach ($habitaciones->take(5) as $habitacion) { // Crear 5 reservas pasadas
                ReservaHabitacion::factory()->pasado()->create([
                    'habitacion_id' => $habitacion->id,
                ]);
            }
        }

        // Reservas pasadas para tours
        $tours = Tour::all();
        if ($tours->isNotEmpty()) {
            foreach ($tours->take(3) as $tour) { // Crear salidas pasadas para 3 tours
                $salidaPasada = TourSalida::factory()->pasado()->create([
                    'servicio_id' => $tour->servicio_id,
                ]);

                // Crear algunas reservas para esta salida pasada
                ReservaTour::factory()->count(rand(1,3))->create([
                    'salida_id' => $salidaPasada->id,
                ]);
            }
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

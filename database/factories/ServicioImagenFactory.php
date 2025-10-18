<?php

namespace Database\Factories;

use App\Models\ServicioImagen;
use App\Models\Servicio;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServicioImagenFactory extends Factory
{
    protected $model = ServicioImagen::class;

    public function definition(): array
    {
        $hotelImgs = [
            'https://hotevia.info/wp-content/uploads/2021/08/acolpacha-tambo-boutique-hotel-arequipa.jpeg',
            'https://www.fiesta-tours-peru.com/img/hotels/arequipa/city/big/qp-hotels-arequipa.jpg',
            'https://www.es.kayak.com/rimg/himg/46/62/64/expedia_group-677469-f1935a-282581.jpg?width=836&height=607&crop=true',
            'https://content.r9cdn.net/rimg/dimg/97/d2/53102ef1-city-10174-17279266c9c.jpg?width=1200&height=630&crop=true',
            'https://www.lostambos.com.pe/colonial/wp-content/uploads/2022/12/Fachada-2.jpeg'
        ];

        $tourImgs = [
            'https://www.tours-machupicchu-peru.com/wp-content/uploads/2021/10/City-Tour-Cusco-Peru.jpg',
            'https://www.amazingperu.com/es/imagenes/tours/tour_de_aventura_de_lujo_peru_tour.jpg',
            'https://rycexcursionesrd.com/wp-content/uploads/2024/05/tour-oasis-huacachina-x-travel-peru.jpg',
            'https://www.qeswachakaperutours.com/wp-content/uploads/galeria-machupicchu-full-day-4.jpg',
            'https://www.limatourperu.com/wp-content/uploads/city-tour-centro-historico-lima-tour-peru.jpg'
        ];

        $tipo = $this->faker->randomElement(['hotel', 'tour']);
        $imagenes = $tipo === 'hotel' ? $hotelImgs : $tourImgs;

        return [
            'servicio_id' => Servicio::factory()->state(['tipo' => $tipo]),
            'url' => $this->faker->randomElement($imagenes),
            'alt' => $this->faker->sentence(2),
        ];
    }

}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HotelResource extends JsonResource
{
    protected $checkIn;
    protected $checkOut;
    protected $filtros;
    protected $habitacionesDisponibles;

    /**
     * Create a new resource instance with optional availability data.
     *
     * @param  mixed  $resource
     * @param  string|null  $checkIn
     * @param  string|null  $checkOut
     * @param  array  $filtros
     * @param  array  $habitacionesDisponibles
     * @return void
     */
    public function __construct($resource, $checkIn = null, $checkOut = null, $filtros = [], $habitacionesDisponibles = [])
    {
        parent::__construct($resource);
        $this->checkIn = $checkIn;
        $this->checkOut = $checkOut;
        $this->filtros = $filtros;
        $this->habitacionesDisponibles = $habitacionesDisponibles;
    }

    /**
     * Transforma el recurso en una matriz.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Obtener el precio mínimo de las habitaciones del hotel
        $precioMinimo = $this->habitaciones->min('precio_por_noche');
        
        $data = [
            'id' => $this->servicio_id,
            'tipo' => $this->servicio->tipo,
            'nombre' => $this->nombre,
            'direccion' => $this->direccion,
            'estrellas' => $this->estrellas,
            'precio_por_noche' => $precioMinimo ? (float) $precioMinimo : null,
            'imagenUrl' => $this->servicio->imagen_url,
            'descripcion' => $this->servicio->descripcion,
        ];

        // Si tenemos información de disponibilidad, la agregamos
        if ($this->checkIn && $this->checkOut) {
            $data['check_in'] = $this->checkIn;
            $data['check_out'] = $this->checkOut;
            $data['filtros'] = $this->filtros;
            $data['habitaciones'] = $this->habitacionesDisponibles;
        }

        return $data;
    }
}
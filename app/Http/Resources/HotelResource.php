<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HotelResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->servicio_id,
            'servicio' => $this->servicio->only('id', 'nombre', 'descripcion', 'ciudad', 'precio', 'imagen_url'),
            'proveedor' => $this->servicio->proveedor->only('id', 'nombre', 'apellido'),
            'hotel' => $this->only('direccion', 'estrellas', 'precio_por_noche'),
        ];
    }
}
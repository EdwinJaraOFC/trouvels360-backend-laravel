<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreHotelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Datos del servicio
            'proveedor_id' => ['required', 'integer', 'exists:usuarios,id'],
            'nombre' => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string'],
            'ciudad' => ['required', 'string', 'max:100'],
            'precio' => ['required', 'numeric', 'min:0'],
            'horario_inicio' => ['nullable', 'date_format:H:i'],
            'horario_fin' => ['nullable', 'date_format:H:i', 'after:horario_inicio'],
            'imagen_url' => ['nullable', 'url', 'max:500'],
            
            // Datos específicos del hotel
            'direccion' => ['required', 'string', 'max:255'],
            'estrellas' => ['nullable', 'integer', 'min:1', 'max:5'],
            'precio_por_noche' => ['required', 'numeric', 'min:0'],
        ];
    }
}
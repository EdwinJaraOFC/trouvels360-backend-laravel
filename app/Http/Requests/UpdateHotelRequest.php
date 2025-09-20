<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHotelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Datos del servicio (opcionales en actualización)
            'nombre' => ['sometimes', 'required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string'],
            'ciudad' => ['sometimes', 'required', 'string', 'max:100'],
            'precio' => ['sometimes', 'required', 'numeric', 'min:0'],
            'horario_inicio' => ['nullable', 'date_format:H:i'],
            'horario_fin' => ['nullable', 'date_format:H:i', 'after:horario_inicio'],
            'imagen_url' => ['nullable', 'url', 'max:500'],
            
            // Datos específicos del hotel
            'direccion' => ['sometimes', 'required', 'string', 'max:255'],
            'estrellas' => ['nullable', 'integer', 'min:1', 'max:5'],
            'precio_por_noche' => ['sometimes', 'required', 'numeric', 'min:0'],
        ];
    }
}
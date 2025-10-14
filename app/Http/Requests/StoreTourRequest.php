<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTourRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Solo proveedores pueden crear tours
        return auth()->check() && auth()->user()->rol === 'proveedor';
    }

    public function rules(): array
    {
        return [
            // Campos del Servicio
            'nombre'       => ['required', 'string', 'max:150'],
            'descripcion'  => ['nullable', 'string'],
            'ciudad'       => ['required', 'string', 'max:100'],
            'pais'         => ['required', 'string', 'max:100'],
            'imagen_url'   => ['nullable', 'url'],
            'activo'       => ['boolean'],

            // Campos del TOUR (detalle)
            'categoria'            => ['nullable','in:Gastronomía,Aventura,Cultura,Relajación'],
            'duracion'         => ['nullable','integer','min:0'],       // 0..1440 si quieres acotar
            'precio'       => ['required','numeric','min:0'],
            'cupos' => ['nullable','integer','min:1'],

            // Campos adicionales
            'cosas_que_llevar'   => ['nullable', 'array'],
            'cosas_que_llevar.*' => ['string'],
            'galeria_imagenes'   => ['nullable', 'array'],
            'galeria_imagenes.*' => ['url'],
        ];
    }
}

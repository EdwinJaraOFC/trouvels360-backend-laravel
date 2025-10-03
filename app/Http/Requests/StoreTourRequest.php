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
            // Campos del SERVICIO
            'nombre'      => ['required','string','max:150'],
            'descripcion' => ['nullable','string'],
            'ciudad'      => ['required','string','max:100'],
            'pais'        => ['required','string','max:100'], // <- nuevo campo requerido
            'imagen_url'  => ['nullable','url'],
            'activo'      => ['boolean'],

            // Campos del TOUR (detalle)
            'categoria'            => ['nullable','in:Gastronomía,Aventura,Cultura,Relajación'],
            'duracion_min'         => ['nullable','integer','min:0'],       // 0..1440 si quieres acotar
            'precio_persona'       => ['required','numeric','min:0'],
            'capacidad_por_salida' => ['nullable','integer','min:1'],
        ];
    }
}

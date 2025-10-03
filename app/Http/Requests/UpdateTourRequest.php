<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTourRequest extends FormRequest
{
    public function authorize(): bool
    {
        // La verificación de dueño se hace en el controller; aquí validamos rol mínimo
        return auth()->check() && auth()->user()->rol === 'proveedor';
    }

    public function rules(): array
    {
        return [
            // Campos del SERVICIO (opcionales)
            'nombre'      => ['sometimes','string','max:150'],
            'descripcion' => ['sometimes','nullable','string'],
            'ciudad'      => ['sometimes','string','max:100'],
            'pais'        => ['sometimes','string','max:100'], // <- permitir actualizar país
            'imagen_url'  => ['sometimes','nullable','url'],
            'activo'      => ['sometimes','boolean'],

            // Campos del TOUR (opcionales)
            'categoria'            => ['sometimes','nullable','in:Gastronomía,Aventura,Cultura,Relajación'],
            'duracion_min'         => ['sometimes','nullable','integer','min:0'],
            'precio_persona'       => ['sometimes','numeric','min:0'],
            'capacidad_por_salida' => ['sometimes','nullable','integer','min:1'],
        ];
    }
}

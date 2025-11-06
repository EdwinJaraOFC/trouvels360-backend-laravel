<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTourRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->rol === 'proveedor';
    }

    public function rules(): array
    {
        return [
            // Servicio (opcionales)
            'nombre'      => ['sometimes','string','max:150'],
            'descripcion' => ['sometimes','nullable','string'],
            'ciudad'      => ['sometimes','string','max:100'],
            'pais'        => ['sometimes','string','max:100'],
            'imagen_url'  => ['sometimes','nullable','url','max:500'],
            'activo'      => ['sometimes','boolean'],

            // Tour (opcionales)
            'categoria'           => ['sometimes','nullable','in:Gastronomía,Aventura,Cultura,Relajación'],
            'duracion'            => ['sometimes','nullable','integer','min:0'],
            'precio'              => ['sometimes','numeric','min:0'],
            'cupos'               => ['sometimes','nullable','integer','min:1'],
            'cosas_para_llevar'   => ['sometimes', 'array'],
            'cosas_para_llevar.*' => ['string'],

            // Galería (reemplazo total si viene)
            'imagenes'       => ['sometimes','array','max:5'],
            'imagenes.*'     => ['nullable'],
            'imagenes.*.url' => ['sometimes','required','url','max:500'],
            'imagenes.*.alt' => ['sometimes','nullable','string','max:150'],
        ];
    }
}

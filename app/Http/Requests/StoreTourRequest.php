<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTourRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->rol === 'proveedor';
    }

    public function rules(): array
    {
        return [
            // Servicio
            'nombre'      => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string'],
            'ciudad'      => ['required', 'string', 'max:100'],
            'pais'        => ['required', 'string', 'max:100'],
            'imagen_url'  => ['nullable', 'url', 'max:500'],
            'activo'      => ['boolean'],

            // Tour
            'categoria'           => ['nullable','in:Gastronomía,Aventura,Cultura,Relajación'],
            'duracion'            => ['nullable','integer','min:0'],
            'precio'              => ['required','numeric','min:0'],
            'cosas_para_llevar'   => ['nullable', 'array'],
            'cosas_para_llevar.*' => ['string'],

            // Galería (servicio_imagenes)
            'imagenes'       => ['sometimes','array','max:5'],
            'imagenes.*'     => ['nullable'],
            'imagenes.*.url' => ['sometimes','required','url','max:500'],
            'imagenes.*.alt' => ['sometimes','nullable','string','max:150'],

            // Salidas (array de salidas)
            'salidas' => ['required', 'array', 'min:1'],
            'salidas.*.fecha' => ['required', 'date', 'after_or_equal:today'],
            'salidas.*.hora' => ['required', 'date_format:H:i'],
            'salidas.*.cupo_total' => ['required', 'integer', 'min:1'],
            'salidas.*.cupo_reservado' => ['nullable', 'integer', 'min:0'],
            'salidas.*.estado' => ['required', 'in:programada,cerrada,cancelada'],

        ];
    }
}

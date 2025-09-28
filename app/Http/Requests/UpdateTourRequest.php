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
            'categoria'            => ['sometimes','in:Gastronomía,Aventura,Cultura,Relajación'],
            'duracion_min'         => ['sometimes','nullable','integer','min:1','max:1440'],
            'precio_persona'       => ['sometimes','numeric','min:0'],
            'capacidad_por_salida' => ['sometimes','integer','min:1'],
        ];
    }
}

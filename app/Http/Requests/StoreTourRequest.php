<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTourRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->rol === 'proveedor';
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'categoria'            => $this->categoria ?: null,
            'duracion_min'         => $this->duracion_min ?: null,
        ]);
    }

    public function rules(): array
    {
        return [
            'servicio_id'          => [
                'required',
                'integer',
                // existe en servicios y es de tipo 'tour'
                Rule::exists('servicios','id')->where(fn($q) => $q->where('tipo','tour')),
            ],
            'categoria'            => ['nullable','string','max:100'],
            'duracion_min'         => ['nullable','integer','min:1','max:1440'],
            'precio_persona'       => ['required','numeric','min:0'],
            'capacidad_por_salida' => ['required','integer','min:1'],
        ];
    }
}

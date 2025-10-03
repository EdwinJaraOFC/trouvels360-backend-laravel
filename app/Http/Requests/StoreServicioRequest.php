<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServicioRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        return $user && $user->rol === 'proveedor';
    }

    protected function prepareForValidation(): void
    {
        if ($this->user()) {
            // Fuerza el dueño al proveedor autenticado
            $this->merge(['proveedor_id' => $this->user()->id]);
        }

        if ($this->has('nombre') && is_string($this->nombre)) {
            $this->merge(['nombre' => trim($this->nombre)]);
        }
        if ($this->has('ciudad') && is_string($this->ciudad)) {
            $this->merge(['ciudad' => trim($this->ciudad)]);
        }
    }

    public function rules(): array
    {
        return [
            // Ya no lo pides al cliente: lo pones tú en prepareForValidation
            'proveedor_id' => [
                'required',
                Rule::exists('usuarios','id')->where('rol','proveedor'),
            ],
            'nombre'       => ['required','string','max:150'],
            'tipo'         => ['required', Rule::in(['hotel','tour'])],
            'descripcion'  => ['sometimes','nullable','string'],
            'ciudad'       => ['required','string','max:100'],
            'pais'         => ['required','string','max:100'], // <-- nuevo
            'imagen_url'   => ['sometimes','nullable','url','max:500'],
            'activo'       => ['sometimes','boolean'],
        ];
    }
}

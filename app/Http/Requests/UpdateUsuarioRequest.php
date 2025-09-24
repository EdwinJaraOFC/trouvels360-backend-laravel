<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $data = $this->all();

        // Limpia strings vacíos para que 'sometimes' no dispare validación
        foreach (['nombre','apellido','email','password','rol'] as $k) {
            if ($this->has($k)) {
                $val = is_string($this->input($k)) ? trim((string) $this->input($k)) : $this->input($k);
                if ($val === '' || $val === null) {
                    unset($data[$k]); // elimina la clave: 'sometimes' ya no aplica
                } else {
                    // normaliza email si viene con valor
                    if ($k === 'email') {
                        $data[$k] = mb_strtolower($val);
                    } else {
                        $data[$k] = $val;
                    }
                }
            }
        }

        $this->replace($data);
    }

    public function rules(): array
    {
        $id = $this->route('usuario')?->id;

        return [
            'nombre'   => ['sometimes','string','max:100'],
            'apellido' => ['sometimes','string','max:100'],
            'email'    => ['sometimes','email','max:150', Rule::unique('usuarios','email')->ignore($id)],
            'password' => ['sometimes','string','min:6'],
            'rol'      => ['sometimes','in:viajero,proveedor'],
        ];
    }
}

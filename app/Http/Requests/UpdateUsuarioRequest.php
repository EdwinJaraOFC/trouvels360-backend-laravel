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

    public function rules(): array
    {
        // El parámetro de ruta es {usuario}, Laravel lo resuelve a la instancia y podemos obtener su id
        $id = $this->route('usuario')?->id;

        return [
            'nombre'   => ['sometimes','string','max:100'],
            'apellido' => ['sometimes','string','max:100'],
            'email'    => [
                'sometimes','email','max:150',
                Rule::unique('usuarios','email')->ignore($id),
            ],
            'password' => ['sometimes','string','min:6'],
            'rol'      => ['sometimes','in:viajero,proveedor'],
        ];
    }
}

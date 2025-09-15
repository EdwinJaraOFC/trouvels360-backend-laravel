<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Ajusta a false si luego usas auth y permisos
    }

    public function rules(): array
    {
        return [
            'nombre'   => ['required','string','max:100'],
            'apellido' => ['required','string','max:100'],
            'email'    => ['required','email','max:150','unique:usuarios,email'],
            'password' => ['required','string','min:6'],
            'rol'      => ['required','in:viajero,proveedor'],
        ];
    }
}

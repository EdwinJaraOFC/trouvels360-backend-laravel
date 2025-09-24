<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Normaliza email solo si viene y no está vacío
        if ($this->has('email')) {
            $email = trim((string) $this->input('email'));
            $this->merge(['email' => $email === '' ? null : mb_strtolower($email)]);
        }
    }

    public function rules(): array
    {
        return [
            'nombre'   => ['required','string','max:100'],
            'apellido' => ['required','string','max:100'],
            'email'    => ['required','email','max:150', Rule::unique('usuarios','email')],
            'password' => ['required','string','min:6'],
            'rol'      => ['required','in:viajero,proveedor'],
        ];
    }
}

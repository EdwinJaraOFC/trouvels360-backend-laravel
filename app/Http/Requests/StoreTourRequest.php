<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTourRequest extends FormRequest
{
    // Determine if the user is authorized to make this request.
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Campos solo de Tour
            'categoria'=> ['nullable','string','max:100'],
            'duracion'=> ['nullable','string','max:50'],
            'precio_adulto'=> ['required','numeric'],
            'precio_child'=> ['required','numeric'],

            // Campos de Servicio
            'proveedor_id'   => ['required','integer','exists:usuarios,id'],
            'nombre' => ['required','string','max:150'],
            'descripcion' => ['nullable','string'],
            'ciudad' => ['required','string','max:100'],
            'horario_inicio' => ['nullable','date_format:H:i:s'],
            'horario_fin' =>['nullable','date_format:H:i:s'],
            'imagen_url'=>['nullable','url','max:500'],
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTourRequest extends FormRequest
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
            'precio_adulto'=> ['sometimes','numeric'],
            'precio_child'=> ['sometimes','numeric'],

            // Campos de Servicio
            'proveedor_id'   => ['sometimes','integer','exists:usuarios,id'],
            'nombre' => ['sometimes','string','max:150'],
            'descripcion' => ['nullable','string'],
            'ciudad' => ['sometimes','string','max:100'],
            'horario_inicio' => ['nullable','date_format:H:i:s'],
            'horario_fin' =>['nullable','date_format:H:i:s'],
            'imagen_url'=>['nullable','url','max:500'],
        ];
    }
}

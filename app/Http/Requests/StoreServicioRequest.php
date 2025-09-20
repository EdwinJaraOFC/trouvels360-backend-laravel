<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreServicioRequest extends FormRequest
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
            'proveedor_id'   => ['required','integer'],
            'nombre' => ['required','string','max:150'],
            'tipo' => ['required','in:hotel,tour'],
            'descripcion' => ['nullable','string'],
            'ciudad' => ['required','string','max:100'],
            'horario_inicio' => ['nullable','date_format:H:i:s'],
            'horario_fin' =>['nullable','date_format:H:i:s'],
            'imagen_url'=>['nullable','url','max:500'],
        ];
    }
}
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
{
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
            'servicio_id' => 'required|exists:servicios,id',
            'usuario_id'  => 'required|exists:usuarios,id',
            'comentario'  => 'required|string|max:300',
            'calificacion'=> 'required|integer|min:1|max:5',
        ];
    }
}

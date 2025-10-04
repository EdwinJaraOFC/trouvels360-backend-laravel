<?php

namespace App\Http\Requests;

use App\Models\Servicio;
use Illuminate\Foundation\Http\FormRequest;

class UpdateHotelRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (!$user || $user->rol !== 'proveedor') {
            return false;
        }

        $servicioId = (int) $this->route('servicio_id');
        $servicio = Servicio::find($servicioId);

        return $servicio
            && $servicio->tipo === 'hotel'
            && (int) $servicio->proveedor_id === (int) $user->id;
    }

    public function rules(): array
    {
        return [
            // Campos del SERVICIO
            'nombre'      => ['sometimes', 'string', 'max:150'],
            'descripcion' => ['sometimes', 'nullable', 'string'],
            'ciudad'      => ['sometimes', 'string', 'max:100'],
            'pais'        => ['sometimes', 'string', 'max:100'],
            'imagen_url'  => ['sometimes', 'nullable', 'string', 'max:500'],
            'activo'      => ['sometimes', 'boolean'],

            // Campos del HOTEL
            'direccion'   => ['sometimes', 'string', 'max:255'],
            'estrellas'   => ['sometimes', 'nullable', 'integer', 'between:1,5'],
        ];
    }
}
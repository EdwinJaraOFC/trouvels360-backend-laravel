<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreHotelRequest extends FormRequest
{
    /**
     * Solo proveedores autenticados pueden crear hoteles.
     * El proveedor dueño se tomará del usuario autenticado (no del body).
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->rol === 'proveedor';
    }

    /**
     * Ahora recibimos JSON plano (sin objeto "servicio").
     * Validamos campos del Servicio + Hotel directamente.
     */
    public function rules(): array
    {
        return [
            // Campos del SERVICIO (tipo se fuerza a 'hotel' en el controller)
            'nombre'      => ['required','string','max:150'],
            'descripcion' => ['sometimes','nullable','string'],
            'ciudad'      => ['required','string','max:100'],
            'pais'        => ['required','string','max:100'],
            'imagen_url'  => ['sometimes','nullable','string','max:500'],
            'activo'      => ['sometimes','boolean'],

            // Campos del HOTEL (detalle)
            'direccion'   => ['required','string','max:255'],
            'estrellas'   => ['sometimes','nullable','integer','between:1,5'],
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreHotelRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (!$user || $user->rol !== 'proveedor') {
            return false;
        }

        // Crear hotel ahora SIEMPRE crea también el servicio (tipo=hotel).
        // Exigimos que el proveedor_id enviado sea el del usuario autenticado.
        $serv = $this->input('servicio', []);
        return isset($serv['proveedor_id']) && (int)$serv['proveedor_id'] === (int)$user->id;
    }

    public function rules(): array
    {
        return [
            // Datos del Servicio (tipo se fuerza a 'hotel' en el controller)
            'servicio.proveedor_id' => ['required','integer','exists:usuarios,id'],
            'servicio.nombre'       => ['required','string','max:150'],
            'servicio.descripcion'  => ['sometimes','nullable','string'],
            'servicio.ciudad'       => ['required','string','max:100'],
            'servicio.pais'         => ['required','string','max:100'],
            'servicio.imagen_url'   => ['sometimes','nullable','string','max:500'],
            'servicio.activo'       => ['sometimes','boolean'],

            // Datos del Hotel
            'direccion'             => ['required','string','max:255'],
            'estrellas'             => ['nullable','integer','between:1,5'],
        ];
    }
}

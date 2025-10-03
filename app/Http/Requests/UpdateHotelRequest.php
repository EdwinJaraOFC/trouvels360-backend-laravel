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

        // servicio_id viene en la ruta como {servicio_id}
        $servicioId = (int) $this->route('servicio_id');
        $servicio = Servicio::find($servicioId);

        return $servicio
            && $servicio->tipo === 'hotel'
            && (int) $servicio->proveedor_id === (int) $user->id;
    }

    public function rules(): array
    {
        return [
            // 'nombre' ya no existe en hoteles
            'direccion' => ['sometimes','string','max:255'],
            'estrellas' => ['sometimes','nullable','integer','between:1,5'],
        ];
    }
}

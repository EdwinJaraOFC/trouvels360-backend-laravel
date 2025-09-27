<?php

namespace App\Http\Requests;

use App\Models\Servicio;
use App\Models\Hotel;
use Illuminate\Foundation\Http\FormRequest;

class UpdateHotelRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (!$user || $user->rol !== 'proveedor') return false;

        // servicio_id viene en la ruta como {servicio_id}
        $servicioId = (int) $this->route('servicio_id');
        $servicio = Servicio::find($servicioId);
        return $servicio
            && $servicio->tipo === 'hotel'
            && $servicio->proveedor_id === $user->id;
    }

    public function rules(): array
    {
        return [
            'nombre'    => ['sometimes','string','max:150'],
            'direccion' => ['sometimes','string','max:255'],
            'estrellas' => ['sometimes','nullable','integer','min:1','max:5'],
        ];
    }
}

<?php

namespace App\Http\Requests;

use App\Models\Servicio;
use Illuminate\Foundation\Http\FormRequest;

class StoreHotelRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (!$user || $user->rol !== 'proveedor') return false;

        // Debe ser dueño del servicio y el servicio debe ser tipo hotel
        $servicioId = (int) $this->input('servicio_id');
        $servicio = Servicio::find($servicioId);
        return $servicio
            && $servicio->tipo === 'hotel'
            && $servicio->proveedor_id === $user->id;
    }

    public function rules(): array
    {
        return [
            'servicio_id' => ['required','integer','exists:servicios,id'],
            'nombre'      => ['required','string','max:150'],
            'direccion'   => ['required','string','max:255'],
            'estrellas'   => ['nullable','integer','min:1','max:5'],
        ];
    }
}

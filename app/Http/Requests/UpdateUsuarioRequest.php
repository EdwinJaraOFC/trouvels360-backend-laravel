<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $data = $this->all();

        // Modelo enlazado por la ruta (Usuario $usuario)
        $usuario = $this->route('usuario');
        $currentRole = $usuario?->rol;

        // Normaliza y elimina strings vacíos para que 'sometimes' funcione bien
        $normalize = function($key, $val) {
            if (is_string($val)) $val = trim($val);
            if ($val === '' || $val === null) return [false, null];

            if ($key === 'email')      return [true, mb_strtolower($val)];
            if ($key === 'ruc')        return [true, preg_replace('/\D/', '', (string)$val)]; // solo dígitos
            if ($key === 'telefono')   return [true, $val]; // formato se valida por regex
            if (in_array($key, ['empresa_nombre','nombre','apellido'], true)) return [true, $val];

            return [true, $val];
        };

        foreach (array_keys($data) as $k) {
            // Nunca permitimos cambiar 'rol' via update()
            if ($k === 'rol') {
                unset($data[$k]);
                continue;
            }

            [$keep, $v] = $normalize($k, $data[$k]);
            if (!$keep) {
                unset($data[$k]);
            } else {
                $data[$k] = $v;
            }
        }

        // Si el usuario es 'viajero', ignoramos silenciosamente campos exclusivos de proveedor
        if ($currentRole === 'viajero') {
            unset($data['empresa_nombre'], $data['telefono'], $data['ruc']);
        }

        $this->replace($data);
    }

    public function rules(): array
    {
        $usuario = $this->route('usuario');   // Usuario actual (model binding)
        $id      = $usuario?->id;
        $role    = $usuario?->rol;            // rol actual en BD

        // Regla explícita para bloquear 'rol' si llega por algún motivo
        $rules = [
            'rol'       => ['prohibited'], // <- NO se permite cambiar rol

            'nombre'    => ['sometimes','string','max:100'],
            'apellido'  => ['sometimes','string','max:100'],
            'email'     => ['sometimes','email','max:150', Rule::unique('usuarios','email')->ignore($id)],
            'password'  => ['sometimes','string','min:6'],
        ];

        // Si es proveedor, puede actualizar sus campos con validaciones específicas
        if ($role === 'proveedor') {
            $rules = array_merge($rules, [
                'empresa_nombre' => ['sometimes','string','max:150'],
                // +51, espacio opcional, luego 9 y 8 dígitos
                'telefono'       => ['sometimes','string','regex:/^\+51\s?9\d{8}$/','max:15'],
                'ruc'            => ['sometimes','string','size:11','regex:/^\d{11}$/', Rule::unique('usuarios','ruc')->ignore($id)],
            ]);
        }

        // Si es viajero, esos campos ya se eliminan en prepareForValidation(), así que no hace falta regla

        return $rules;
    }

    public function messages(): array
    {
        return [
            'rol.prohibited'  => 'No está permitido cambiar el rol del usuario.',
            'telefono.regex'  => 'El teléfono debe tener el formato +51 9########.',
            'ruc.size'        => 'El RUC debe tener exactamente 11 dígitos.',
            'ruc.regex'       => 'El RUC solo debe contener dígitos.',
            'ruc.unique'      => 'El RUC ya está registrado.',
        ];
    }
}

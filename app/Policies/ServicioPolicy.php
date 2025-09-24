<?php

namespace App\Policies;

use App\Models\Servicio;
use App\Models\Usuario;
use Illuminate\Auth\Access\Response;

class ServicioPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Usuario $usuario): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Usuario $usuario, Servicio $servicio): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Usuario $user): bool {
        return $user->rol === 'proveedor';
    }

    public function update(Usuario $user, Servicio $servicio): bool {
        return $user->rol === 'proveedor' && $servicio->proveedor_id === $user->id;
    }

    public function delete(Usuario $user, Servicio $servicio): bool {
        return $user->rol === 'proveedor' && $servicio->proveedor_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Usuario $usuario, Servicio $servicio): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Usuario $usuario, Servicio $servicio): bool
    {
        return false;
    }
}

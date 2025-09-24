<?php

namespace Database\Seeders;

use App\Models\Usuario;
use App\Models\Servicio;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /** Pobla la base con datos de ejemplo. */
    public function run(): void
    {
        // --- Usuarios fijos ---
        $proveedor = Usuario::factory()->proveedor()->create([
            'nombre'   => 'Edwin',
            'apellido' => 'Proveedor',
            'email'    => 'edwinproveedor@gmail.com',
            'password' => 'proveedor', // se hashea por el cast
        ]);

        Usuario::factory()->viajero()->create([
            'nombre'   => 'Edwin',
            'apellido' => 'Viajero',
            'email'    => 'edwinviajero@gmail.com',
            'password' => 'viajero',
        ]);

        // Lotes adicionales de usuarios
        Usuario::factory()->viajero()->count(5)->create();
        Usuario::factory()->proveedor()->count(5)->create();

        // --- Servicios ---
        // 1) Crear un servicio fijo asociado al proveedor anterior
        Servicio::factory()->create([
            'proveedor_id' => $proveedor->id,
            'nombre'       => 'Hotel Cayetano',
            'tipo'         => 'hotel',
            'ciudad'       => 'Lima',
            'descripcion'  => 'Un hotel de prueba para el seeder.',
        ]);

        // 2) Crear lote aleatorio de servicios (hoteles/tours)
        Servicio::factory()->count(10)->create();
    }
}

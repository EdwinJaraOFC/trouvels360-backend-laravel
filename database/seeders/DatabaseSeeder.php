<?php

namespace Database\Seeders;

use App\Models\Usuario;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Poblar la base de datos con datos de ejemplo.
     */
    public function run(): void
    {
        // Crear un usuario fijo con rol proveedor
        Usuario::factory()->create([
            'nombre'   => 'Edwin',
            'apellido' => 'Proveedor',
            'email'    => 'edwinproveedor@gmail.com',
            'password' => 'proveedor', // se encripta automáticamente por el modelo
            'rol'      => 'proveedor',
        ]);

        // Crear un usuario fijo con rol viajero
        Usuario::factory()->create([
            'nombre'   => 'Edwin',
            'apellido' => 'Viajero',
            'email'    => 'edwinviajero@gmail.com',
            'password' => 'viajero', // se encripta automáticamente por el modelo
            'rol'      => 'viajero',
        ]);

        // Crear 5 usuarios con rol viajero
        Usuario::factory()->count(5)->state(['rol' => 'viajero'])->create();

        // Crear 5 usuarios con rol proveedor
        Usuario::factory()->count(5)->state(['rol' => 'proveedor'])->create();
    }
}

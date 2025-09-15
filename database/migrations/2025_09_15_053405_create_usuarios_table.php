<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecutar las migraciones.
     * 
     * Crea la tabla 'usuarios' con sus columnas principales.
     */
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id(); // Clave primaria autoincremental
            $table->string('nombre', 100); // Nombre del usuario
            $table->string('apellido', 100); // Apellido del usuario
            $table->string('email', 150)->unique(); // Email único
            $table->string('password'); // Contraseña encriptada
            
            // Rol del usuario: viajero o proveedor
            $table->enum('rol', ['viajero', 'proveedor'])->default('viajero');

            // Fechas de creación y última actualización (created_at, updated_at)
            $table->timestamps();
        });
    }

    /**
     * Revertir las migraciones.
     * 
     * Elimina la tabla 'usuarios' si existe.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};

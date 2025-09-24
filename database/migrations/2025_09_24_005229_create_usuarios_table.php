<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Crea la tabla 'usuarios' lista para autenticación futura (sin soft delete). */
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('apellido', 100);
            $table->string('email', 150)->unique();
            $table->timestamp('email_verified_at')->nullable(); // verificación de email
            $table->string('password');                          // hash
            $table->enum('rol', ['viajero', 'proveedor'])->default('viajero'); // tipo de usuario
            $table->rememberToken(); // "recuérdame"
            $table->timestamps();    // created_at / updated_at
        });
    }

    /** Elimina la tabla 'usuarios' si existe. */
    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('servicios', function (Blueprint $table) {
            $table->id();

            // Relación con el proveedor (usuarios.id)
            $table->foreignId('proveedor_id')
                ->constrained('usuarios')
                ->cascadeOnDelete();

            $table->string('nombre', 150);
            $table->enum('tipo', ['hotel', 'tour']);     // especialización
            $table->text('descripcion')->nullable();
            $table->string('ciudad', 100);
            $table->string('imagen_url', 500)->nullable();

            // Permite ocultar/pausar un servicio sin borrarlo
            $table->boolean('activo')->default(true);

            $table->timestamps();

            // Índices para búsqueda
            $table->index(['ciudad', 'tipo']);
            $table->index(['activo', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servicios');
    }
};

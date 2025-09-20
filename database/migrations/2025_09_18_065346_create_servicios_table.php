<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('servicios', function (Blueprint $table) {
            $table->id();

            // FK al proveedor (usuarios.id)
            $table->foreignId('proveedor_id')
                ->constrained('usuarios')
                ->cascadeOnDelete();

            $table->string('nombre', 150);
            $table->enum('tipo', ['hotel', 'tour']); // especialización
            $table->text('descripcion')->nullable();
            $table->string('ciudad', 100);
            $table->time('horario_inicio')->nullable();
            $table->time('horario_fin')->nullable();
            $table->string('imagen_url', 500)->nullable();

            $table->timestamps();

            // Índices útiles para búsqueda
            $table->index(['ciudad', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servicios');
    }
};

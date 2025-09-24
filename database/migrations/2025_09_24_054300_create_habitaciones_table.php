<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('habitaciones', function (Blueprint $table) {
            $table->id();

            // Pertenece a un hotel (hoteles.servicio_id es la PK)
            $table->unsignedBigInteger('servicio_id');
            $table->foreign('servicio_id')
                ->references('servicio_id')->on('hoteles')
                ->onDelete('cascade');

            $table->string('nombre', 100);                 // p.ej. "Doble", "Familiar"
            $table->unsignedSmallInteger('capacidad_adultos');
            $table->unsignedSmallInteger('capacidad_ninos')->default(0);
            $table->unsignedInteger('cantidad');           // unidades de este tipo de habitación
            $table->decimal('precio_por_noche', 10, 2);    // tarifa base por noche
            $table->text('descripcion')->nullable();

            $table->timestamps();

            // Índices útiles para búsqueda/filtrado
            $table->index(['servicio_id', 'capacidad_adultos', 'capacidad_ninos']);
            $table->index('precio_por_noche');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('habitaciones');
    }
};

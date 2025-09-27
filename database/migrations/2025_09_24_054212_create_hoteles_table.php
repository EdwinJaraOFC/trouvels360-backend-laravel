<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('hoteles', function (Blueprint $table) {
            // Relación 1:1 con servicios (solo aplica cuando servicios.tipo = 'hotel')
            $table->unsignedBigInteger('servicio_id')->primary();
            $table->string('nombre', 150);
            $table->string('direccion', 255);
            $table->unsignedTinyInteger('estrellas')->nullable(); // 1..5

            // FK
            $table->foreign('servicio_id')
                ->references('id')->on('servicios')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hoteles');
    }
};

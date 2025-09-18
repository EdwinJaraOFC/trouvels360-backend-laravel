<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('hoteles', function (Blueprint $table) {
            // PK = FK a servicios.id (1:1)
            $table->unsignedBigInteger('servicio_id')->primary();

            $table->string('direccion', 255);
            $table->unsignedTinyInteger('estrellas')->nullable(); // 1..5
            $table->decimal('precio_por_noche', 10, 2);

            $table->foreign('servicio_id')
                ->references('id')->on('servicios')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hoteles');
    }
};

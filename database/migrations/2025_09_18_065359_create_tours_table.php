<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tours', function (Blueprint $table) {
            // PK = FK a servicios.id (1:1)
            $table->unsignedBigInteger('servicio_id')->primary();

            $table->string('categoria', 100)->nullable();
            $table->string('duracion', 50)->nullable(); // ej. "4 horas"
            $table->decimal('precio_adulto', 10, 2);
            $table->decimal('precio_child', 10, 2);

            // FK explícita con cascade
            $table->foreign('servicio_id')
                ->references('id')->on('servicios')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tours');
    }
};

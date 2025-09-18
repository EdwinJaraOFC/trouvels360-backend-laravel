<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reservas', function (Blueprint $table) {
            $table->id();

            $table->string('codigo_reserva', 20)->unique();

            // FK al viajero (usuarios.id)
            $table->foreignId('usuario_id')
                ->constrained('usuarios')
                ->cascadeOnDelete();

            // FK al servicio reservado
            $table->foreignId('servicio_id')
                ->constrained('servicios')
                ->cascadeOnDelete();

            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->unsignedInteger('huespedes')->default(1);

            $table->enum('estado', ['pendiente', 'confirmada', 'cancelada'])
                ->default('pendiente');

            $table->timestamps();

            // Validación básica de rango de fechas (a nivel app, no SQL)
            // Puedes agregar un CHECK si usas MySQL 8.0+:
            // $table->check('fecha_fin >= fecha_inicio');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservas');
    }
};

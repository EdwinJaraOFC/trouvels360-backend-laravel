<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reservas_habitaciones', function (Blueprint $table) {
            $table->id();

            $table->string('codigo_reserva', 20)->unique();

            // Quién reserva
            $table->foreignId('usuario_id')
                ->constrained('usuarios')
                ->cascadeOnDelete();

            // Qué habitación (tipo) reserva
            $table->foreignId('habitacion_id')
                ->constrained('habitaciones')
                ->cascadeOnDelete();

            // Rango de estadía (fechas)
            $table->date('fecha_inicio');
            $table->date('fecha_fin');

            // Cuántas unidades de esa habitación (ej. 2 habitaciones Dobles)
            $table->unsignedSmallInteger('cantidad')->default(1);

            // Estados que bloquean disponibilidad: pendiente/confirmada
            $table->enum('estado', ['pendiente', 'confirmada', 'cancelada'])
                ->default('pendiente');

            // Snapshot de precio y total para auditoría
            $table->decimal('precio_por_noche', 10, 2);
            $table->decimal('total', 12, 2);

            $table->timestamps();

            // Índices para disponibilidad y consultas rápidas
            $table->index(['habitacion_id', 'fecha_inicio', 'fecha_fin']);
            $table->index(['usuario_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservas_habitaciones');
    }
};

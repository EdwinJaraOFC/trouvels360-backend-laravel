<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para la tabla `reservas_habitaciones`
 */
class ReservaHabitacion extends Model
{
    use HasFactory;

    protected $table = 'reservas_habitaciones';

    protected $fillable = [
        'codigo_reserva',
        'usuario_id',
        'habitacion_id',
        'fecha_inicio',
        'fecha_fin',
        'cantidad',
        'estado',
        'precio_por_noche',
        'total',
    ];

    // 🔗 Relaciones
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function habitacion()
    {
        return $this->belongsTo(Habitacion::class, 'habitacion_id');
    }
}

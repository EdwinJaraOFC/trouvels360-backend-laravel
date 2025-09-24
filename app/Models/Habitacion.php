<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para la tabla `habitaciones`
 */
class Habitacion extends Model
{
    use HasFactory;

    protected $table = 'habitaciones';

    protected $fillable = [
        'servicio_id',
        'nombre',
        'capacidad_adultos',
        'capacidad_ninos',
        'cantidad',
        'precio_por_noche',
        'descripcion',
    ];

    // 🔗 Relaciones
    public function hotel()
    {
        return $this->belongsTo(Hotel::class, 'servicio_id');
    }

    public function reservas()
    {
        return $this->hasMany(ReservaHabitacion::class, 'habitacion_id');
    }
}

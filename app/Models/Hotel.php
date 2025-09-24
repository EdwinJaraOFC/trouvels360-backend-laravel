<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para la tabla `hoteles`
 */
class Hotel extends Model
{
    use HasFactory;

    protected $table = 'hoteles';
    protected $primaryKey = 'servicio_id'; // la PK es el servicio_id
    public $incrementing = false;          // no es autoincremental
    protected $keyType = 'int';

    protected $fillable = [
        'servicio_id',
        'direccion',
        'estrellas',
    ];

    // 🔗 Relaciones
    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }

    public function habitaciones()
    {
        return $this->hasMany(Habitacion::class, 'servicio_id');
    }
}

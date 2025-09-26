<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tour extends Model
{
    use HasFactory;

    protected $table = 'tours';
    protected $primaryKey = 'servicio_id';
    public $incrementing = false;               // PK no autoincremental
    protected $keyType = 'int';

    protected $fillable = [
        'servicio_id',
        'categoria',
        'duracion_min',
        'precio_persona',
        'capacidad_por_salida',
    ];

    protected $casts = [
        'duracion_min'         => 'integer',
        'precio_persona'       => 'decimal:2',
        'capacidad_por_salida' => 'integer',
    ];

    // 👇 clave para que {tour} use servicio_id en rutas
    public function getRouteKeyName(): string
    {
        return 'servicio_id';
    }

    /** Servicio padre (debe tener tipo='tour') */
    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }

    /** Salidas programadas (fecha/hora + cupo) */
    public function salidas()
    {
        return $this->hasMany(TourSalida::class, 'servicio_id', 'servicio_id');
    }

    /** Actividades/itinerario del tour (ordenadas) */
    public function actividades()
    {
        return $this->hasMany(TourActividad::class, 'servicio_id', 'servicio_id')
                    ->orderBy('orden');
    }
}

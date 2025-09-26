<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TourActividad extends Model
{
    use HasFactory;

    protected $table = 'tour_actividades';

    protected $fillable = [
        'servicio_id',
        'titulo',
        'descripcion',
        'orden',
        'duracion_min',
        'direccion',
        'imagen_url',
    ];

    protected $casts = [
        'orden'        => 'integer',
        'duracion_min' => 'integer',
    ];

    /** Servicio (de tipo 'tour') */
    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }
}

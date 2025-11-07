<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TourItem extends Model
{
    use HasFactory;

    protected $table = 'tour_items';

    protected $fillable = [
        'servicio_id',
        'nombre',
        'icono',
    ];

    /** Servicio (padre) */
    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }

    /** Definición del tour (1:1 con servicios) */
    public function tour()
    {
        return $this->hasOne(Tour::class, 'servicio_id', 'servicio_id');
    }
}

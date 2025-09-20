<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @use HasFactory<\Database\Factories\TourFactory>
 */
class Tour extends Model
{
    use HasFactory;

    protected $table = 'tours';
    public $timestamps = false; // si no tienes created_at / updated_at

    protected $fillable = [
        'servicio_id',
        'categoria',
        'duracion',
        'precio_adulto',
        'precio_child'
    ];

    // 🔗 Relaciones
    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }
}

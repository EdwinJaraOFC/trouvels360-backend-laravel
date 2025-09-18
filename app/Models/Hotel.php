<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @use HasFactory<\Database\Factories\HotelFactory>
 */
class Hotel extends Model
{
    use HasFactory;

    protected $table = 'hoteles';
    public $timestamps = false; // si no tienes created_at / updated_at

    protected $fillable = [
        'servicio_id',
        'direccion',
        'estrellas',
        'precio_por_noche',
    ];

    // 🔗 Relaciones
    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }
}

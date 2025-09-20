<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @use HasFactory<\Database\Factories\ServicioFactory>
 */
class Servicio extends Model
{
    use HasFactory;

    protected $table = 'servicios';

    protected $fillable = [
        'proveedor_id',
        'nombre',
        'tipo',
        'descripcion',
        'ciudad',
        'horario_inicio',
        'horario_fin',
        'imagen_url',
    ];

    // Relaciones
    public function proveedor()
    {
        return $this->belongsTo(Usuario::class, 'proveedor_id');
    }

    public function hotel()
    {
        return $this->hasOne(Hotel::class, 'servicio_id');
    }

    public function tour()
    {
        return $this->hasOne(Tour::class, 'servicio_id');
    }

    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'servicio_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'imagen_url',
        'activo',
    ];

    protected $casts = [
        'activo' => 'bool',
    ];

    // Relaciones
    public function proveedor()
    {
        return $this->belongsTo(Usuario::class, 'proveedor_id');
    }

    // Mantén estas relaciones si planeas agregarlas luego
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

    // Scopes opcionales útiles para el index
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorCiudadYTipo($query, ?string $ciudad = null, ?string $tipo = null)
    {
        return $query
            ->when($ciudad, fn($q) => $q->where('ciudad', $ciudad))
            ->when($tipo, fn($q) => $q->where('tipo', $tipo));
    }
}

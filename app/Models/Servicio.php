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
        'tipo',          // 'hotel' | 'tour'
        'descripcion',
        'ciudad',
        'pais',          // <-- agregado
        'imagen_url',
        'activo',
    ];

    protected $casts = [
        'activo' => 'bool',
    ];

    // 🔗 Relaciones principales
    public function proveedor()
    {
        return $this->belongsTo(Usuario::class, 'proveedor_id');
    }

    // --- Hotel ---
    public function hotel()
    {
        return $this->hasOne(Hotel::class, 'servicio_id', 'id');
    }

    public function habitaciones()
    {
        return $this->hasMany(Habitacion::class, 'servicio_id', 'id');
    }

    public function reservasHabitaciones()
    {
        return $this->hasManyThrough(
            ReservaHabitacion::class,
            Habitacion::class,
            'servicio_id',      // FK en Habitacion -> Servicio
            'habitacion_id',    // FK en ReservaHabitacion -> Habitacion
            'id',               // PK en Servicio
            'id'                // PK en Habitacion
        );
    }

    // --- Tour ---
    public function tour()
    {
        return $this->hasOne(Tour::class, 'servicio_id', 'id');
    }

    public function salidas()
    {
        return $this->hasMany(TourSalida::class, 'servicio_id', 'id');
    }

    public function actividades()
    {
        return $this->hasMany(TourActividad::class, 'servicio_id', 'id')
                    ->orderBy('orden');
    }

    // 🔎 Scopes
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorDestino($query, ?string $pais = null, ?string $ciudad = null)
    {
        return $query
            ->when($pais,   fn($q) => $q->where('pais', $pais))
            ->when($ciudad, fn($q) => $q->where('ciudad', $ciudad));
    }

    public function scopePorTipo($query, ?string $tipo = null)
    {
        return $query->when($tipo, fn($q) => $q->where('tipo', $tipo));
    }

    // Reviews
    public function reviews()
    {
        return $this->hasMany(Review::class, 'servicio_id', 'id')
                ->orderBy('created_at', 'desc');
    }
}

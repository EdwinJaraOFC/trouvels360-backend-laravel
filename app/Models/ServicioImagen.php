<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServicioImagen extends Model
{
    use HasFactory;

    protected $table = 'servicio_imagenes';

    protected $fillable = [
        'servicio_id',
        'url',
        'alt',
    ];

    public function servicio()
    {
        return $this->belongsTo(Servicio::class);
    }

    public function scopeRecientes($query)
    {
        return $query->orderByDesc('created_at');
    }
}

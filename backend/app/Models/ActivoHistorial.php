<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivoHistorial extends Model
{
    use HasFactory;

    protected $table = 'activo_historial';

    /** Solo maneja created_at (es un registro inmutable de cambio). */
    const UPDATED_AT = null;

    protected $fillable = [
        'activo_id',
        'estado_anterior',
        'estado_nuevo',
        'situacion_anterior',
        'situacion_nueva',
        'motivo',
        'usuario_id',
    ];

    public function activo()
    {
        return $this->belongsTo(Activo::class);
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }
}

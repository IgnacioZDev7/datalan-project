<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Activo extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'activos';

    protected $fillable = [
        'producto_id',
        'codigo_interno',
        'nro_serie',
        'mac',
        'estado',
        'situacion',
        'ubicacion_id',
        'tecnico_id',
        'activo_padre_id',
        'fecha_fabricacion',
        'fecha_ingreso',
        'credenciales',
        'observaciones',
    ];

    protected $hidden = ['credenciales'];

    protected function casts(): array
    {
        return [
            'credenciales' => 'encrypted:array',
            'fecha_fabricacion' => 'date',
            'fecha_ingreso' => 'date',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        // No se audita 'credenciales' (dato sensible cifrado).
        return LogOptions::defaults()
            ->logFillable()
            ->logExcept(['credenciales'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function ubicacion()
    {
        return $this->belongsTo(Ubicacion::class);
    }

    public function tecnico()
    {
        return $this->belongsTo(Tecnico::class);
    }

    /** Maletin/kit que contiene a este activo. */
    public function padre()
    {
        return $this->belongsTo(Activo::class, 'activo_padre_id');
    }

    /** Activos contenidos (si este es un maletin/kit). */
    public function contenido()
    {
        return $this->hasMany(Activo::class, 'activo_padre_id');
    }

    public function historial()
    {
        return $this->hasMany(ActivoHistorial::class);
    }

    public function asignaciones()
    {
        return $this->hasMany(Asignacion::class);
    }
}

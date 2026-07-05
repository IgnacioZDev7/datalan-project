<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Carrete extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'carretes';

    protected $fillable = [
        'producto_id',
        'codigo',
        'nro_hilos',
        'tipo_fibra',
        'metraje_inicial',
        'metraje_disponible',
        'estado',
        'ubicacion_id',
        'carrete_padre_id',
        'fecha_ingreso',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'metraje_inicial' => 'decimal:2',
            'metraje_disponible' => 'decimal:2',
            'fecha_ingreso' => 'date',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty()->dontSubmitEmptyLogs();
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function ubicacion()
    {
        return $this->belongsTo(Ubicacion::class);
    }

    /** Bobina original de la que se corto este tramo. */
    public function padre()
    {
        return $this->belongsTo(Carrete::class, 'carrete_padre_id');
    }

    /** Tramos cortados a partir de este carrete. */
    public function cortes()
    {
        return $this->hasMany(Carrete::class, 'carrete_padre_id');
    }
}

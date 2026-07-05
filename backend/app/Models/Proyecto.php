<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Proyecto extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'proyectos';

    protected $fillable = [
        'codigo',
        'nombre',
        'empresa_id',
        'tecnico_id',
        'estado',
        'fecha_inicio',
        'fecha_fin',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty()->dontSubmitEmptyLogs();
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function tecnico()
    {
        return $this->belongsTo(Tecnico::class);
    }

    public function movimientos()
    {
        return $this->hasMany(Movimiento::class);
    }

    public function direcciones()
    {
        return $this->morphMany(Direccion::class, 'direccionable');
    }
}

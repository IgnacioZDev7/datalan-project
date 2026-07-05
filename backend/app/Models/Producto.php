<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Producto extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'productos';

    protected $fillable = [
        'codigo',
        'nombre',
        'categoria_id',
        'modelo_id',
        'unidad_id',
        'retornable',
        'especificaciones',
        'imagen',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'especificaciones' => 'array',
            'retornable' => 'boolean',
            'activo' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty()->dontSubmitEmptyLogs();
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function modelo()
    {
        return $this->belongsTo(Modelo::class);
    }

    public function unidad()
    {
        return $this->belongsTo(UnidadMedida::class, 'unidad_id');
    }

    public function existencias()
    {
        return $this->hasMany(Existencia::class);
    }

    public function activos()
    {
        return $this->hasMany(Activo::class);
    }

    public function carretes()
    {
        return $this->hasMany(Carrete::class);
    }

    /** La marca se obtiene a traves del modelo. */
    public function marca()
    {
        return $this->modelo?->marca;
    }
}

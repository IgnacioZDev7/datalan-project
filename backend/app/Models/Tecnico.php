<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Tecnico extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'tecnicos';

    protected $fillable = ['nombre', 'ci', 'telefono', 'cargo', 'usuario_id', 'activo'];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty()->dontSubmitEmptyLogs();
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    public function proyectos()
    {
        return $this->hasMany(Proyecto::class);
    }

    public function asignaciones()
    {
        return $this->hasMany(Asignacion::class);
    }

    public function activos()
    {
        return $this->hasMany(Activo::class);
    }
}

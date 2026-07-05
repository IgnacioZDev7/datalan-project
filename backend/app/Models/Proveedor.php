<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Proveedor extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'proveedores';

    protected $fillable = ['nombre', 'nit', 'telefono', 'contacto', 'activo'];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty()->dontSubmitEmptyLogs();
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

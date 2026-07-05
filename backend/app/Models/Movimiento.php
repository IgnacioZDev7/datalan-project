<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Movimiento extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'movimientos';

    protected $fillable = [
        'codigo',
        'tipo',
        'fecha',
        'almacen_origen_id',
        'almacen_destino_id',
        'proveedor_id',
        'proyecto_id',
        'tecnico_id',
        'inventario_fisico_id',
        'registrado_por',
        'autorizado_por',
        'documento_referencia',
        'observaciones',
    ];

    protected function casts(): array
    {
        return ['fecha' => 'datetime'];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty()->dontSubmitEmptyLogs();
    }

    public function almacenOrigen()
    {
        return $this->belongsTo(Almacen::class, 'almacen_origen_id');
    }

    public function almacenDestino()
    {
        return $this->belongsTo(Almacen::class, 'almacen_destino_id');
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class);
    }

    public function tecnico()
    {
        return $this->belongsTo(Tecnico::class);
    }

    public function inventarioFisico()
    {
        return $this->belongsTo(InventarioFisico::class);
    }

    public function registradoPor()
    {
        return $this->belongsTo(Usuario::class, 'registrado_por');
    }

    public function autorizadoPor()
    {
        return $this->belongsTo(Usuario::class, 'autorizado_por');
    }

    public function detalles()
    {
        return $this->hasMany(MovimientoDetalle::class);
    }
}

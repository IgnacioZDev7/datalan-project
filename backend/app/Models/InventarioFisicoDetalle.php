<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventarioFisicoDetalle extends Model
{
    use HasFactory;

    protected $table = 'inventario_fisico_detalles';

    protected $fillable = [
        'inventario_fisico_id',
        'producto_id',
        'activo_id',
        'carrete_id',
        'cantidad_sistema',
        'cantidad_fisica',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'cantidad_sistema' => 'decimal:2',
            'cantidad_fisica' => 'decimal:2',
        ];
    }

    /** Diferencia derivada (no se almacena). */
    public function getDiferenciaAttribute(): float
    {
        return (float) $this->cantidad_fisica - (float) $this->cantidad_sistema;
    }

    public function inventarioFisico()
    {
        return $this->belongsTo(InventarioFisico::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function activo()
    {
        return $this->belongsTo(Activo::class);
    }

    public function carrete()
    {
        return $this->belongsTo(Carrete::class);
    }
}

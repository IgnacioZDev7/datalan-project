<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimientoDetalle extends Model
{
    use HasFactory;

    protected $table = 'movimiento_detalles';

    protected $fillable = [
        'movimiento_id',
        'producto_id',
        'activo_id',
        'carrete_id',
        'cantidad',
        'metraje',
        'estado',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:2',
            'metraje' => 'decimal:2',
        ];
    }

    public function movimiento()
    {
        return $this->belongsTo(Movimiento::class);
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

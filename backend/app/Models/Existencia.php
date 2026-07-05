<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Existencia extends Model
{
    use HasFactory;

    protected $table = 'existencias';

    protected $fillable = [
        'producto_id',
        'almacen_id',
        'cantidad_actual',
        'cantidad_minima',
        'cantidad_maxima',
    ];

    protected function casts(): array
    {
        return [
            'cantidad_actual' => 'decimal:2',
            'cantidad_minima' => 'decimal:2',
            'cantidad_maxima' => 'decimal:2',
        ];
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function almacen()
    {
        return $this->belongsTo(Almacen::class);
    }

    /** True si la existencia esta en o por debajo del minimo. */
    public function getBajoMinimoAttribute(): bool
    {
        return $this->cantidad_actual <= $this->cantidad_minima;
    }
}

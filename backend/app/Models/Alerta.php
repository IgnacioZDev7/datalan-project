<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alerta extends Model
{
    use HasFactory;

    protected $table = 'alertas';

    protected $fillable = [
        'tipo',
        'producto_id',
        'activo_id',
        'mensaje',
        'nivel',
        'leida',
    ];

    protected function casts(): array
    {
        return ['leida' => 'boolean'];
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function activo()
    {
        return $this->belongsTo(Activo::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventarioFisico extends Model
{
    use HasFactory;

    protected $table = 'inventarios_fisicos';

    protected $fillable = [
        'codigo',
        'almacen_id',
        'fecha',
        'responsable_id',
        'estado',
        'observaciones',
    ];

    protected function casts(): array
    {
        return ['fecha' => 'date'];
    }

    public function almacen()
    {
        return $this->belongsTo(Almacen::class);
    }

    public function responsable()
    {
        return $this->belongsTo(Usuario::class, 'responsable_id');
    }

    public function detalles()
    {
        return $this->hasMany(InventarioFisicoDetalle::class);
    }

    public function movimientos()
    {
        return $this->hasMany(Movimiento::class);
    }
}

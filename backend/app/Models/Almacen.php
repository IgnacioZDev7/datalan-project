<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Almacen extends Model
{
    use HasFactory;

    protected $table = 'almacenes';

    protected $fillable = ['nombre', 'responsable_id', 'direccion_id', 'activo'];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function responsable()
    {
        return $this->belongsTo(Usuario::class, 'responsable_id');
    }

    public function ubicaciones()
    {
        return $this->hasMany(Ubicacion::class);
    }

    public function existencias()
    {
        return $this->hasMany(Existencia::class);
    }

    public function direccion()
    {
        return $this->belongsTo(Direccion::class);
    }
}

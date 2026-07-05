<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ubicacion extends Model
{
    use HasFactory;

    protected $table = 'ubicaciones';

    protected $fillable = ['almacen_id', 'codigo', 'descripcion'];

    public function almacen()
    {
        return $this->belongsTo(Almacen::class);
    }

    public function activos()
    {
        return $this->hasMany(Activo::class);
    }

    public function carretes()
    {
        return $this->hasMany(Carrete::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Direccion extends Model
{
    use HasFactory;

    protected $table = 'direcciones';

    protected $fillable = [
        'ciudad',
        'zona',
        'calle',
        'nro',
        'referencia',
    ];

    // Cada entidad (usuario, almacen, empresa, proveedor, proyecto) referencia
    // esta direccion con su columna direccion_id (FK). Relaciones inversas:

    public function usuarios()
    {
        return $this->hasMany(Usuario::class);
    }

    public function almacenes()
    {
        return $this->hasMany(Almacen::class);
    }

    public function empresas()
    {
        return $this->hasMany(Empresa::class);
    }

    public function proveedores()
    {
        return $this->hasMany(Proveedor::class);
    }

    public function proyectos()
    {
        return $this->hasMany(Proyecto::class);
    }

    /** Direccion en una linea legible. */
    public function getCompletaAttribute(): string
    {
        return trim(collect([$this->calle, $this->nro, $this->zona, $this->ciudad])
            ->filter()->implode(', '));
    }
}

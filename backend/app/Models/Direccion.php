<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Direccion extends Model
{
    use HasFactory;

    protected $table = 'direcciones';

    protected $fillable = [
        'direccionable_type',
        'direccionable_id',
        'ciudad',
        'zona',
        'calle',
        'nro',
        'referencia',
    ];

    public function direccionable()
    {
        return $this->morphTo();
    }
}

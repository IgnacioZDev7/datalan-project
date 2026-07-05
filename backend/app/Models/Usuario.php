<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class Usuario extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UsuarioFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRoles;

    protected $table = 'usuarios';

    /**
     * Atributos asignables masivamente.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nombres',
        'apellido_paterno',
        'apellido_materno',
        'ci',
        'correo_electronico',
        'contrasena',
        'cargo',
        'telefono',
        'activo',
        'ultimo_acceso',
    ];

    /**
     * Atributos ocultos en la serializacion.
     *
     * @var list<string>
     */
    protected $hidden = [
        'contrasena',
        'remember_token',
    ];

    /**
     * Casts de atributos.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'contrasena' => 'hashed',
            'ultimo_acceso' => 'datetime',
            'activo' => 'boolean',
        ];
    }

    /**
     * La columna de contraseña de este proyecto es 'contrasena'.
     */
    public function getAuthPassword(): string
    {
        return $this->contrasena;
    }

    /**
     * Nombre completo del usuario (helper de dominio).
     */
    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->nombres} {$this->apellido_paterno} {$this->apellido_materno}");
    }

    // ---------------------------------------------------------------
    // Relaciones
    // ---------------------------------------------------------------

    public function direcciones()
    {
        return $this->morphMany(Direccion::class, 'direccionable');
    }

    /** Ficha de tecnico vinculada (si el usuario ademas es tecnico de campo). */
    public function tecnico()
    {
        return $this->hasOne(Tecnico::class);
    }

    public function almacenesACargo()
    {
        return $this->hasMany(Almacen::class, 'responsable_id');
    }

    public function movimientosRegistrados()
    {
        return $this->hasMany(Movimiento::class, 'registrado_por');
    }
}

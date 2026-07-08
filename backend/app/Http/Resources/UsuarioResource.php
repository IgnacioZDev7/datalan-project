<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UsuarioResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombres' => $this->nombres,
            'apellido_paterno' => $this->apellido_paterno,
            'apellido_materno' => $this->apellido_materno,
            'nombre_completo' => $this->nombre_completo,
            'ci' => $this->ci,
            'correo_electronico' => $this->correo_electronico,
            'cargo' => $this->cargo,
            'telefono' => $this->telefono,
            'activo' => $this->activo,
            'ultimo_acceso' => $this->ultimo_acceso,
            'roles' => $this->getRoleNames(),
            'permisos' => $this->getAllPermissions()->pluck('name'),
        ];
    }
}

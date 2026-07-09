<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TecnicoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'ci' => $this->ci,
            'telefono' => $this->telefono,
            'cargo' => $this->cargo,
            'usuario_id' => $this->usuario_id,
            'activo' => $this->activo,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'usuario' => new UsuarioResource($this->whenLoaded('usuario')),
        ];
    }
}

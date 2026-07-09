<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AlmacenResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'responsable_id' => $this->responsable_id,
            'direccion_id' => $this->direccion_id,
            'activo' => $this->activo,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'responsable' => new UsuarioResource($this->whenLoaded('responsable')),
            'direccion' => new DireccionResource($this->whenLoaded('direccion')),
        ];
    }
}

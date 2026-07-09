<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmpresaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'nit' => $this->nit,
            'telefono' => $this->telefono,
            'contacto' => $this->contacto,
            'direccion_id' => $this->direccion_id,
            'activo' => $this->activo,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'direccion' => new DireccionResource($this->whenLoaded('direccion')),
        ];
    }
}

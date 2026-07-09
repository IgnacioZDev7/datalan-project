<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UbicacionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'almacen_id' => $this->almacen_id,
            'codigo' => $this->codigo,
            'descripcion' => $this->descripcion,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'almacen' => new AlmacenResource($this->whenLoaded('almacen')),
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventarioFisicoDetalleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'producto_id' => $this->producto_id,
            'activo_id' => $this->activo_id,
            'carrete_id' => $this->carrete_id,
            'cantidad_sistema' => $this->cantidad_sistema,
            'cantidad_fisica' => $this->cantidad_fisica,
            'diferencia' => $this->diferencia,
            'observaciones' => $this->observaciones,
            'producto' => new ProductoResource($this->whenLoaded('producto')),
        ];
    }
}

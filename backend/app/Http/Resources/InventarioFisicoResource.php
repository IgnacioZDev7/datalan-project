<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventarioFisicoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'codigo' => $this->codigo,
            'almacen_id' => $this->almacen_id,
            'fecha' => $this->fecha,
            'responsable_id' => $this->responsable_id,
            'estado' => $this->estado,
            'observaciones' => $this->observaciones,
            'created_at' => $this->created_at,
            'detalles_count' => $this->detalles_count ?? $this->detalles?->count() ?? 0,
            'detalles' => InventarioFisicoDetalleResource::collection($this->whenLoaded('detalles')),
        ];
    }
}

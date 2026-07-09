<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MovimientoDetalleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'producto_id' => $this->producto_id,
            'activo_id' => $this->activo_id,
            'carrete_id' => $this->carrete_id,
            'cantidad' => $this->cantidad,
            'metraje' => $this->metraje,
            'estado' => $this->estado,
            'observaciones' => $this->observaciones,

            'producto' => new ProductoResource($this->whenLoaded('producto')),
            'activo' => $this->whenLoaded('activo', fn () => [
                'id' => $this->activo->id,
                'codigo_interno' => $this->activo->codigo_interno,
            ]),
            'carrete' => $this->whenLoaded('carrete', fn () => [
                'id' => $this->carrete->id,
                'codigo' => $this->carrete->codigo,
                'metraje_disponible' => $this->carrete->metraje_disponible,
            ]),
        ];
    }
}

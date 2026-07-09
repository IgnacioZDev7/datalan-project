<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AlertaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tipo' => $this->tipo,
            'producto_id' => $this->producto_id,
            'activo_id' => $this->activo_id,
            'mensaje' => $this->mensaje,
            'nivel' => $this->nivel,
            'leida' => $this->leida,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'producto' => new ProductoResource($this->whenLoaded('producto')),
            'activo' => $this->whenLoaded('activo', function () {
                return [
                    'id' => $this->activo->id,
                    'codigo_interno' => $this->activo->codigo_interno,
                    'nombre' => $this->activo->nombre,
                ];
            }),
        ];
    }
}

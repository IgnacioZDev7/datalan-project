<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExistenciaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'producto_id' => $this->producto_id,
            'almacen_id' => $this->almacen_id,
            'cantidad_actual' => $this->cantidad_actual,
            'cantidad_minima' => $this->cantidad_minima,
            'cantidad_maxima' => $this->cantidad_maxima,
            'bajo_minimo' => $this->bajo_minimo,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'producto' => new ProductoResource($this->whenLoaded('producto')),
            'almacen' => $this->whenLoaded('almacen', function () {
                return [
                    'id' => $this->almacen->id,
                    'nombre' => $this->almacen->nombre,
                ];
            }),
        ];
    }
}

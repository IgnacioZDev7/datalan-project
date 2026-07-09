<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CarreteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'producto_id' => $this->producto_id,
            'codigo' => $this->codigo,
            'nro_hilos' => $this->nro_hilos,
            'tipo_fibra' => $this->tipo_fibra,
            'metraje_inicial' => $this->metraje_inicial,
            'metraje_disponible' => $this->metraje_disponible,
            'estado' => $this->estado,
            'ubicacion_id' => $this->ubicacion_id,
            'carrete_padre_id' => $this->carrete_padre_id,
            'fecha_ingreso' => $this->fecha_ingreso,
            'observaciones' => $this->observaciones,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'producto' => new ProductoResource($this->whenLoaded('producto')),
            'ubicacion' => $this->whenLoaded('ubicacion', fn () => [
                'id' => $this->ubicacion->id,
                'codigo' => $this->ubicacion->codigo,
                'almacen_id' => $this->ubicacion->almacen_id,
            ]),
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'producto_id' => $this->producto_id,
            'codigo_interno' => $this->codigo_interno,
            'nro_serie' => $this->nro_serie,
            'mac' => $this->mac,
            'estado' => $this->estado,
            'situacion' => $this->situacion,
            'ubicacion_id' => $this->ubicacion_id,
            'tecnico_id' => $this->tecnico_id,
            'activo_padre_id' => $this->activo_padre_id,
            'fecha_fabricacion' => $this->fecha_fabricacion,
            'fecha_ingreso' => $this->fecha_ingreso,
            // Nunca se expone el contenido de credenciales (dato sensible cifrado).
            'tiene_credenciales' => ! empty($this->credenciales),
            'observaciones' => $this->observaciones,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'producto' => new ProductoResource($this->whenLoaded('producto')),
            'ubicacion' => $this->whenLoaded('ubicacion', fn () => [
                'id' => $this->ubicacion->id,
                'codigo' => $this->ubicacion->codigo,
                'almacen_id' => $this->ubicacion->almacen_id,
            ]),
            'tecnico' => $this->whenLoaded('tecnico', fn () => [
                'id' => $this->tecnico->id,
                'nombre' => $this->tecnico->nombre,
            ]),
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AsignacionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'activo_id' => $this->activo_id,
            'tecnico_id' => $this->tecnico_id,
            'proyecto_id' => $this->proyecto_id,
            'fecha_asignacion' => $this->fecha_asignacion,
            'fecha_devolucion' => $this->fecha_devolucion,
            'estado' => $this->estado,
            'observaciones' => $this->observaciones,
            'registrado_por' => $this->registrado_por,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'activo' => new ActivoResource($this->whenLoaded('activo')),
            'tecnico' => new TecnicoResource($this->whenLoaded('tecnico')),
            'proyecto' => new ProyectoResource($this->whenLoaded('proyecto')),
        ];
    }
}

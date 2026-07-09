<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProyectoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'codigo' => $this->codigo,
            'nombre' => $this->nombre,
            'empresa_id' => $this->empresa_id,
            'tecnico_id' => $this->tecnico_id,
            'direccion_id' => $this->direccion_id,
            'estado' => $this->estado,
            'fecha_inicio' => $this->fecha_inicio,
            'fecha_fin' => $this->fecha_fin,
            'observaciones' => $this->observaciones,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'empresa' => new EmpresaResource($this->whenLoaded('empresa')),
            'tecnico' => new TecnicoResource($this->whenLoaded('tecnico')),
            'direccion' => new DireccionResource($this->whenLoaded('direccion')),
        ];
    }
}

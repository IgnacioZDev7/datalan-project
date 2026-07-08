<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'codigo' => $this->codigo,
            'nombre' => $this->nombre,
            'categoria_id' => $this->categoria_id,
            'modelo_id' => $this->modelo_id,
            'unidad_id' => $this->unidad_id,
            'retornable' => $this->retornable,
            'especificaciones' => $this->especificaciones,
            'imagen' => $this->imagen,
            'imagen_url' => $this->imagen ? Storage::disk('public')->url($this->imagen) : null,
            'activo' => $this->activo,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Relaciones (solo si fueron cargadas)
            'categoria' => new CategoriaResource($this->whenLoaded('categoria')),
            'modelo' => new ModeloResource($this->whenLoaded('modelo')),
            'unidad' => new UnidadMedidaResource($this->whenLoaded('unidad')),
        ];
    }
}

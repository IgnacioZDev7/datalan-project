<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoriaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'tipo_inventario' => $this->tipo_inventario,
            'categoria_padre_id' => $this->categoria_padre_id,
            'activo' => $this->activo,
        ];
    }
}

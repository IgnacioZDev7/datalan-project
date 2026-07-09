<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DireccionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ciudad' => $this->ciudad,
            'zona' => $this->zona,
            'calle' => $this->calle,
            'nro' => $this->nro,
            'referencia' => $this->referencia,
            'completa' => $this->completa,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MovimientoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'codigo' => $this->codigo,
            'tipo' => $this->tipo,
            'fecha' => $this->fecha,
            'almacen_origen_id' => $this->almacen_origen_id,
            'almacen_destino_id' => $this->almacen_destino_id,
            'proveedor_id' => $this->proveedor_id,
            'proyecto_id' => $this->proyecto_id,
            'tecnico_id' => $this->tecnico_id,
            'inventario_fisico_id' => $this->inventario_fisico_id,
            'registrado_por' => $this->registrado_por,
            'autorizado_por' => $this->autorizado_por,
            'documento_referencia' => $this->documento_referencia,
            'observaciones' => $this->observaciones,
            'anulado' => ! is_null($this->deleted_at),
            'created_at' => $this->created_at,

            'detalles' => MovimientoDetalleResource::collection($this->whenLoaded('detalles')),
        ];
    }
}

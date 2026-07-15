<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BitacoraResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'log_name'      => $this->log_name,
            'description'   => $this->description,
            'event'         => $this->event,
            'subject_type'  => $this->subject_type,
            'subject_id'    => $this->subject_id,
            'properties'    => $this->properties,
            'causer'        => $this->whenLoaded('causer', fn () => [
                'id'     => $this->causer->id,
                'nombre' => trim(($this->causer->nombres ?? '') . ' ' . ($this->causer->apellido_paterno ?? '')),
                'correo_electronico' => $this->causer->correo_electronico ?? null,
            ]),
            'created_at'    => $this->created_at,
        ];
    }
}

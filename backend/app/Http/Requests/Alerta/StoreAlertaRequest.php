<?php

namespace App\Http\Requests\Alerta;

use Illuminate\Foundation\Http\FormRequest;

class StoreAlertaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tipo' => ['required', 'string', 'max:60'],
            'producto_id' => ['nullable', 'integer', 'exists:productos,id'],
            'activo_id' => ['nullable', 'integer', 'exists:activos,id'],
            'mensaje' => ['required', 'string', 'max:500'],
            'nivel' => ['nullable', 'string', 'max:30'],
            'leida' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'tipo.required' => 'El tipo es obligatorio.',
            'mensaje.required' => 'El mensaje es obligatorio.',
            'producto_id.exists' => 'El producto seleccionado no existe.',
            'activo_id.exists' => 'El activo seleccionado no existe.',
        ];
    }
}

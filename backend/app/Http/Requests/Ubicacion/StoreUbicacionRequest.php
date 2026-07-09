<?php

namespace App\Http\Requests\Ubicacion;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUbicacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'almacen_id' => ['required', 'integer', 'exists:almacenes,id'],
            'codigo' => ['required', 'string', 'max:60',
                Rule::unique('ubicaciones', 'codigo')],
            'descripcion' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'almacen_id.required' => 'El almacén es obligatorio.',
            'almacen_id.exists' => 'El almacén seleccionado no existe.',
            'codigo.required' => 'El código es obligatorio.',
            'codigo.unique' => 'Ya existe una ubicación con ese código.',
        ];
    }
}

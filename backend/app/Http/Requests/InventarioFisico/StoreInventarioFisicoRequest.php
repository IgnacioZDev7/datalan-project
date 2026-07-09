<?php

namespace App\Http\Requests\InventarioFisico;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInventarioFisicoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'codigo' => ['required', 'string', 'max:60', Rule::unique('inventarios_fisicos', 'codigo')],
            'almacen_id' => ['required', 'integer', 'exists:almacenes,id'],
            'fecha' => ['required', 'date'],
            'observaciones' => ['nullable', 'string'],
            'detalles' => ['required', 'array', 'min:1'],
            'detalles.*.producto_id' => ['required', 'integer', 'exists:productos,id'],
            'detalles.*.activo_id' => ['nullable', 'integer', 'exists:activos,id'],
            'detalles.*.carrete_id' => ['nullable', 'integer', 'exists:carretes,id'],
            'detalles.*.cantidad_fisica' => ['required', 'numeric', 'min:0'],
            'detalles.*.observaciones' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'codigo.unique' => 'Ya existe un conteo con ese código.',
            'almacen_id.required' => 'El almacén es obligatorio.',
            'detalles.required' => 'El conteo debe tener al menos un renglón.',
            'detalles.*.cantidad_fisica.required' => 'La cantidad física es obligatoria en cada renglón.',
        ];
    }
}

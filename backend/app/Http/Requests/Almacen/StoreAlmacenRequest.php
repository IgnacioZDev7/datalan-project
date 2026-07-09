<?php

namespace App\Http\Requests\Almacen;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAlmacenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:180',
                Rule::unique('almacenes', 'nombre')],
            'responsable_id' => ['nullable', 'integer', 'exists:users,id'],
            'direccion_id' => ['nullable', 'integer', 'exists:direcciones,id'],
            'activo' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.unique' => 'Ya existe un almacén con ese nombre.',
            'responsable_id.exists' => 'El responsable seleccionado no existe.',
            'direccion_id.exists' => 'La dirección seleccionada no existe.',
        ];
    }
}

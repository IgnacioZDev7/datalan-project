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
            'activo' => ['boolean'],
            'direccion' => ['nullable', 'array'],
            'direccion.ciudad' => ['nullable', 'string', 'max:80'],
            'direccion.zona' => ['nullable', 'string', 'max:100'],
            'direccion.calle' => ['nullable', 'string', 'max:150'],
            'direccion.nro' => ['nullable', 'string', 'max:20'],
            'direccion.referencia' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.unique' => 'Ya existe un almacén con ese nombre.',
            'responsable_id.exists' => 'El responsable seleccionado no existe.',
        ];
    }
}

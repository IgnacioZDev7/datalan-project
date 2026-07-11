<?php

namespace App\Http\Requests\Proveedor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProveedorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:180',
                Rule::unique('proveedores', 'nombre')->whereNull('deleted_at')],
            'nit' => ['nullable', 'string', 'max:60'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'contacto' => ['nullable', 'string', 'max:180'],
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
            'nombre.unique' => 'Ya existe un proveedor con ese nombre.',
        ];
    }
}

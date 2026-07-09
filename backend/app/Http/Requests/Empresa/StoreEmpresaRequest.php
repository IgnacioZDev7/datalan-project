<?php

namespace App\Http\Requests\Empresa;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmpresaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:180',
                Rule::unique('empresas', 'nombre')->whereNull('deleted_at')],
            'nit' => ['nullable', 'string', 'max:60'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'contacto' => ['nullable', 'string', 'max:180'],
            'direccion_id' => ['nullable', 'integer', 'exists:direcciones,id'],
            'activo' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.unique' => 'Ya existe una empresa con ese nombre.',
            'direccion_id.exists' => 'La dirección seleccionada no existe.',
        ];
    }
}

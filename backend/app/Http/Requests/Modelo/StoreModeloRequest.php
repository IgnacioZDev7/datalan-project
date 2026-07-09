<?php

namespace App\Http\Requests\Modelo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreModeloRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'marca_id' => ['required', 'integer', 'exists:marcas,id'],
            'nombre' => ['required', 'string', 'max:180',
                Rule::unique('modelos', 'nombre')->whereNull('deleted_at')],
            'descripcion' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'marca_id.required' => 'La marca es obligatoria.',
            'marca_id.exists' => 'La marca seleccionada no existe.',
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.unique' => 'Ya existe un modelo con ese nombre.',
        ];
    }
}

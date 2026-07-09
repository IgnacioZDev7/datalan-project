<?php

namespace App\Http\Requests\Categoria;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:180',
                Rule::unique('categorias', 'nombre')->whereNull('deleted_at')],
            'tipo_inventario' => ['nullable', 'string', 'max:60'],
            'categoria_padre_id' => ['nullable', 'integer', 'exists:categorias,id'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'activo' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.unique' => 'Ya existe una categoría con ese nombre.',
            'categoria_padre_id.exists' => 'La categoría padre seleccionada no existe.',
        ];
    }
}

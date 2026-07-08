<?php

namespace App\Http\Requests\Producto;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // autorizacion via middleware can:productos.crear
    }

    public function rules(): array
    {
        return [
            'codigo' => ['required', 'string', 'max:60',
                Rule::unique('productos', 'codigo')->whereNull('deleted_at')],
            'nombre' => ['required', 'string', 'max:180'],
            'categoria_id' => ['required', 'integer', 'exists:categorias,id'],
            'modelo_id' => ['nullable', 'integer', 'exists:modelos,id'],
            'unidad_id' => ['required', 'integer', 'exists:unidades_medida,id'],
            'retornable' => ['boolean'],
            'especificaciones' => ['nullable', 'array'],
            'imagen' => ['nullable', 'image', 'max:2048'],
            'activo' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'codigo.required' => 'El código es obligatorio.',
            'codigo.unique' => 'Ya existe un producto con ese código.',
            'nombre.required' => 'El nombre es obligatorio.',
            'categoria_id.required' => 'La categoría es obligatoria.',
            'categoria_id.exists' => 'La categoría seleccionada no existe.',
            'unidad_id.required' => 'La unidad de medida es obligatoria.',
            'unidad_id.exists' => 'La unidad de medida no existe.',
            'modelo_id.exists' => 'El modelo seleccionado no existe.',
            'imagen.image' => 'El archivo debe ser una imagen.',
            'imagen.max' => 'La imagen no debe superar los 2 MB.',
        ];
    }
}

<?php

namespace App\Http\Requests\Producto;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // autorizacion via middleware can:productos.editar
    }

    public function rules(): array
    {
        $id = $this->route('producto')?->id;

        return [
            'codigo' => ['sometimes', 'required', 'string', 'max:60',
                Rule::unique('productos', 'codigo')->ignore($id)->whereNull('deleted_at')],
            'nombre' => ['sometimes', 'required', 'string', 'max:180'],
            'categoria_id' => ['sometimes', 'required', 'integer', 'exists:categorias,id'],
            'modelo_id' => ['nullable', 'integer', 'exists:modelos,id'],
            'unidad_id' => ['sometimes', 'required', 'integer', 'exists:unidades_medida,id'],
            'retornable' => ['boolean'],
            'especificaciones' => ['nullable', 'array'],
            'imagen' => ['nullable', 'image', 'max:2048'],
            'activo' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'codigo.unique' => 'Ya existe un producto con ese código.',
            'categoria_id.exists' => 'La categoría seleccionada no existe.',
            'unidad_id.exists' => 'La unidad de medida no existe.',
            'modelo_id.exists' => 'El modelo seleccionado no existe.',
            'imagen.image' => 'El archivo debe ser una imagen.',
            'imagen.max' => 'La imagen no debe superar los 2 MB.',
        ];
    }
}

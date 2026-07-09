<?php

namespace App\Http\Requests\Modelo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateModeloRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('modelo')?->id;

        return [
            'marca_id' => ['sometimes', 'required', 'integer', 'exists:marcas,id'],
            'nombre' => ['sometimes', 'required', 'string', 'max:180',
                Rule::unique('modelos', 'nombre')->ignore($id)->whereNull('deleted_at')],
            'descripcion' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'marca_id.exists' => 'La marca seleccionada no existe.',
            'nombre.unique' => 'Ya existe un modelo con ese nombre.',
        ];
    }
}

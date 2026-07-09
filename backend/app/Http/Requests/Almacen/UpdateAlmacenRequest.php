<?php

namespace App\Http\Requests\Almacen;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAlmacenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('almacen')?->id;

        return [
            'nombre' => ['sometimes', 'required', 'string', 'max:180',
                Rule::unique('almacenes', 'nombre')->ignore($id)],
            'responsable_id' => ['nullable', 'integer', 'exists:users,id'],
            'direccion_id' => ['nullable', 'integer', 'exists:direcciones,id'],
            'activo' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.unique' => 'Ya existe un almacén con ese nombre.',
            'responsable_id.exists' => 'El responsable seleccionado no existe.',
            'direccion_id.exists' => 'La dirección seleccionada no existe.',
        ];
    }
}

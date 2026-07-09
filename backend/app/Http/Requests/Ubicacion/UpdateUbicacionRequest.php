<?php

namespace App\Http\Requests\Ubicacion;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUbicacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('ubicacion')?->id;

        return [
            'almacen_id' => ['sometimes', 'required', 'integer', 'exists:almacenes,id'],
            'codigo' => ['sometimes', 'required', 'string', 'max:60',
                Rule::unique('ubicaciones', 'codigo')->ignore($id)],
            'descripcion' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'almacen_id.exists' => 'El almacén seleccionado no existe.',
            'codigo.unique' => 'Ya existe una ubicación con ese código.',
        ];
    }
}

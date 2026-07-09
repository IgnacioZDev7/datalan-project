<?php

namespace App\Http\Requests\Marca;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMarcaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('marca')?->id;

        return [
            'nombre' => ['sometimes', 'required', 'string', 'max:180',
                Rule::unique('marcas', 'nombre')->ignore($id)->whereNull('deleted_at')],
            'activo' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.unique' => 'Ya existe una marca con ese nombre.',
        ];
    }
}

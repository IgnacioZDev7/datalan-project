<?php

namespace App\Http\Requests\UnidadMedida;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUnidadMedidaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('unidad_medidum')?->id;

        return [
            'nombre' => ['sometimes', 'required', 'string', 'max:100',
                Rule::unique('unidades_medida', 'nombre')->ignore($id)],
            'abreviatura' => ['sometimes', 'required', 'string', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.unique' => 'Ya existe una unidad de medida con ese nombre.',
        ];
    }
}

<?php

namespace App\Http\Requests\UnidadMedida;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUnidadMedidaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:100',
                Rule::unique('unidades_medida', 'nombre')],
            'abreviatura' => ['required', 'string', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.unique' => 'Ya existe una unidad de medida con ese nombre.',
            'abreviatura.required' => 'La abreviatura es obligatoria.',
        ];
    }
}

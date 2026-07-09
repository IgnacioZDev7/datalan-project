<?php

namespace App\Http\Requests\Tecnico;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTecnicoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('tecnico')?->id;

        return [
            'nombre' => ['sometimes', 'required', 'string', 'max:180'],
            'ci' => ['nullable', 'string', 'max:30',
                Rule::unique('tecnicos', 'ci')->ignore($id)->whereNull('deleted_at')],
            'telefono' => ['nullable', 'string', 'max:30'],
            'cargo' => ['nullable', 'string', 'max:120'],
            'usuario_id' => ['nullable', 'integer', 'exists:users,id'],
            'activo' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'ci.unique' => 'Ya existe un técnico con ese CI.',
            'usuario_id.exists' => 'El usuario seleccionado no existe.',
        ];
    }
}

<?php

namespace App\Http\Requests\Usuario;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('usuario')?->id;

        return [
            'nombres' => ['sometimes', 'required', 'string', 'max:100'],
            'apellido_paterno' => ['nullable', 'string', 'max:60'],
            'apellido_materno' => ['nullable', 'string', 'max:60'],
            'ci' => ['nullable', 'string', 'max:20', Rule::unique('usuarios', 'ci')->ignore($id)],
            'correo_electronico' => ['sometimes', 'required', 'email', 'max:150',
                Rule::unique('usuarios', 'correo_electronico')->ignore($id)],
            'contrasena' => ['nullable', 'string', 'min:6'],
            'cargo' => ['nullable', 'string', 'max:100'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'activo' => ['boolean'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', Rule::exists('roles', 'name')],
        ];
    }

    public function messages(): array
    {
        return [
            'correo_electronico.unique' => 'Ya existe un usuario con ese correo.',
            'ci.unique' => 'Ya existe un usuario con ese CI.',
            'contrasena.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'roles.*.exists' => 'Uno de los roles no existe.',
        ];
    }
}

<?php

namespace App\Http\Requests\Usuario;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombres' => ['required', 'string', 'max:100'],
            'apellido_paterno' => ['nullable', 'string', 'max:60'],
            'apellido_materno' => ['nullable', 'string', 'max:60'],
            'ci' => ['nullable', 'string', 'max:20', Rule::unique('usuarios', 'ci')],
            'correo_electronico' => ['required', 'email', 'max:150', Rule::unique('usuarios', 'correo_electronico')],
            'contrasena' => ['required', 'string', 'min:6'],
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
            'nombres.required' => 'El nombre es obligatorio.',
            'correo_electronico.required' => 'El correo electrónico es obligatorio.',
            'correo_electronico.unique' => 'Ya existe un usuario con ese correo.',
            'ci.unique' => 'Ya existe un usuario con ese CI.',
            'contrasena.required' => 'La contraseña es obligatoria.',
            'contrasena.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'roles.*.exists' => 'Uno de los roles no existe.',
        ];
    }
}

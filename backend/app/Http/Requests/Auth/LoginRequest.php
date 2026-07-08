<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'correo_electronico' => ['required', 'email'],
            'contrasena' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'correo_electronico.required' => 'El correo electronico es obligatorio.',
            'correo_electronico.email' => 'El correo electronico no es valido.',
            'contrasena.required' => 'La contraseña es obligatoria.',
        ];
    }
}

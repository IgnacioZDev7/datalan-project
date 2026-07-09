<?php

namespace App\Http\Requests\Direccion;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDireccionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ciudad' => ['sometimes', 'required', 'string', 'max:120'],
            'zona' => ['nullable', 'string', 'max:120'],
            'calle' => ['nullable', 'string', 'max:120'],
            'nro' => ['nullable', 'string', 'max:20'],
            'referencia' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'ciudad.required' => 'La ciudad es obligatoria.',
        ];
    }
}

<?php

namespace App\Http\Requests\Proyecto;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProyectoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('proyecto')?->id;

        return [
            'codigo' => ['sometimes', 'required', 'string', 'max:60',
                Rule::unique('proyectos', 'codigo')->ignore($id)->whereNull('deleted_at')],
            'nombre' => ['sometimes', 'required', 'string', 'max:180'],
            'empresa_id' => ['nullable', 'integer', 'exists:empresas,id'],
            'tecnico_id' => ['nullable', 'integer', 'exists:tecnicos,id'],
            'estado' => ['nullable', 'string', 'max:60'],
            'direccion' => ['nullable', 'array'],
            'direccion.ciudad' => ['nullable', 'string', 'max:80'],
            'direccion.zona' => ['nullable', 'string', 'max:100'],
            'direccion.calle' => ['nullable', 'string', 'max:150'],
            'direccion.nro' => ['nullable', 'string', 'max:20'],
            'direccion.referencia' => ['nullable', 'string', 'max:255'],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'codigo.unique' => 'Ya existe un proyecto con ese código.',
            'empresa_id.exists' => 'La empresa seleccionada no existe.',
            'tecnico_id.exists' => 'El técnico seleccionado no existe.',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser posterior o igual a la fecha de inicio.',
        ];
    }
}

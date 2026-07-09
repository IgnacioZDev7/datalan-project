<?php

namespace App\Http\Requests\Asignacion;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAsignacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'activo_id' => ['sometimes', 'required', 'integer', 'exists:activos,id'],
            'tecnico_id' => ['sometimes', 'required', 'integer', 'exists:tecnicos,id'],
            'proyecto_id' => ['nullable', 'integer', 'exists:proyectos,id'],
            'fecha_asignacion' => ['sometimes', 'required', 'date'],
            'fecha_devolucion' => ['nullable', 'date', 'after_or_equal:fecha_asignacion'],
            'estado' => ['sometimes', 'required', 'string', 'in:asignado,devuelto,perdido,danado'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'activo_id.exists' => 'El activo seleccionado no existe.',
            'tecnico_id.exists' => 'El técnico seleccionado no existe.',
            'proyecto_id.exists' => 'El proyecto seleccionado no existe.',
            'fecha_devolucion.after_or_equal' => 'La fecha de devolución debe ser posterior o igual a la fecha de asignación.',
            'estado.in' => 'El estado debe ser: asignado, devuelto, perdido o danado.',
        ];
    }
}

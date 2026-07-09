<?php

namespace App\Http\Requests\Activo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateActivoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('activo')?->id;

        return [
            'producto_id' => ['sometimes', 'required', 'integer', 'exists:productos,id'],
            'codigo_interno' => ['sometimes', 'required', 'string', 'max:60',
                Rule::unique('activos', 'codigo_interno')->ignore($id)->whereNull('deleted_at')],
            'nro_serie' => ['nullable', 'string', 'max:120',
                Rule::unique('activos', 'nro_serie')->ignore($id)->whereNull('deleted_at')],
            'mac' => ['nullable', 'string', 'max:30',
                Rule::unique('activos', 'mac')->ignore($id)->whereNull('deleted_at')],
            'estado' => ['sometimes', 'required', Rule::in(['nuevo', 'bueno', 'regular', 'danado', 'en_reparacion', 'baja'])],
            'situacion' => ['sometimes', 'required', Rule::in(['en_almacen', 'asignado', 'instalado', 'de_baja'])],
            'ubicacion_id' => ['nullable', 'integer', 'exists:ubicaciones,id'],
            'tecnico_id' => [Rule::requiredIf(fn () => $this->input('situacion') === 'asignado'),
                'nullable', 'integer', 'exists:tecnicos,id'],
            'activo_padre_id' => ['nullable', 'integer', 'exists:activos,id', 'different:id'],
            'fecha_fabricacion' => ['nullable', 'date'],
            'fecha_ingreso' => ['nullable', 'date'],
            'credenciales' => ['nullable', 'array'],
            'observaciones' => ['nullable', 'string', 'max:255'],
            // Motivo opcional del cambio de estado/situacion (se guarda en el historial).
            'motivo' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'codigo_interno.unique' => 'Ya existe un activo con ese código interno.',
            'nro_serie.unique' => 'Ese número de serie ya está registrado.',
            'mac.unique' => 'Esa MAC ya está registrada.',
            'estado.in' => 'El estado no es válido.',
            'situacion.in' => 'La situación no es válida.',
            'tecnico_id.required' => 'Si la situación es "asignado", el técnico es obligatorio.',
        ];
    }
}

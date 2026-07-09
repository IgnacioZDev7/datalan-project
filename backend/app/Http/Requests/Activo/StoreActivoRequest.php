<?php

namespace App\Http\Requests\Activo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreActivoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'producto_id' => ['required', 'integer', 'exists:productos,id'],
            'codigo_interno' => ['required', 'string', 'max:60',
                Rule::unique('activos', 'codigo_interno')->whereNull('deleted_at')],
            'nro_serie' => ['nullable', 'string', 'max:120',
                Rule::unique('activos', 'nro_serie')->whereNull('deleted_at')],
            'mac' => ['nullable', 'string', 'max:30',
                Rule::unique('activos', 'mac')->whereNull('deleted_at')],
            'estado' => ['required', Rule::in(['nuevo', 'bueno', 'regular', 'danado', 'en_reparacion', 'baja'])],
            'situacion' => ['required', Rule::in(['en_almacen', 'asignado', 'instalado', 'de_baja'])],
            'ubicacion_id' => ['nullable', 'integer', 'exists:ubicaciones,id'],
            // Regla de negocio: si situacion=asignado, el tecnico es obligatorio.
            'tecnico_id' => [Rule::requiredIf(fn () => $this->input('situacion') === 'asignado'),
                'nullable', 'integer', 'exists:tecnicos,id'],
            'activo_padre_id' => ['nullable', 'integer', 'exists:activos,id'],
            'fecha_fabricacion' => ['nullable', 'date'],
            'fecha_ingreso' => ['nullable', 'date'],
            'credenciales' => ['nullable', 'array'],
            'observaciones' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'producto_id.required' => 'El producto es obligatorio.',
            'producto_id.exists' => 'El producto no existe.',
            'codigo_interno.required' => 'El código interno es obligatorio.',
            'codigo_interno.unique' => 'Ya existe un activo con ese código interno.',
            'nro_serie.unique' => 'Ese número de serie ya está registrado.',
            'mac.unique' => 'Esa MAC ya está registrada.',
            'estado.in' => 'El estado no es válido.',
            'situacion.in' => 'La situación no es válida.',
            'tecnico_id.required' => 'Si la situación es "asignado", el técnico es obligatorio.',
            'tecnico_id.exists' => 'El técnico no existe.',
        ];
    }
}

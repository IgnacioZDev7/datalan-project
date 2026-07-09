<?php

namespace App\Http\Requests\Movimiento;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMovimientoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'codigo' => ['required', 'string', 'max:60',
                Rule::unique('movimientos', 'codigo')->whereNull('deleted_at')],
            'tipo' => ['required', Rule::in(['entrada', 'salida', 'devolucion', 'baja', 'traslado', 'ajuste'])],
            'fecha' => ['required', 'date'],

            // Almacenes segun el tipo de movimiento.
            'almacen_origen_id' => [
                Rule::requiredIf(fn () => in_array($this->input('tipo'), ['salida', 'baja', 'traslado'])),
                'nullable', 'integer', 'exists:almacenes,id',
            ],
            'almacen_destino_id' => [
                Rule::requiredIf(fn () => in_array($this->input('tipo'), ['entrada', 'devolucion', 'traslado'])),
                'nullable', 'integer', 'exists:almacenes,id',
            ],

            'proveedor_id' => ['nullable', 'integer', 'exists:proveedores,id'],
            'proyecto_id' => ['nullable', 'integer', 'exists:proyectos,id'],
            'tecnico_id' => ['nullable', 'integer', 'exists:tecnicos,id'],
            'inventario_fisico_id' => ['nullable', 'integer', 'exists:inventarios_fisicos,id'],
            'autorizado_por' => ['nullable', 'integer', 'exists:usuarios,id'],
            'documento_referencia' => ['nullable', 'string', 'max:120'],
            'observaciones' => ['nullable', 'string'],

            // Detalles (al menos uno).
            'detalles' => ['required', 'array', 'min:1'],
            'detalles.*.producto_id' => ['required', 'integer', 'exists:productos,id'],
            'detalles.*.activo_id' => ['nullable', 'integer', 'exists:activos,id'],
            'detalles.*.carrete_id' => ['nullable', 'integer', 'exists:carretes,id'],
            'detalles.*.cantidad' => ['nullable', 'numeric', 'min:0'],
            'detalles.*.metraje' => ['nullable', 'numeric', 'min:0'],
            'detalles.*.estado' => ['nullable', Rule::in(['nuevo', 'bueno', 'regular', 'danado'])],
            'detalles.*.observaciones' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'codigo.unique' => 'Ya existe un movimiento con ese código.',
            'tipo.in' => 'El tipo de movimiento no es válido.',
            'almacen_origen_id.required' => 'Este tipo de movimiento requiere almacén de origen.',
            'almacen_destino_id.required' => 'Este tipo de movimiento requiere almacén de destino.',
            'detalles.required' => 'El movimiento debe tener al menos un detalle.',
            'detalles.min' => 'El movimiento debe tener al menos un detalle.',
            'detalles.*.producto_id.required' => 'Cada detalle requiere un producto.',
        ];
    }
}

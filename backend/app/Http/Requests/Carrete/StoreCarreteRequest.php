<?php

namespace App\Http\Requests\Carrete;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCarreteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'producto_id' => ['required', 'integer', 'exists:productos,id'],
            'codigo' => ['required', 'string', 'max:60',
                Rule::unique('carretes', 'codigo')->whereNull('deleted_at')],
            'nro_hilos' => ['nullable', 'integer', 'min:1'],
            'tipo_fibra' => ['nullable', Rule::in(['MM', 'SM'])],
            'metraje_inicial' => ['required', 'numeric', 'min:0'],
            // Regla de negocio: el saldo no puede superar el metraje inicial.
            'metraje_disponible' => ['required', 'numeric', 'min:0', 'lte:metraje_inicial'],
            'estado' => ['required', Rule::in(['en_almacen', 'instalado', 'en_espera', 'inexistente'])],
            'ubicacion_id' => ['nullable', 'integer', 'exists:ubicaciones,id'],
            'carrete_padre_id' => ['nullable', 'integer', 'exists:carretes,id'],
            'fecha_ingreso' => ['nullable', 'date'],
            'observaciones' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'producto_id.exists' => 'El producto no existe.',
            'codigo.unique' => 'Ya existe un carrete con ese código.',
            'tipo_fibra.in' => 'El tipo de fibra debe ser MM o SM.',
            'metraje_disponible.lte' => 'El metraje disponible no puede superar el metraje inicial.',
            'estado.in' => 'El estado del carrete no es válido.',
        ];
    }
}

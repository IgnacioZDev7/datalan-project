<?php

namespace App\Exports;

use App\Models\Existencia;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExistenciasExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function __construct(
        private ?int $almacenId = null,
        private bool $bajoMinimo = false
    ) {}

    public function query()
    {
        $query = Existencia::query()->with(['producto', 'almacen']);
        if ($this->almacenId) $query->where('almacen_id', $this->almacenId);
        if ($this->bajoMinimo) $query->whereColumn('cantidad_actual', '<=', 'cantidad_minima');
        return $query->orderBy('almacen_id')->orderBy('producto_id');
    }

    public function headings(): array
    {
        return ['Almacén', 'Código Producto', 'Producto', 'Cantidad Actual', 'Cantidad Mínima', 'Cantidad Máxima'];
    }

    public function map($e): array
    {
        return [
            $e->almacen?->nombre ?? '-',
            $e->producto?->codigo ?? '-',
            $e->producto?->nombre ?? '-',
            (float) $e->cantidad_actual,
            (float) ($e->cantidad_minima ?? 0),
            (float) ($e->cantidad_maxima ?? 0),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}

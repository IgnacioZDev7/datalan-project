<?php

namespace App\Exports;

use App\Models\Movimiento;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MovimientosExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function __construct(
        private ?string $desde = null,
        private ?string $hasta = null,
        private ?string $tipo = null
    ) {}

    public function query()
    {
        $query = Movimiento::query()->with('detalles');
        if ($this->tipo) $query->where('tipo', $this->tipo);
        if ($this->desde) $query->whereDate('fecha', '>=', $this->desde);
        if ($this->hasta) $query->whereDate('fecha', '<=', $this->hasta);
        return $query->orderByDesc('fecha');
    }

    public function headings(): array
    {
        return ['Código', 'Tipo', 'Fecha', 'Origen', 'Destino', 'Documento', 'Observaciones'];
    }

    public function map($m): array
    {
        return [
            $m->codigo,
            $m->tipo,
            $m->fecha,
            $m->almacen_origen_id ?? '-',
            $m->almacen_destino_id ?? '-',
            $m->documento_referencia ?? '-',
            $m->observaciones ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}

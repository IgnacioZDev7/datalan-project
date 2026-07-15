<?php

namespace App\Console\Commands;

use App\Models\Alerta;
use App\Models\Existencia;
use Illuminate\Console\Command;

class GenerarAlertasStock extends Command
{
    protected $signature = 'alertas:generar';
    protected $description = 'Generar alertas de stock mínimo para existencias por debajo del mínimo';

    public function handle(): int
    {
        $existencias = Existencia::whereColumn('cantidad_actual', '<=', 'cantidad_minima')
            ->where('cantidad_minima', '>', 0)
            ->with('producto')
            ->get();

        $creadas = 0;

        foreach ($existencias as $existencia) {
            $productoId = $existencia->producto_id;

            // Evitar duplicados: no crear si ya hay una alerta no leída para este producto
            $yaExiste = Alerta::where('tipo', 'stock_minimo')
                ->where('producto_id', $productoId)
                ->where('leida', false)
                ->exists();

            if ($yaExiste) {
                continue;
            }

            $nombreProducto = $existencia->producto?->nombre ?? "Producto #{$productoId}";

            Alerta::create([
                'tipo'        => 'stock_minimo',
                'nivel'       => 'advertencia',
                'mensaje'     => "Stock bajo mínimo: {$nombreProducto} ({$existencia->cantidad_actual})",
                'producto_id' => $productoId,
                'leida'       => false,
            ]);

            $creadas++;
        }

        $this->info("Se crearon {$creadas} alerta(s) de stock mínimo.");
        return Command::SUCCESS;
    }
}

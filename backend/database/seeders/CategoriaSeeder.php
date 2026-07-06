<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        // Estructura: padre (con tipo_inventario) -> hijos (heredan el tipo).
        $arbol = [
            'consumible' => [
                'Consumibles' => ['Conectores', 'Tornilleria y fijacion', 'Abrazaderas', 'Patch Cords', 'Grapas', 'Accesorios varios'],
            ],
            'cable' => [
                'Cable de Fibra Optica' => ['Cable SM (monomodo)', 'Cable MM (multimodo)'],
            ],
            'activo' => [
                'Equipos Activos' => ['Routers', 'Switches', 'ONT / ONU', 'OLT', 'Media Converters', 'Modulos SFP'],
            ],
            'herramienta' => [
                'Herramientas' => ['Fusionadoras', 'OTDR / Medidores', 'Herramientas manuales'],
            ],
        ];

        foreach ($arbol as $tipo => $padres) {
            foreach ($padres as $nombrePadre => $hijos) {
                $padre = Categoria::firstOrCreate(
                    ['nombre' => $nombrePadre],
                    ['tipo_inventario' => $tipo, 'activo' => true]
                );

                foreach ($hijos as $nombreHijo) {
                    Categoria::firstOrCreate(
                        ['nombre' => $nombreHijo],
                        ['tipo_inventario' => $tipo, 'categoria_padre_id' => $padre->id, 'activo' => true]
                    );
                }
            }
        }
    }
}

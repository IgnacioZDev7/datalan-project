<?php

namespace Database\Seeders;

use App\Models\UnidadMedida;
use Illuminate\Database\Seeder;

class UnidadMedidaSeeder extends Seeder
{
    public function run(): void
    {
        $unidades = [
            ['nombre' => 'Metro', 'abreviatura' => 'm'],
            ['nombre' => 'Unidad', 'abreviatura' => 'u'],
            ['nombre' => 'Caja', 'abreviatura' => 'caja'],
            ['nombre' => 'Bolsa', 'abreviatura' => 'bolsa'],
            ['nombre' => 'Par', 'abreviatura' => 'par'],
            ['nombre' => 'Rollo', 'abreviatura' => 'rollo'],
            ['nombre' => 'Pieza', 'abreviatura' => 'pza'],
        ];

        foreach ($unidades as $u) {
            UnidadMedida::firstOrCreate(['abreviatura' => $u['abreviatura']], $u);
        }
    }
}

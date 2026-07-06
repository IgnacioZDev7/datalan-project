<?php

namespace Database\Seeders;

use App\Models\Almacen;
use App\Models\Direccion;
use App\Models\Ubicacion;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class AlmacenSeeder extends Seeder
{
    public function run(): void
    {
        $responsable = Usuario::where('correo_electronico', 'almacen@datalan.bo')->first();

        // Direccion conocida (oficina central segun documentacion).
        $dirCentral = Direccion::firstOrCreate([
            'calle' => 'Av. Camacho',
            'nro' => '1277',
        ], [
            'zona' => 'Central',
            'ciudad' => 'La Paz',
            'referencia' => 'Edificio Krsul, Piso 3, Oficina 311',
        ]);

        $central = Almacen::firstOrCreate(
            ['nombre' => 'Almacen Central'],
            ['responsable_id' => $responsable?->id, 'direccion_id' => $dirCentral->id, 'activo' => true]
        );

        // Segunda sucursal (direccion pendiente de relevar).
        $secundario = Almacen::firstOrCreate(
            ['nombre' => 'Almacen Secundario'],
            ['responsable_id' => $responsable?->id, 'activo' => true]
        );

        // Ubicacion GENERAL por defecto en cada almacen (convencion del modelo).
        foreach ([$central, $secundario] as $almacen) {
            Ubicacion::firstOrCreate(
                ['almacen_id' => $almacen->id, 'codigo' => 'GENERAL'],
                ['descripcion' => 'Ubicacion por defecto (sin posicion especifica)']
            );
        }
    }
}

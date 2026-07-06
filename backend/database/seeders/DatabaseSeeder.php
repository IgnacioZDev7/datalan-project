<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Datos base del sistema. Orden importante por dependencias.
     */
    public function run(): void
    {
        $this->call([
            RolePermisoSeeder::class,   // roles y permisos (Spatie)
            UsuarioSeeder::class,       // usuarios + asignacion de roles
            UnidadMedidaSeeder::class,  // unidades de medida
            CategoriaSeeder::class,     // categorias base (por tipo_inventario)
            AlmacenSeeder::class,       // 2 almacenes + ubicacion GENERAL + direcciones
        ]);
    }
}

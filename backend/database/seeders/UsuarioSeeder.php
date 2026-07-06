<?php

namespace Database\Seeders;

use App\Models\Usuario;
use Illuminate\Database\Seeder;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        // Los 3 usuarios reales del sistema (segun relevamiento).
        // Contraseña por defecto: "password" (cambiar en el primer ingreso).

        $gerente = Usuario::firstOrCreate(
            ['correo_electronico' => 'gerente@datalan.bo'],
            [
                'nombres' => 'Gerente',
                'apellido_paterno' => 'General',
                'contrasena' => 'password',
                'cargo' => 'Gerente General',
                'activo' => true,
            ]
        );
        $gerente->assignRole('gerente');

        $almacen = Usuario::firstOrCreate(
            ['correo_electronico' => 'almacen@datalan.bo'],
            [
                'nombres' => 'Encargado',
                'apellido_paterno' => 'Almacen',
                'contrasena' => 'password',
                'cargo' => 'Encargado de Almacen',
                'activo' => true,
            ]
        );
        $almacen->assignRole('encargado_almacen');

        $tecnico = Usuario::firstOrCreate(
            ['correo_electronico' => 'tecnico@datalan.bo'],
            [
                'nombres' => 'Jefe',
                'apellido_paterno' => 'Tecnico',
                'contrasena' => 'password',
                'cargo' => 'Jefe Tecnico 1',
                'activo' => true,
            ]
        );
        $tecnico->assignRole('jefe_tecnico');
    }
}

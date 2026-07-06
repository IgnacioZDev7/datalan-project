<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermisoSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // --- Permisos CRUD por modulo ---
        $modulos = [
            'usuarios', 'roles', 'categorias', 'marcas', 'modelos', 'unidades',
            'productos', 'almacenes', 'ubicaciones', 'existencias', 'activos',
            'carretes', 'movimientos', 'proyectos', 'empresas', 'proveedores',
            'tecnicos', 'inventarios_fisicos', 'asignaciones', 'alertas',
        ];
        $acciones = ['ver', 'crear', 'editar', 'eliminar'];

        foreach ($modulos as $m) {
            foreach ($acciones as $a) {
                Permission::firstOrCreate(['name' => "$m.$a"]);
            }
        }

        // --- Permisos especiales ---
        $especiales = [
            'movimientos.aprobar', 'movimientos.anular',
            'reportes.ver', 'reportes.exportar', 'bitacora.ver',
        ];
        foreach ($especiales as $p) {
            Permission::firstOrCreate(['name' => $p]);
        }

        // --- Roles ---
        // Gerente: acceso total.
        $gerente = Role::firstOrCreate(['name' => 'gerente']);
        $gerente->syncPermissions(Permission::all());

        // Encargado de almacen: todo el inventario/catalogo, sin gestionar usuarios/roles.
        $encargado = Role::firstOrCreate(['name' => 'encargado_almacen']);
        $encargado->syncPermissions(
            Permission::whereNot(fn ($q) => $q->where('name', 'like', 'usuarios.%')
                ->orWhere('name', 'like', 'roles.%'))->get()
        );

        // Jefe tecnico: activos, herramientas, movimientos, proyectos, asignaciones + lectura.
        $jefe = Role::firstOrCreate(['name' => 'jefe_tecnico']);
        $permisosJefe = collect();
        foreach (['activos', 'carretes', 'movimientos', 'proyectos', 'tecnicos', 'asignaciones'] as $m) {
            $permisosJefe = $permisosJefe->merge(
                Permission::where('name', 'like', "$m.%")->pluck('name')
            );
        }
        // Lectura general de catalogo/existencias + reportes.
        $permisosJefe = $permisosJefe->merge([
            'productos.ver', 'categorias.ver', 'marcas.ver', 'modelos.ver',
            'almacenes.ver', 'ubicaciones.ver', 'existencias.ver',
            'inventarios_fisicos.ver', 'alertas.ver', 'reportes.ver',
        ])->unique()->values();
        $jefe->syncPermissions($permisosJefe->all());

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}

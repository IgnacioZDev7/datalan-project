<?php

namespace Database\Seeders;

use App\Models\Activo;
use App\Models\Almacen;
use App\Models\Carrete;
use App\Models\Categoria;
use App\Models\Direccion;
use App\Models\Empresa;
use App\Models\Existencia;
use App\Models\Marca;
use App\Models\Modelo;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Tecnico;
use App\Models\Ubicacion;
use App\Models\UnidadMedida;
use Illuminate\Database\Seeder;

class DatosRealesSeeder extends Seeder
{
    public function run(): void
    {
        // ──── MARCAS ────
        $marcas = [
            'DROOP', 'BIRLA ERICSSON', 'FURUKAWA', 'TELCOM', 'NINGBO',
            'SURLINK', 'SUMITOMO', 'PLANET', 'Huawei', 'Mikrotik',
            'Ubiquiti', 'Fujikura', 'Comway',
        ];
        $marcaModelos = [];
        foreach ($marcas as $nombre) {
            $marcaModelos[$nombre] = Marca::firstOrCreate(['nombre' => $nombre], ['activo' => true])->id;
        }

        // ──── MODELOS ────
        $modelosData = [
            ['marca' => 'Fujikura', 'nombre' => 'FSM-60S'],
            ['marca' => 'Fujikura', 'nombre' => 'FSM-80S'],
            ['marca' => 'Fujikura', 'nombre' => 'FSM-50S'],
            ['marca' => 'Comway', 'nombre' => 'C10'],
            ['marca' => 'SUMITOMO', 'nombre' => 'TYPE-81C'],
            ['marca' => 'PLANET', 'nombre' => 'Media Converter GP-100'],
            ['marca' => 'PLANET', 'nombre' => 'Switch GS-1200'],
            ['marca' => 'PLANET', 'nombre' => 'Switch ISW-500'],
            ['marca' => 'Huawei', 'nombre' => 'ONT HG8245H'],
            ['marca' => 'Huawei', 'nombre' => 'ONT HG8010H'],
            ['marca' => 'Huawei', 'nombre' => 'Módulo SFP GPON'],
            ['marca' => 'Mikrotik', 'nombre' => 'RB750Gr3 (hEX)'],
            ['marca' => 'Mikrotik', 'nombre' => 'SXT LTE Kit'],
            ['marca' => 'Ubiquiti', 'nombre' => 'EdgeRouter X'],
            ['marca' => 'Ubiquiti', 'nombre' => 'NanoStation M5'],
            ['marca' => 'DROOP', 'nombre' => 'Cable SM 2 hilos 2000m'],
            ['marca' => 'DROOP', 'nombre' => 'Cable SM 4 hilos'],
            ['marca' => 'BIRLA ERICSSON', 'nombre' => 'Cable SM 4 hilos'],
            ['marca' => 'BIRLA ERICSSON', 'nombre' => 'Cable SM 2 hilos'],
            ['marca' => 'FURUKAWA', 'nombre' => 'Cable SM 2 hilos'],
            ['marca' => 'TELCOM', 'nombre' => 'Conector LC'],
            ['marca' => 'TELCOM', 'nombre' => 'Conector SC'],
            ['marca' => 'NINGBO', 'nombre' => 'Patch Cord LC-LC 3m'],
            ['marca' => 'NINGBO', 'nombre' => 'Patch Cord SC-SC 3m'],
            ['marca' => 'SURLINK', 'nombre' => 'Módulo SFP 1.25G'],
            ['marca' => 'SURLINK', 'nombre' => 'MGB-LA20'],
            ['marca' => 'PLANET', 'nombre' => 'GPN-100'],
        ];
        $modeloIds = [];
        foreach ($modelosData as $m) {
            $modelo = Modelo::firstOrCreate(
                ['marca_id' => $marcaModelos[$m['marca']], 'nombre' => $m['nombre']]
            );
            $modeloIds[$m['nombre']] = $modelo->id;
        }

        // ──── UNIDADES (referencia) ────
        $unidad = fn(string $abrev): int => UnidadMedida::where('abreviatura', $abrev)->first()->id;
        $u = [
            'm' => $unidad('m'),
            'u' => $unidad('u'),
            'caja' => $unidad('caja'),
            'bolsa' => $unidad('bolsa'),
            'par' => $unidad('par'),
            'pza' => $unidad('pza'),
        ];

        // ──── CATEGORIAS (referencia) ────
        $catId = fn(string $nombre): int => Categoria::where('nombre', $nombre)->first()->id;

        // ──── PRODUCTOS ────
        $productosData = [
            // Consumibles
            ['codigo' => 'P-CON-LC', 'nombre' => 'Conector LC', 'categoria' => 'Conectores', 'unidad' => 'u', 'modelo' => null],
            ['codigo' => 'P-CON-SC', 'nombre' => 'Conector SC', 'categoria' => 'Conectores', 'unidad' => 'u', 'modelo' => null],
            ['codigo' => 'P-PC-LC3', 'nombre' => 'Patch Cord LC-LC 3m', 'categoria' => 'Patch Cords', 'unidad' => 'u', 'modelo' => 'Patch Cord LC-LC 3m'],
            ['codigo' => 'P-PC-SC3', 'nombre' => 'Patch Cord SC-SC 3m', 'categoria' => 'Patch Cords', 'unidad' => 'u', 'modelo' => 'Patch Cord SC-SC 3m'],
            ['codigo' => 'P-TRN10', 'nombre' => 'Tornillo tirafondo N°10', 'categoria' => 'Tornilleria y fijacion', 'unidad' => 'bolsa', 'modelo' => null],
            ['codigo' => 'P-TRN8', 'nombre' => 'Tornillo tirafondo N°8', 'categoria' => 'Tornilleria y fijacion', 'unidad' => 'bolsa', 'modelo' => null],
            ['codigo' => 'P-RMP10', 'nombre' => 'Ramplus N°10', 'categoria' => 'Tornilleria y fijacion', 'unidad' => 'bolsa', 'modelo' => null],
            ['codigo' => 'P-RMP8', 'nombre' => 'Ramplus N°8', 'categoria' => 'Tornilleria y fijacion', 'unidad' => 'bolsa', 'modelo' => null],
            ['codigo' => 'P-ABZ', 'nombre' => 'Abrazadera metálica 1/2"', 'categoria' => 'Abrazaderas', 'unidad' => 'u', 'modelo' => null],
            ['codigo' => 'P-GRP', 'nombre' => 'Grapa para coaxial', 'categoria' => 'Grapas', 'unidad' => 'bolsa', 'modelo' => null],
            ['codigo' => 'P-ACC', 'nombre' => 'Kit accesorios varios', 'categoria' => 'Accesorios varios', 'unidad' => 'caja', 'modelo' => null],
            // Cable
            ['codigo' => 'P-CAB-SM2', 'nombre' => 'Cable SM 2 hilos', 'categoria' => 'Cable SM (monomodo)', 'unidad' => 'm', 'modelo' => 'Cable SM 2 hilos 2000m'],
            ['codigo' => 'P-CAB-SM4', 'nombre' => 'Cable SM 4 hilos', 'categoria' => 'Cable SM (monomodo)', 'unidad' => 'm', 'modelo' => null],
            ['codigo' => 'P-CAB-MM', 'nombre' => 'Cable MM 2 hilos', 'categoria' => 'Cable MM (multimodo)', 'unidad' => 'm', 'modelo' => null],
            // Activos
            ['codigo' => 'P-ONT-8245', 'nombre' => 'ONT Huawei HG8245H', 'categoria' => 'ONT / ONU', 'unidad' => 'u', 'modelo' => 'ONT HG8245H'],
            ['codigo' => 'P-ONT-8010', 'nombre' => 'ONT Huawei HG8010H', 'categoria' => 'ONT / ONU', 'unidad' => 'u', 'modelo' => 'ONT HG8010H'],
            ['codigo' => 'P-MC-GP100', 'nombre' => 'Media Converter PLANET GP-100', 'categoria' => 'Media Converters', 'unidad' => 'u', 'modelo' => 'Media Converter GP-100'],
            ['codigo' => 'P-SW-GS1200', 'nombre' => 'Switch PLANET GS-1200', 'categoria' => 'Switches', 'unidad' => 'u', 'modelo' => 'Switch GS-1200'],
            ['codigo' => 'P-SW-ISW500', 'nombre' => 'Switch PLANET ISW-500', 'categoria' => 'Switches', 'unidad' => 'u', 'modelo' => 'Switch ISW-500'],
            ['codigo' => 'P-SFP-GPON', 'nombre' => 'Módulo SFP GPON Huawei', 'categoria' => 'Modulos SFP', 'unidad' => 'u', 'modelo' => 'Módulo SFP GPON'],
            ['codigo' => 'P-SFP-125G', 'nombre' => 'Módulo SFP 1.25G SURLINK', 'categoria' => 'Modulos SFP', 'unidad' => 'u', 'modelo' => 'Módulo SFP 1.25G'],
            ['codigo' => 'P-RB-HX', 'nombre' => 'Router Mikrotik hEX RB750Gr3', 'categoria' => 'Routers', 'unidad' => 'u', 'modelo' => 'RB750Gr3 (hEX)'],
            ['codigo' => 'P-RB-SXT', 'nombre' => 'Mikrotik SXT LTE Kit', 'categoria' => 'Routers', 'unidad' => 'u', 'modelo' => 'SXT LTE Kit'],
            ['codigo' => 'P-ERX', 'nombre' => 'EdgeRouter X Ubiquiti', 'categoria' => 'Routers', 'unidad' => 'u', 'modelo' => 'EdgeRouter X'],
            // Herramientas
            ['codigo' => 'P-FSM60S', 'nombre' => 'Fusionadora Fujikura FSM-60S', 'categoria' => 'Fusionadoras', 'unidad' => 'u', 'modelo' => 'FSM-60S'],
            ['codigo' => 'P-FSM80S', 'nombre' => 'Fusionadora Fujikura FSM-80S', 'categoria' => 'Fusionadoras', 'unidad' => 'u', 'modelo' => 'FSM-80S'],
            ['codigo' => 'P-FSM50S', 'nombre' => 'Fusionadora Fujikura FSM-50S', 'categoria' => 'Fusionadoras', 'unidad' => 'u', 'modelo' => 'FSM-50S'],
            ['codigo' => 'P-C10', 'nombre' => 'Fusionadora Comway C10', 'categoria' => 'Fusionadoras', 'unidad' => 'u', 'modelo' => 'C10'],
            ['codigo' => 'P-T81C', 'nombre' => 'Fusionadora Sumitomo TYPE-81C', 'categoria' => 'Fusionadoras', 'unidad' => 'u', 'modelo' => 'TYPE-81C'],
        ];

        $productoIds = [];
        foreach ($productosData as $p) {
            $prod = Producto::firstOrCreate(
                ['codigo' => $p['codigo']],
                [
                    'nombre' => $p['nombre'],
                    'categoria_id' => $catId($p['categoria']),
                    'modelo_id' => $p['modelo'] ? ($modeloIds[$p['modelo']] ?? null) : null,
                    'unidad_id' => $u[$p['unidad']],
                    'activo' => true,
                ]
            );
            $productoIds[$p['codigo']] = $prod->id;
        }

        // ──── EMPRESAS ────
        $empresasData = [
            ['nombre' => 'BTV Oruro', 'nit' => '102483021', 'direccion' => ['calle' => 'Av. 6 de Octubre', 'nro' => '500', 'zona' => 'Central', 'ciudad' => 'Oruro']],
            ['nombre' => 'SEGIP Sucre', 'nit' => '102948302', 'direccion' => ['calle' => 'Calle Bolívar', 'nro' => '300', 'zona' => 'Centro', 'ciudad' => 'Sucre']],
            ['nombre' => 'TSE Achumani', 'nit' => '103847201', 'direccion' => ['calle' => 'Av. Costanera', 'nro' => '100', 'zona' => 'Achumani', 'ciudad' => 'La Paz']],
        ];
        foreach ($empresasData as $e) {
            $dir = Direccion::firstOrCreate(
                ['calle' => $e['direccion']['calle'], 'nro' => $e['direccion']['nro']],
                $e['direccion']
            );
            Empresa::firstOrCreate(
                ['nombre' => $e['nombre']],
                ['nit' => $e['nit'], 'direccion_id' => $dir->id, 'activo' => true]
            );
        }

        // ──── PROVEEDORES ────
        $proveedoresData = [
            ['nombre' => 'Distribuidora Telcom Bolivia', 'nit' => '201839201'],
            ['nombre' => 'Importadora Ningbo Ltda.', 'nit' => '203847102'],
            ['nombre' => 'SurLink Telecom SRL', 'nit' => '204958301'],
            ['nombre' => 'Huawei Technologies Bolivia', 'nit' => '205738201'],
            ['nombre' => 'Mikrotik Bolivia', 'nit' => '206839201'],
        ];
        foreach ($proveedoresData as $p) {
            Proveedor::firstOrCreate(
                ['nombre' => $p['nombre']],
                ['nit' => $p['nit'], 'activo' => true]
            );
        }

        // ──── TECNICOS ────
        $tecnicos = [
            ['nombre' => 'Carlos Mamani', 'ci' => '4839201', 'cargo' => 'Técnico Fibra Óptica'],
            ['nombre' => 'Juan Pérez', 'ci' => '5938201', 'cargo' => 'Técnico Instalador'],
            ['nombre' => 'Luis Quispe', 'ci' => '6829301', 'cargo' => 'Técnico Senior'],
        ];
        foreach ($tecnicos as $t) {
            Tecnico::firstOrCreate(
                ['nombre' => $t['nombre']],
                ['ci' => $t['ci'], 'cargo' => $t['cargo'], 'activo' => true]
            );
        }

        // ──── CARRETES ────
        $ubicacionGeneral = Ubicacion::where('codigo', 'GENERAL')->first();
        $ubicacionId = $ubicacionGeneral?->id;
        $almacenCentral = Almacen::where('nombre', 'Almacen Central')->first();
        $almacenId = $almacenCentral?->id;

        $carretesData = [
            ['codigo' => 'CR-001', 'producto' => 'P-CAB-SM2', 'nro_hilos' => 2, 'tipo_fibra' => 'SM', 'metraje_inicial' => 2000, 'metraje_disponible' => 1850],
            ['codigo' => 'CR-002', 'producto' => 'P-CAB-SM2', 'nro_hilos' => 2, 'tipo_fibra' => 'SM', 'metraje_inicial' => 2000, 'metraje_disponible' => 2000],
            ['codigo' => 'CR-003', 'producto' => 'P-CAB-SM4', 'nro_hilos' => 4, 'tipo_fibra' => 'SM', 'metraje_inicial' => 2000, 'metraje_disponible' => 1980],
            ['codigo' => 'CR-004', 'producto' => 'P-CAB-SM4', 'nro_hilos' => 4, 'tipo_fibra' => 'SM', 'metraje_inicial' => 1000, 'metraje_disponible' => 1000],
            ['codigo' => 'CR-005', 'producto' => 'P-CAB-MM', 'nro_hilos' => 2, 'tipo_fibra' => 'MM', 'metraje_inicial' => 500, 'metraje_disponible' => 450],
        ];
        foreach ($carretesData as $c) {
            Carrete::firstOrCreate(
                ['codigo' => $c['codigo']],
                [
                    'producto_id' => $productoIds[$c['producto']],
                    'nro_hilos' => $c['nro_hilos'],
                    'tipo_fibra' => $c['tipo_fibra'],
                    'metraje_inicial' => $c['metraje_inicial'],
                    'metraje_disponible' => $c['metraje_disponible'],
                    'estado' => 'en_almacen',
                    'ubicacion_id' => $ubicacionId,
                ]
            );
        }

        // ──── ACTIVOS ────
        $ubicacionAlmacenId = Ubicacion::where('codigo', 'GENERAL')
            ->where('almacen_id', $almacenId)->first()->id ?? $ubicacionId;

        $activosData = [
            ['codigo_interno' => 'ACT-FSM60S-001', 'nro_serie' => 'FSM60S-2023-A001', 'producto' => 'P-FSM60S', 'estado' => 'bueno', 'situacion' => 'en_almacen'],
            ['codigo_interno' => 'ACT-FSM80S-001', 'nro_serie' => 'PX7DRA1234', 'producto' => 'P-FSM80S', 'estado' => 'bueno', 'situacion' => 'en_almacen', 'observaciones' => 'Batería dañada'],
            ['codigo_interno' => 'ACT-C10-001', 'nro_serie' => '1040330-2023', 'producto' => 'P-C10', 'estado' => 'regular', 'situacion' => 'en_almacen'],
            ['codigo_interno' => 'ACT-T81C-001', 'nro_serie' => 'T81C-2022-B045', 'producto' => 'P-T81C', 'estado' => 'bueno', 'situacion' => 'en_almacen'],
            ['codigo_interno' => 'ACT-ONT-001', 'nro_serie' => 'HG8245H-2024-001', 'producto' => 'P-ONT-8245', 'estado' => 'nuevo', 'situacion' => 'en_almacen'],
            ['codigo_interno' => 'ACT-ONT-002', 'nro_serie' => 'HG8245H-2024-002', 'producto' => 'P-ONT-8245', 'estado' => 'nuevo', 'situacion' => 'en_almacen'],
            ['codigo_interno' => 'ACT-ONT-003', 'nro_serie' => 'HG8010H-2024-003', 'producto' => 'P-ONT-8010', 'estado' => 'nuevo', 'situacion' => 'en_almacen'],
            ['codigo_interno' => 'ACT-MC-001', 'nro_serie' => 'GP100-2024-001', 'producto' => 'P-MC-GP100', 'estado' => 'nuevo', 'situacion' => 'en_almacen'],
            ['codigo_interno' => 'ACT-SFP-001', 'nro_serie' => 'GPON-SFP-001', 'producto' => 'P-SFP-GPON', 'estado' => 'nuevo', 'situacion' => 'en_almacen'],
            ['codigo_interno' => 'ACT-RB-001', 'nro_serie' => 'RB750-2024-001', 'producto' => 'P-RB-HX', 'estado' => 'nuevo', 'situacion' => 'en_almacen'],
        ];
        foreach ($activosData as $a) {
            Activo::firstOrCreate(
                ['codigo_interno' => $a['codigo_interno']],
                [
                    'producto_id' => $productoIds[$a['producto']],
                    'nro_serie' => $a['nro_serie'],
                    'estado' => $a['estado'],
                    'situacion' => $a['situacion'],
                    'ubicacion_id' => $ubicacionAlmacenId,
                    'observaciones' => $a['observaciones'] ?? null,
                ]
            );
        }

        // ──── EXISTENCIAS (stock inicial directo) ────
        $existenciasData = [
            ['producto' => 'P-CON-LC', 'almacen' => $almacenId, 'cantidad_actual' => 500, 'cantidad_minima' => 50],
            ['producto' => 'P-CON-SC', 'almacen' => $almacenId, 'cantidad_actual' => 300, 'cantidad_minima' => 30],
            ['producto' => 'P-PC-LC3', 'almacen' => $almacenId, 'cantidad_actual' => 100, 'cantidad_minima' => 20],
            ['producto' => 'P-PC-SC3', 'almacen' => $almacenId, 'cantidad_actual' => 80, 'cantidad_minima' => 20],
            ['producto' => 'P-TRN10', 'almacen' => $almacenId, 'cantidad_actual' => 10, 'cantidad_minima' => 5],
            ['producto' => 'P-TRN8', 'almacen' => $almacenId, 'cantidad_actual' => 15, 'cantidad_minima' => 5],
            ['producto' => 'P-RMP10', 'almacen' => $almacenId, 'cantidad_actual' => 8, 'cantidad_minima' => 3],
            ['producto' => 'P-RMP8', 'almacen' => $almacenId, 'cantidad_actual' => 12, 'cantidad_minima' => 3],
            ['producto' => 'P-ABZ', 'almacen' => $almacenId, 'cantidad_actual' => 200, 'cantidad_minima' => 50],
            ['producto' => 'P-GRP', 'almacen' => $almacenId, 'cantidad_actual' => 20, 'cantidad_minima' => 5],
            ['producto' => 'P-ACC', 'almacen' => $almacenId, 'cantidad_actual' => 5, 'cantidad_minima' => 2],
            ['producto' => 'P-ONT-8245', 'almacen' => $almacenId, 'cantidad_actual' => 15, 'cantidad_minima' => 5],
            ['producto' => 'P-ONT-8010', 'almacen' => $almacenId, 'cantidad_actual' => 10, 'cantidad_minima' => 3],
            ['producto' => 'P-MC-GP100', 'almacen' => $almacenId, 'cantidad_actual' => 8, 'cantidad_minima' => 2],
            ['producto' => 'P-SW-GS1200', 'almacen' => $almacenId, 'cantidad_actual' => 5, 'cantidad_minima' => 2],
            ['producto' => 'P-SFP-GPON', 'almacen' => $almacenId, 'cantidad_actual' => 20, 'cantidad_minima' => 5],
            ['producto' => 'P-SFP-125G', 'almacen' => $almacenId, 'cantidad_actual' => 15, 'cantidad_minima' => 5],
            ['producto' => 'P-RB-HX', 'almacen' => $almacenId, 'cantidad_actual' => 6, 'cantidad_minima' => 2],
        ];
        foreach ($existenciasData as $e) {
            Existencia::firstOrCreate(
                ['producto_id' => $productoIds[$e['producto']], 'almacen_id' => $e['almacen']],
                ['cantidad_actual' => $e['cantidad_actual'], 'cantidad_minima' => $e['cantidad_minima']]
            );
        }
    }
}

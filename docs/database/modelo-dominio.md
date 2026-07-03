# Modelo de Dominio — Sistema de Inventario DATALAN

> Fuente para el diagrama `datalan.dbml` (dbdiagram.io) y, posteriormente, las
> migraciones de Laravel. Basado en `analisis-imgs.txt`, el relevamiento
> (`PROYECTO DATALAN_SISTEMA.docx`), el documento universitario
> (`INVENTARIO-DATALAN.pdf`) y las fotografías del inventario real (`docs/guias`).

## Regla de oro

El inventario se basa en **movimientos**, no en stock:

```
stock = entradas − salidas + devoluciones
```

La cantidad **nunca** se edita directamente. El stock por cantidad se cachea en
`stocks` y se recalcula mediante un *observer* de Eloquent sobre
`movimiento_detalles`.

## Inventario híbrido: 3 formas de manejar un producto

Cada `producto` tiene un `tipo_manejo` que determina dónde vive su existencia física:

| `tipo_manejo` | Ejemplos | Existencia física en | Se controla por |
|---|---|---|---|
| `cantidad` | tornillos, conectores, abrazaderas, grapas, patch cord | `stocks` | cantidad |
| `metraje` | cable de fibra (carretes SM/MM) | `carretes` | metros disponibles |
| `serie` | routers, ONT, OLT / fusionadora, OTDR | `activos` | unidad individual |

La **categoría** clasifica el producto (`tipo_inventario`: consumible / cable /
activo / herramienta). Un `activo` puede ser un equipo de red o una herramienta;
la diferencia es que la herramienta se **asigna a un técnico** (`asignaciones`).

## Bloques del modelo

### 1. Seguridad
`users`, `roles`, `permisos` + pivotes (`role_user`, `permiso_role`), y `bitacora`
(auditoría / historial de acciones por usuario). Roles dinámicos. Usuarios se dan
de baja con el flag `activo` (no se borran). Solo ~3 usuarios reales, pero el
esquema de roles/permisos permite crecer.
> Sugerencia de implementación: `spatie/laravel-permission` para roles/permisos
> y `owen-it/laravel-auditing` para la bitácora.

### 2. Catálogo
`categorias` (jerárquicas), `marcas`, `modelos` (por marca), `unidades_medida` y
`productos`. Los atributos variables por categoría (velocidad, capacidad, tipo de
transmisión — "muy infinitos" según el cliente) se guardan en el JSON
`productos.especificaciones`. Los datos fijos del cable viven en `carretes`; los
de equipos, en `activos`.

### 3. Inventario (físico)
- `almacenes` (varios) y `ubicaciones` (estante/sección dentro del almacén).
- `stocks`: existencia por cantidad (cache derivado).
- `activos`: una fila por unidad física, con `nro_serie`, `mac`, `estado`,
  `codigo_interno` (QR/código de barras). Cubre equipos y herramientas.
- `carretes`: cable con saldo propio (`metraje_inicial` → `metraje_disponible`),
  `nro_hilos`, `tipo_fibra` (MM/SM), `estado` (en_almacen/instalado/en_espera).
- `movimientos` + `movimiento_detalles`: fuente de verdad. Tipos: entrada, salida,
  devolución, baja, traslado, ajuste. Una línea de detalle referencia como máximo
  un `activo_id` **o** un `carrete_id` (con `metraje` consumido) según el producto.

### 4. Operación
- `empresas`: clientes destino (BTV Oruro, SEGIP Sucre…).
- `proveedores`: origen de las entradas.
- `tecnicos`: personal de campo (puede o no tener `user_id`).
- `proyectos`: instalaciones (empresa + dirección + técnico responsable + estado).
- `asignaciones`: historial de custodia de herramientas por técnico.

### 5. Alertas
`alertas` para stock mínimo, mantenimiento y devoluciones pendientes.

## Decisiones de diseño tomadas (revisar antes de migrar)

1. **Técnicos ≠ usuarios del sistema.** Los técnicos de campo se modelan aparte
   (`tecnicos`), con `user_id` opcional. Solo 3 personas usan el sistema.
2. **Atributos variables → JSON** (`productos.especificaciones`) en vez de EAV,
   por pragmatismo. Si se necesita búsqueda estructurada, migrar a tablas
   `categoria_atributos` + `producto_atributo_valores`.
3. **Marca del cable** se toma de `producto.marca_id`; `nro_hilos` y `tipo_fibra`
   son del carrete físico.
4. **`stocks` es un cache**, no la verdad. La verdad son los movimientos.

## Cómo visualizar el diagrama

1. Abrir <https://dbdiagram.io>.
2. Pegar el contenido de `datalan.dbml`.
3. Exportar a PNG/PDF/SQL desde el propio dbdiagram cuando el modelo esté aprobado.

## Siguiente paso

Aprobar/ajustar este modelo → generar migraciones de Laravel (una por tabla,
respetando el orden de dependencias de las FK) → seeders con los datos base
(categorías, unidades, roles, almacenes).

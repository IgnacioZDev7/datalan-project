# Avance de Desarrollo — Sistema de Inventario DATALAN

> Registro del estado de la implementación. Complementa a
> [`plan-desarrollo.md`](plan-desarrollo.md) (el plan) y al modelo de datos en
> [`database/datalan.dbml`](database/datalan.dbml).
> Última actualización: Fases 0, 1 y 2 completadas.

## Resumen de avance

| Fase | Contenido | Estado |
|---|---|---|
| **0** | Preparación del entorno (Sanctum, Spatie, MySQL, CORS) | ✅ Completada |
| **1** | Migraciones del dominio (38 tablas, 49 FK) | ✅ Completada |
| **2** | Modelos Eloquent (23 modelos) | ✅ Completada |
| **3** | Seeders (datos base) | ✅ Completada |
| **4** | Autenticación API (Sanctum token) | ✅ Completada |
| **5** | API por módulos | 🔵 En curso — **Productos ✅ (referencia)** |
| **6+** | Frontend, reportes, pruebas, despliegue | ⏳ Pendiente |

> **Documentos clave para continuar / delegar:**
> [`guia-desarrollo-modulos.md`](guia-desarrollo-modulos.md) (patrón obligatorio) ·
> [`roadmap-fases.md`](roadmap-fases.md) (paquetes de trabajo y qué delegar a opencode).
> El módulo **Productos** es el ejemplo vivo: se copia para cada módulo nuevo.

**Stack:** Laravel 11 · PHP 8.2 · Sanctum (token) · Spatie Permission + Activity Log ·
MySQL (desarrollo) / PostgreSQL (final) · Frontend React (TailAdmin) desacoplado.

---

## Fase 0 — Preparación del entorno

Trabajado en `backend/`.

### Paquetes instalados
- **Laravel Sanctum** (`php artisan install:api`) → API tokens + `routes/api.php`.
- **spatie/laravel-permission** `^6.25` → roles y permisos.
- **spatie/laravel-activitylog** `^4.12` → auditoría.

### Configuración aplicada
- `config/permission.php` → **nombres de tabla en español**:
  `roles`, `permisos`, `permiso_rol`, `modelo_tiene_roles`, `modelo_tiene_permisos`.
- `config/activitylog.php` → tabla **`bitacora_actividad`**.
- `config/cors.php` → `allowed_origins` = `http://localhost:5173` (Vite) + `127.0.0.1:5173`.
- `php artisan storage:link` → enlace `public/storage` para imágenes de productos.
- `.env`:
  - `APP_NAME=DATALAN`, `FILESYSTEM_DISK=public`, `FRONTEND_URL=http://localhost:5173`.
  - **BD dual**: `DB_CONNECTION=mysql` activo (dev); bloque `pgsql` comentado (final).
- Base de datos `datalan` creada en MySQL con `utf8mb4` / `utf8mb4_unicode_ci`.

---

## Fase 1 — Migraciones del dominio

23 tablas de dominio + tablas de framework/paquetes = **38 tablas, 49 claves foráneas**.
`php artisan migrate:fresh` corre limpio en MySQL.

### `users` → `usuarios`
- La migración por defecto (`0001_01_01_000000_create_users_table.php`) se modificó
  para crear **`usuarios`** con: `nombres`, `apellido_paterno`, `apellido_materno`,
  `ci` (unique), `correo_electronico` (unique), `contrasena`, `cargo`, `telefono`,
  `activo`, `ultimo_acceso`, `remember_token`.
- `password_reset_tokens` y `sessions` se conservan (infraestructura de framework).
- `config/auth.php` → `providers.users.model = App\Models\Usuario`.

### Migraciones por bloque (orden de dependencias de FK)
`database/migrations/2026_07_05_0000NN_*`:

- **A — Base:** `unidades_medida`, `marcas`, `categorias` (self-FK), `empresas`,
  `proveedores`, `direcciones` (tabla de dirección postal, se crea primero para que
  las entidades la referencien por FK real `direccion_id`).
- **B — Catálogo/ubicación:** `modelos`, `productos`, `almacenes`, `ubicaciones`, `tecnicos`.
- **C — Operación:** `proyectos`.
- **D — Inventario:** `existencias`, `activos` (self-FK), `activo_historial`,
  `carretes` (self-FK), `inventarios_fisicos`, `movimientos`, `movimiento_detalles`,
  `inventario_fisico_detalles`.
- **E — Transversales:** `asignaciones`, `alertas`.

### Decisiones de implementación (portabilidad MySQL ↔ PostgreSQL)
- **Enums** → columnas `string` con `->comment('valor1|valor2|...')` (autodocumentado, portable).
- **JSON** → `json()` (no `jsonb`).
- **Soft delete** → `deleted_at` en las 11 tablas de negocio (productos, activos,
  carretes, movimientos, categorias, marcas, modelos, empresas, proveedores, tecnicos, proyectos).
- **Unicidad**: `nro_serie`/`mac`/códigos con `unique()` (NULLs no colisionan). En
  PostgreSQL se optimizará con índice único parcial `WHERE deleted_at IS NULL`.
- **Auto-referencias** (kits, cortes de cable, jerarquía de categorías) resueltas con
  FK a la misma tabla.

---

## Fase 2 — Modelos Eloquent

23 modelos en `app/Models/`. Todos verificados: mapean a su tabla y sus relaciones
cargan (probado con eager-loading real).

### Catálogo
`Categoria`, `Marca`, `Modelo`, `UnidadMedida`, `Producto`, `Direccion`.

### Inventario
`Almacen`, `Ubicacion`, `Existencia`, `Activo`, `ActivoHistorial`, `Carrete`,
`Movimiento`, `MovimientoDetalle`.

### Operación / Conteo / Alertas
`Empresa`, `Proveedor`, `Tecnico`, `Proyecto`, `Asignacion`, `InventarioFisico`,
`InventarioFisicoDetalle`, `Alerta`.

### Seguridad
`Usuario` (traits `HasApiTokens`, `HasRoles`, `Notifiable`; override `getAuthPassword()`
→ `contrasena`).

### Convenciones aplicadas en los modelos
- `$table` explícito en todos (necesario por los nombres en español).
- **Casts:** `especificaciones`→`array`; `credenciales`→**`encrypted:array`** (cifrado
  y oculto en serialización); fechas→`date`/`datetime`; decimales→`decimal:2`; `boolean`.
- **Traits:** `SoftDeletes` (11 entidades); `LogsActivity` en las entidades clave
  (en `Activo` se excluye `credenciales` del log de auditoría).
- **Relaciones especiales:**
  - Direcciones por FK real: `Usuario/Almacen/Empresa/Proveedor/Proyecto → belongsTo
    Direccion` (columna `direccion_id`). `direcciones` ≠ `ubicaciones` (posición
    interna del almacén).
  - Auto-referencias: `Activo` (`padre`/`contenido`), `Carrete` (`padre`/`cortes`),
    `Categoria` (`padre`/`hijos`).
  - Marca vía modelo: `Producto::marca()` (respeta 3FN).
- **Helpers de dominio:** `Usuario::nombre_completo`, `Existencia::bajo_minimo`,
  `InventarioFisicoDetalle::diferencia`.

---

## Fase 3 — Seeders (datos base)

`database/seeders/` — ejecutar con `php artisan migrate:fresh --seed`.

- **RolePermisoSeeder** — 3 roles (`gerente`, `encargado_almacen`, `jefe_tecnico`) y
  **85 permisos** (CRUD por módulo + especiales: aprobar/anular movimientos, reportes,
  bitácora). Gerente = todos; encargado = todo menos usuarios/roles; jefe técnico =
  activos/carretes/movimientos/proyectos/asignaciones + lectura.
- **UsuarioSeeder** — 3 usuarios reales con su rol. **Credenciales por defecto:**
  `gerente@datalan.bo`, `almacen@datalan.bo`, `tecnico@datalan.bo` — contraseña
  `password` (⚠️ cambiar en el primer ingreso).
- **UnidadMedidaSeeder** — metro, unidad, caja, bolsa, par, rollo, pieza.
- **CategoriaSeeder** — 21 categorías (4 padres por `tipo_inventario`: Consumibles,
  Cable de Fibra, Equipos Activos, Herramientas) + subcategorías.
- **AlmacenSeeder** — 2 almacenes (Central con dirección Av. Camacho 1277, La Paz +
  Secundario) con su ubicación `GENERAL` por defecto.

Verificado: asignación de roles/permisos correcta (gerente puede `movimientos.aprobar`;
jefe técnico NO puede `usuarios.crear`).

## Fase 4 — Autenticación API (Sanctum token)

Endpoints bajo `/api/v1` (verificados end-to-end con curl):

| Método | Ruta | Descripción |
|---|---|---|
| POST | `/api/v1/login` | Valida `correo_electronico` + `contrasena`, verifica `activo`, registra `ultimo_acceso`, emite token Bearer |
| POST | `/api/v1/logout` | Revoca el token actual (`auth:sanctum`) |
| GET | `/api/v1/me` | Usuario autenticado con `roles` y `permisos` (`auth:sanctum`) |

Componentes: `AuthController`, `LoginRequest` (validación), `UsuarioResource` (respuesta).
Login devuelve `{ token, token_type, usuario }`. El frontend guarda el token y lo envía
en `Authorization: Bearer <token>`.

> Pendiente menor (Fase 5): estandarizar el "envelope" de respuesta (login no envuelve
> en `data`, los Resources sí) y agregar Policies por módulo.

## Estado de la base de datos (38 tablas)

**Dominio (23):** usuarios, direcciones · categorias, marcas, modelos,
unidades_medida, productos · almacenes, ubicaciones, existencias, activos,
activo_historial, carretes, movimientos, movimiento_detalles · empresas, proveedores,
tecnicos, proyectos, asignaciones · inventarios_fisicos, inventario_fisico_detalles · alertas.

**Framework / Sanctum (8):** users→usuarios, password_reset_tokens, sessions, cache,
cache_locks, jobs, job_batches, failed_jobs, migrations, personal_access_tokens.

**Spatie (6):** roles, permisos, permiso_rol, modelo_tiene_roles,
modelo_tiene_permisos, bitacora_actividad.

---

## Cómo levantar el backend desde cero

```bash
cd backend
composer install
cp .env.example .env            # y ajustar credenciales (ver abajo)
php artisan key:generate
php artisan storage:link
php artisan migrate:fresh        # (--seed cuando existan seeders, Fase 3)
php artisan serve
```

`.env` (desarrollo, MySQL):
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=datalan
DB_USERNAME=root
DB_PASSWORD=********
```
Para cambiar a PostgreSQL (final): activar el bloque `pgsql` comentado en `.env` y
volver a `migrate:fresh --seed`.

---

## Pendiente

- **Fase 3 — Seeders:** roles/permisos (Spatie), usuario admin + 3 reales, unidades de
  medida, categorías base, los 2 almacenes de DATALAN (+ ubicación GENERAL y direcciones).
- **Fase 4 — Auth API:** login/logout/me con Sanctum, bloqueo por `activo`, Policies.
- **Fase 5 — API por módulos:** Catálogo → Inventario → **Movimientos** (Service +
  recálculo de existencias + kardex) → Operación → Conteo → Alertas/Dashboard.
- **Fase 6 — Frontend (TailAdmin):** integración por módulo.
- **Diferido:** costos/ventas, producto_proveedor, mantenimiento, etiquetas, adjuntos, EAN.

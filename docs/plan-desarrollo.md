# Plan de Desarrollo — Sistema de Inventario DATALAN

> Punto de partida: modelo de datos **v5 cerrado** (`docs/database/datalan.dbml`).
> Este documento define **cómo** construir el sistema sobre el stack real del repo.

---

## 1. Arquitectura y stack (detectado en el repo)

| Capa | Tecnología | Estado |
|---|---|---|
| **Backend / API** | Laravel 11 · PHP 8.2 (`backend/`) | Instalado, base limpia |
| **Frontend / SPA** | React 19 · TypeScript · Vite 6 · Tailwind 4 · plantilla **TailAdmin** (`frontend/`) | Plantilla lista |
| **Base de datos** | Dev: **MySQL** (Laragon) · Producción/final: **PostgreSQL** | Migraciones portables |
| **Auth** | Laravel Sanctum — **modo token (Bearer)** | Pendiente instalar |
| **Roles/permisos** | `spatie/laravel-permission` (pendiente) | — |
| **Auditoría** | `spatie/laravel-activitylog` (pendiente) | — |

**Arquitectura:** SPA desacoplada. React (TailAdmin) consume una **API REST** de
Laravel. Autenticación con **Sanctum** (tokens para SPA). El backend NO renderiza
vistas Blade salvo utilidades.

> **Estrategia dual de BD:** se **desarrolla y visualiza en MySQL** (cómodo en Laragon
> para manipular el diagrama y las tablas) y el proyecto **final corre en PostgreSQL**.
> Para que las **mismas migraciones** funcionen en ambos, se escriben **portables**:
> - Enums → `string()` + validación en la app (no ENUM nativo).
> - JSON → `json()` (no `jsonb`).
> - Índice único parcial (soft-delete `WHERE deleted_at IS NULL`) → **condicional por
>   driver**: se crea solo en `pgsql`; en `mysql`, unicidad simple + validación en la app.
>
> Cambio de motor = cambiar `DB_CONNECTION` en `.env` y `migrate:fresh --seed`. Los datos
> de desarrollo (MySQL) no migran solos; los datos reales entran al final en PostgreSQL.

---

## 2. Convenciones

- **Idioma:** BD y dominio en español (tablas, columnas, enums). Código Laravel en
  español para modelos/campos de dominio; términos de framework quedan en inglés.
- **Tablas** en plural (`productos`), **modelos** Eloquent en singular (`Producto`).
- **API** versionada bajo `/api/v1/...`, respuestas con **API Resources**.
- **Validación** en **Form Requests**; **autorización** en **Policies** (+ permisos Spatie).
- **Lógica de negocio** (movimientos, recálculo de stock) en **Services**, no en controladores.
- **Commits** por módulo/feature; rama actual `feature-modelo-bda` → luego ramas por módulo.

---

## 3. Fase 0 — Preparación del entorno

Objetivo: dejar el backend listo para migrar. (Trabajar dentro de `backend/`.)

1. **Verificar PostgreSQL** corriendo y la BD `datalan` creada (Laragon/pgAdmin).
2. **Instalar API + Sanctum** (Laravel 11): `php artisan install:api`
   → crea `routes/api.php` y publica Sanctum.
3. **Instalar paquetes Spatie:**
   - `composer require spatie/laravel-permission`
   - `composer require spatie/laravel-activitylog`
   - Publicar sus migraciones/config; en `config/permission.php` **renombrar las tablas
     a español** (roles, permisos, permiso_rol, modelo_tiene_roles, modelo_tiene_permisos).
4. **CORS** (`config/cors.php`) para el origen del frontend Vite (`http://localhost:5173`)
   y `SANCTUM_STATEFUL_DOMAINS` / `SESSION_DOMAIN` si se usa auth por cookie.
5. **`.env`:** confirmar `APP_NAME=DATALAN`, `APP_URL`, credenciales de Postgres,
   `FILESYSTEM_DISK=public` (para imágenes de productos) y `php artisan storage:link`.
6. **Decisión `usuarios`:** la migración por defecto de Laravel crea `users`. La
   reemplazamos por `usuarios` (ver Fase 1) y ajustamos: `User`→`Usuario` con
   `protected $table = 'usuarios'`, `getAuthPassword()` → `contrasena`,
   `config/auth.php`, y las tablas `password_reset_tokens`/`sessions` que referencian
   el email → usar `correo_electronico`.

**Entregable Fase 0:** backend arranca (`php artisan serve`), Sanctum y Spatie
instalados, CORS ok, sin migrar todavía el dominio.

---

## 4. Fase 1 — Migraciones (orden por dependencias de FK)

Una migración por tabla, con timestamps ordenados para respetar las FK. Bloques:

**Bloque A — Base (sin dependencias):**
1. `usuarios` (reemplaza `users`; campos v5 + `remember_token`)
2. `unidades_medida`
3. `marcas`
4. `categorias` (self-FK `categoria_padre_id`)
5. `empresas`
6. `proveedores`
7. *(Spatie)* roles / permisos / pivotes → vía migraciones del paquete
8. `direcciones` (tabla de dirección postal; se crea primero, referenciada por
   `direccion_id` FK real desde usuarios/almacenes/empresas/proveedores/proyectos)

**Bloque B — Catálogo y ubicación:**
9. `modelos` (FK `marcas`)
10. `productos` (FK `categorias`, `modelos`, `unidades_medida`; `jsonb especificaciones`; `imagen`)
11. `almacenes` (FK `usuarios.responsable_id`)
12. `ubicaciones` (FK `almacenes`; unique `(almacen_id, codigo)`)
13. `tecnicos` (FK `usuarios`)

**Bloque C — Operación:**
14. `proyectos` (FK `empresas`, `tecnicos`)

**Bloque D — Inventario físico:**
15. `existencias` (FK `productos`, `almacenes`; unique `(producto_id, almacen_id)`)
16. `activos` (FK `productos`, `ubicaciones`, `tecnicos`, self `activo_padre_id`;
    `jsonb credenciales`; enums `estado`/`situacion`; **unique parcial** en `codigo_interno`)
17. `activo_historial` (FK `activos`, `usuarios`)
18. `carretes` (FK `productos`, `ubicaciones`, self `carrete_padre_id`)
19. `inventarios_fisicos` (FK `almacenes`, `usuarios`)
20. `movimientos` (FK `almacenes` x2, `proveedores`, `proyectos`, `tecnicos`,
    `usuarios` x2, `inventarios_fisicos`)
21. `movimiento_detalles` (FK `movimientos`, `productos`, `activos`, `carretes`)
22. `inventario_fisico_detalles` (FK `inventarios_fisicos`, `productos`, `activos`, `carretes`)

**Bloque E — Transversales:**
23. `asignaciones` (FK `activos`, `tecnicos`, `proyectos`, `usuarios`)
24. `alertas` (FK `productos`, `activos`)

**Notas de implementación PostgreSQL:**
- **Enums:** en Laravel usar `$table->string(...)` + `$table->check(...)` o columnas
  string validadas en la app (más portable que los tipos ENUM nativos de PG).
- **Soft delete + unicidad:** índice único parcial `WHERE deleted_at IS NULL`
  (`productos.codigo`, `activos.codigo_interno`, `carretes.codigo`,
  `usuarios.correo_electronico`/`ci`).
- **`nro_serie`/`mac`:** `unique()` (NULLs no colisionan en PG).
- **JSON:** usar `jsonb`.
- **Reglas de negocio** (no en el esquema): recálculo de existencias, exclusividad
  activo/carrete en detalle, `situacion=asignado ⇒ tecnico_id`, no-ciclos en kits/cortes.
  Ver §"Restricciones" del `analisis-normalizacion.md`.

**Entregable Fase 1:** `php artisan migrate:fresh` corre limpio en PostgreSQL.

---

## 5. Fase 2 — Modelos Eloquent

Un modelo por tabla con: `$fillable`/`$guarded`, `$casts`, relaciones y traits.

- **Traits clave:**
  - `SoftDeletes` en las 11 tablas de negocio.
  - `Usuario`: `HasApiTokens` (Sanctum), `HasRoles` (Spatie), `Notifiable`, `Authenticatable`.
  - `LogsActivity` (Spatie) en modelos auditables (productos, activos, movimientos…).
- **Casts:** `especificaciones` → `array` (`jsonb`); `credenciales` → **`encrypted:array`**
  (cifrado obligatorio, G10); fechas → `datetime`; enums → Enum PHP (`enum` de PHP 8.1+).
- **Relaciones:** `belongsTo`/`hasMany` según FK; `direccion()` `belongsTo` en cada
  entidad; self-refs (`categoria_padre`, `activo_padre`, `carrete_padre`).
- **Enums PHP** para `estado_activo`, `situacion_activo`, `tipo_movimiento`, etc.
  (carpeta `app/Enums`).

**Entregable Fase 2:** modelos con relaciones probadas en `tinker`.

---

## 6. Fase 3 — Seeders (datos base)

1. **Roles y permisos** (Spatie): roles `gerente`, `encargado_almacen`, `jefe_tecnico`;
   permisos por módulo (ver productos, registrar movimiento, aprobar, etc.).
2. **Usuario administrador** inicial (gerente) + los 3 usuarios reales.
3. **Unidades de medida** (metro, unidad, caja, bolsa, par, rollo).
4. **Categorías** base según los docs (consumibles, cable/fibra, equipos activos,
   herramientas) con su `tipo_inventario`.
5. **Los 2 almacenes** de DATALAN + su ubicación `GENERAL` por defecto + direcciones.
6. *(Opcional)* marcas vistas en los datos (PLANET, FURUKAWA, DROOP, BIRLA, Fujikura…).

**Entregable Fase 3:** `php artisan migrate:fresh --seed` deja el sistema operable.

---

## 7. Fase 4 — Autenticación (API)

- **Sanctum**: login/logout que emite token; middleware `auth:sanctum`.
- Endpoints: `POST /api/v1/login`, `POST /api/v1/logout`, `GET /api/v1/me`.
- Bloqueo por `usuarios.activo=false`; registrar `ultimo_acceso`.
- Autorización: **Policies** por modelo + `can:`/permisos Spatie en rutas.

---

## 8. Fase 5 — API por módulos (vertical slices)

Cada módulo = Controller + Form Requests + API Resource + Policy + Service (si hay
lógica) + rutas `/api/v1`. **Orden de construcción recomendado** (entrega valor de a poco):

1. **Núcleo & Auth** — usuarios, roles/permisos, login. *(base de todo)*
2. **Catálogo** — categorías, marcas, modelos, unidades, **productos** (con carga de imagen). CRUD.
3. **Almacenes & ubicaciones** (+ direcciones).
4. **Inventario físico** — activos (+ `activo_historial`), carretes; existencias (lectura).
5. **Movimientos** — entradas/salidas/devoluciones/traslados/ajustes. Aquí vive el
   **Service** que actualiza `existencias` (observer) y descuenta metraje de carretes,
   + la **vista `vw_kardex`**. *(módulo corazón del sistema)*
6. **Operación** — empresas, proveedores, técnicos, proyectos, **asignaciones** de herramientas.
7. **Conteo físico** — inventarios físicos + detalles + reconciliación (genera ajustes).
8. **Alertas, dashboard y reportes** — stock mínimo, credenciales genéricas; KPIs;
   exportación Excel/PDF; generación de QR/códigos de barras.

---

## 9. Fase 6 — Frontend (TailAdmin) e integración

Por cada módulo del backend, la pantalla equivalente en React:
- Cliente API (axios/fetch) con el token Sanctum; manejo de sesión.
- Layout TailAdmin (sidebar por módulos), tablas con filtros (fecha, técnico, tipo,
  estado, ubicación), formularios, y **dashboard** con ApexCharts.
- Componentes ya disponibles en la plantilla: tablas, charts, calendario, dropzone
  (para imágenes de productos), formularios.

---

## 10. Estrategia de pruebas

- **Feature tests** (PHPUnit) por endpoint clave, sobre todo el **Service de movimientos**
  (que `stock = entradas − salidas + devoluciones` se mantenga; que un corte de carrete
  cuadre; que `situacion=asignado` exija técnico).
- **Base de datos de prueba** en PostgreSQL o SQLite en memoria (según compatibilidad de enums/jsonb).
- Factories con Faker para cada modelo.

---

## 11. Riesgos y decisiones ya tomadas

- **`users` → `usuarios`:** requiere ajustar auth/Sanctum/Spatie y las tablas de reseteo
  de contraseña. Contemplado en Fase 0/1.
- **Enums en PostgreSQL:** se implementan como string validado (no tipo nativo) por portabilidad.
- **Diferido a futuro** (aditivo, no bloquea): costos/ventas, `producto_proveedor`,
  mantenimiento, etiquetas, adjuntos, EAN.
- **Kardex/Dashboard:** consultas/vistas, no tablas.

---

## 12. Roadmap resumido

| Hito | Contenido | Depende de |
|---|---|---|
| **H0** | Entorno: Sanctum + Spatie + CORS + storage | — |
| **H1** | Migraciones + modelos + seeders (BD viva) | H0 |
| **H2** | Auth API + módulo Núcleo/usuarios | H1 |
| **H3** | Catálogo (productos) end-to-end (API + UI) | H2 |
| **H4** | Almacenes/ubicaciones + inventario físico (activos/carretes) | H3 |
| **H5** | **Movimientos** + existencias + kardex | H4 |
| **H6** | Operación (proyectos, técnicos, asignaciones) | H5 |
| **H7** | Conteo físico + reconciliación | H5 |
| **H8** | Alertas + dashboard + reportes + QR/exportación | H5 |

**Siguiente acción concreta:** ejecutar la **Fase 0** (preparar entorno) y luego generar
las migraciones de la **Fase 1**.

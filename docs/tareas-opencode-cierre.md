# Tareas de cierre del software DATALAN — para opencode

> Documento maestro de las tareas restantes. Cada tarea es **autocontenida y específica**.
> opencode debe leer también: `guia-desarrollo-modulos.md` (patrón backend),
> `roadmap-fases.md`, y usar como referencia el módulo **Productos** (backend) y la
> pantalla **Productos.tsx** (frontend).

---

## Reglas generales (obligatorias)

- **Entorno:** trabajar en un **worktree aislado** en la rama indicada (creada desde
  `feature-modelo-bda`, que ya tiene todo: backend completo + frontend + logo). NO cambiar
  de rama. Correr `composer install` (backend) y `npm install` (frontend) una vez.
- **Pre-flight (si falla, DETENERSE y avisar):**
  - `backend/` : `php artisan migrate:status` lista las migraciones del dominio.
  - `frontend/` : `npx tsc -b` pasa sin errores; existe `src/pages/Catalogo/Productos.tsx`.
- **Backend en puerto 8000** para probar el frontend (`php artisan serve` o Laragon).
- **NO tocar:** el motor `MovimientoService` ni `InventarioFisicoService`, la base de auth
  del frontend (`services/api.ts`, `context/AuthContext.tsx`, `ProtectedRoute`, `SignInForm`),
  el hook `useCrud`, ni el cifrado de `credenciales`.
- **Migraciones portables** (MySQL ↔ PostgreSQL): nada de SQL específico de un motor sin
  `if (DB::getDriverName() === 'pgsql')`. Enums = `string` + validación. JSON = `json()`.
- **Después de cada tarea:** `npx tsc -b` (frontend) y las pruebas curl deben pasar. **Commit por tarea.**
- **Probar con:** `gerente@datalan.bo` / `password` (y roles `almacen@datalan.bo`, `tecnico@datalan.bo`).

---

## TAREA 1 — Dirección "inline" (combinar el CRUD de direcciones en los formularios)

**Problema:** hoy `direcciones` es un CRUD aparte con un dropdown `direccion_id`. Es
incómodo (para un proveedor nuevo hay que elegir una dirección de una lista). 

**Objetivo:** que la dirección se llene **dentro del mismo formulario** de la entidad. El
modelo NO cambia (sigue el FK `direccion_id`); solo cambian el endpoint y el formulario.

**Entidades afectadas (todas las que tienen `direccion_id`):**
`usuarios`, `almacenes`, `empresas`, `proveedores`, `proyectos`.

### Backend (por cada entidad afectada)
1. En el **StoreRequest** y **UpdateRequest**, aceptar un objeto anidado `direccion`:
   ```php
   'direccion' => ['nullable', 'array'],
   'direccion.ciudad'     => ['nullable', 'string', 'max:80'],
   'direccion.zona'       => ['nullable', 'string', 'max:100'],
   'direccion.calle'      => ['nullable', 'string', 'max:150'],
   'direccion.nro'        => ['nullable', 'string', 'max:20'],
   'direccion.referencia' => ['nullable', 'string', 'max:255'],
   ```
   Quitar `direccion_id` de las reglas (ya no se manda desde el form).
2. En el **Controller** (`store` y `update`), dentro de una **transacción**:
   ```php
   $data = $request->validated();
   $dir = $data['direccion'] ?? null;
   unset($data['direccion']);

   // crear/actualizar la direccion si vienen datos
   if ($dir && array_filter($dir)) {
       if ($entidad?->direccion_id) {                 // update: reutiliza la existente
           $entidad->direccion->update($dir);
       } else {
           $data['direccion_id'] = \App\Models\Direccion::create($dir)->id;
       }
   }
   // ...create/update de la entidad con $data
   ```
3. El **Resource** de cada entidad debe incluir la dirección anidada:
   `'direccion' => new DireccionResource($this->whenLoaded('direccion'))` y el controller
   hace `->load('direccion')` en show/store/update.

### Frontend (por cada pantalla afectada)
1. Crear un componente reutilizable **`src/components/form/CamposDireccion.tsx`** con los 5
   campos (ciudad, zona, calle, nro, referencia) controlados por props `value`/`onChange`.
2. En cada formulario (Proveedores, Empresas, Almacenes, Proyectos, Usuarios): **quitar el
   select de `direccion_id`** y poner `<CamposDireccion />`. Enviar `direccion: {...}` en el
   payload. Al editar, precargar desde `entidad.direccion`.
3. **Eliminar** la pantalla `Direcciones` y su ítem de menú y ruta (ya no es un CRUD suelto).
   Dejar el endpoint backend `/direcciones` como está (por si se usa internamente).

### Terminado
- Crear un proveedor llenando nombre + nit + … + dirección **en el mismo form** → se guarda
  con su dirección. Editarlo cambia la dirección. Igual para empresas, almacenes, proyectos, usuarios.

---

## TAREA 2 — Optimización de rendimiento (carga instantánea)

**Diagnóstico:** cada request tarda ~0.2s (bien), pero las pantallas piden varias listas a
la vez (lista + selects de FKs) y `php artisan serve` es de un hilo → se encolan (~1s).

### Frontend (lo principal)
1. Crear hook **`src/hooks/useLookup.ts`** con **caché en memoria** (Map a nivel de módulo)
   para las listas de selects que casi no cambian:
   ```ts
   const cache = new Map<string, unknown[]>();
   export function useLookup(endpoint: string) {
     const [items, setItems] = useState<any[]>(cache.get(endpoint) ?? []);
     useEffect(() => {
       if (cache.has(endpoint)) return;               // ya cacheado → no re-pide
       api.get(endpoint, { params: { per_page: 200 } })
          .then(r => { cache.set(endpoint, r.data.data); setItems(r.data.data); });
     }, [endpoint]);
     return items;
   }
   ```
2. En TODAS las pantallas, reemplazar los `api.get('/categorias')` etc. de los **selects**
   por `useLookup('/categorias')`. (La **lista principal** de cada pantalla sigue con `useCrud`.)
3. Agregar un **skeleton/"Cargando..."** visible mientras `cargando` es true (ya existe en
   Productos.tsx; replicarlo).

### Backend / runtime (documentar, no es código)
- Recomendado: servir el backend con **Laragon (Apache + PHP-FPM)** apuntando a
  `backend/public` → maneja peticiones **en paralelo**. Alternativa: exportar
  `PHP_CLI_SERVER_WORKERS=4` antes de `php artisan serve`.
- Verificar que los `index` usan **eager loading** (`->with([...])`) — evita N+1.

### Terminado
- Navegar entre secciones carga al instante (los selects no se vuelven a pedir).

---

## TAREA 3 — Datos reales (seeders con la data de la documentación)

**Objetivo:** reemplazar los 3 registros de ejemplo por **datos reales** sacados de
`docs/relevamiento-info` y `docs/guias` (fotos del inventario real).

Crear **`database/seeders/DatosRealesSeeder.php`** (y llamarlo desde `DatabaseSeeder`
después de los seeders base). Insertar (con `firstOrCreate` para ser idempotente):
- **Marcas:** DROOP, BIRLA ERICSSON, FURUKAWA, TELCOM, NINGBO, SURLINK, SUMITOMO, PLANET,
  Huawei, Mikrotik, Ubiquiti, Fujikura, Comway.
- **Modelos** (ejemplos de las fotos): Fujikura FSM-60S/FSM-80S/FSM-50S, Comway C10,
  Sumitomo TYPE-81C, PLANET (media converter/switch), Huawei ONT, MGB-LA20, GPN-100, etc.
- **Productos:**
  - Consumibles: Conector LC, Patch Cord LC-LC, Tornillos tirafondo N°10/8, Ramplus N°8/10,
    Abrazaderas, Grapas para coaxial, etc. (ver hojas de `docs/guias`).
  - Cable (fibra): "Cable SM 2 hilos", "Cable MM", etc.
  - Activos: ONT, Switch, Media Converter, Router, Módulo SFP.
  - Herramientas: Fusionadora, OTDR, Medidor.
- **Carretes** (de la hoja de cables): DROOP SM 2 hilos 2000m, BIRLA ERICSSON SM 4 hilos,
  etc., con `metraje_inicial`/`metraje_disponible`.
- **Activos** (del cuaderno de fusionadoras): Fujikura FSM-60S serie —, FSM-80S serie
  PX7DRA…, Comway C10 serie 1040330..., etc., con `estado` y `observaciones` ("batería dañada").
- **Existencias** iniciales para algunos consumibles (via el `MovimientoService` con un
  movimiento de entrada, o directo en `existencias` para el seed inicial).
- **Empresas** cliente: BTV Oruro, SEGIP Sucre, TSE Achumani (aparecen en las hojas).

**No romper** los seeders existentes; este es adicional. Referencias: las imágenes en
`docs/guias/*.jpeg` y `docs/relevamiento-info/`.

### Terminado
- `php artisan migrate:fresh --seed` deja el sistema con datos realistas navegables.

---

## TAREA 4 — Pantallas pendientes del frontend

Clonar el patrón de `Productos.tsx`. Registrar ruta en `App.tsx` y menú en `AppSidebar.tsx`.

### 4A — Usuarios / Roles (admin)
- Endpoint: `/usuarios` (CRUD) + `/roles` (GET, para el select de roles).
- Campos del form: nombres, apellido_paterno, apellido_materno, ci, correo_electronico,
  contrasena (solo requerida al crear), cargo, telefono, **roles** (multi-select con los
  nombres de `/roles`), activo.
- `destroy` = desactivar (el backend ya lo maneja). Mostrar el rol en la tabla.
- Solo visible para quien tenga `usuarios.ver` (usar `puede()`).

### 4B — Conteo físico
- Endpoint: `/inventarios-fisicos`.
- Crear conteo: cabecera (codigo, almacen_id, fecha, observaciones) + **detalles** dinámicos
  (producto + cantidad_fisica) — igual que el form multi-línea de Movimientos.
- Ver conteo: tabla de detalles mostrando `cantidad_sistema`, `cantidad_fisica`, `diferencia`.
- Botón **"Cerrar y reconciliar"** → `POST /inventarios-fisicos/{id}/cerrar` (genera el ajuste).
- Botón **"Anular"** (mientras esté `en_proceso`).

---

## TAREA 5 — QR y códigos de barras (generar + imprimir)

**Solo generar/imprimir** (no escanear todavía, pero dejar preparado).

1. Instalar en el frontend: `qrcode.react` (QR) y `jsbarcode` + `react-barcode` (barras).
2. En las pantallas **Productos** y **Activos**: botón **"Etiqueta"** por fila → abre un modal
   con: el **QR** del `codigo` (productos) / `codigo_interno` (activos), el **código de barras**
   del mismo valor, el nombre del ítem, y un botón **"Imprimir"** (`window.print()` con estilo
   de etiqueta). El QR codifica el `codigo` (texto simple; a futuro puede ser una URL/deep-link).
3. **Preparado para escanear (futuro):** dejar un comentario `// TODO: escaneo` y estructurar
   el buscador de la pantalla para que un lector USB (que "teclea" el código) ya funcione:
   la búsqueda por `codigo` debe filtrar exacto si el texto coincide con un código.

### Terminado
- Desde Productos/Activos se genera y se imprime una etiqueta con QR + código de barras.

---

## TAREA 6 — Reportes con exportación PDF y Excel

**Reportes prioritarios:** Existencias (stock por almacén), Kardex (ya hay pantalla),
Movimientos (por rango de fechas/tipo).

**Enfoque (backend genera los archivos):**
1. Instalar: `composer require maatwebsite/excel` (Excel) y `barryvdh/laravel-dompdf` (PDF).
2. Endpoints (permiso `can:reportes.exportar`):
   - `GET /reportes/existencias/excel` y `/pdf` (opcional filtro `almacen_id`, `bajo_minimo`).
   - `GET /reportes/movimientos/excel` y `/pdf` (filtros `desde`, `hasta`, `tipo`).
   - `GET /reportes/kardex/{producto}/pdf` (reusa el cálculo del `KardexController`).
   - Devuelven el archivo descargable.
3. Frontend: botones **"Exportar Excel" / "Exportar PDF"** en las pantallas de Existencias,
   Movimientos y Kardex → abren la URL del endpoint con el token (o `window.open` con auth).
   Los reportes deben incluir el **logo y el nombre DATALAN** en el encabezado (para el PDF).

### Terminado
- Descargar el reporte de existencias y de movimientos en Excel y en PDF, con datos reales.

---

## TAREA 7 — Nota para el despliegue a PostgreSQL (NO romper)

El proyecto se desarrolla en MySQL y al final se despliega en **PostgreSQL**. Cualquier
cambio que opencode haga en migraciones debe **mantenerse portable**:
- Enums = columnas `string` con `->comment('a|b|c')` (NO enum nativo).
- JSON = `json()` (NO `jsonb`).
- Unicidad + soft delete: usar `->unique()` simple; el índice parcial de PostgreSQL
  (`WHERE deleted_at IS NULL`) se agrega **condicionalmente** con
  `if (DB::getDriverName() === 'pgsql') { DB::statement('...'); }`.
- No usar funciones SQL específicas de un motor sin verificar el driver.

El cambio de motor será solo: editar `.env` (`DB_CONNECTION=pgsql` + credenciales) y
`php artisan migrate:fresh --seed`. Si esto se respeta, no se rompe nada.

---

## Orden sugerido y entrega

1. Tarea 1 (dirección inline) → 2 (rendimiento) → 3 (datos reales) → 4 (pantallas) →
   5 (QR) → 6 (reportes). Tarea 7 es una regla transversal (aplíquese siempre).
2. Un **commit por tarea** con mensaje claro. Probar cada una (curl backend + `tsc -b` frontend).
3. Ante cualquier duda de regla de negocio: **no improvisar**, anotarlo y preguntar.

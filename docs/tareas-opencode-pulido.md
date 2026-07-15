# Tareas de pulido final del software DATALAN — para opencode

> Fase final para que el sistema se vea como un **producto administrativo terminado**.
> Leer también: `guia-desarrollo-modulos.md`, `estado-entrega.md` (§8), y usar como
> referencia el módulo Productos (backend) y `Productos.tsx` (frontend).

## Reglas generales (obligatorias)

- Worktree aislado, rama indicada (desde `feature-modelo-bda`). NO cambiar de rama.
  `composer install` + `npm install` una vez.
- **Pre-flight (si falla, DETENERSE):** `frontend/` → `npx tsc -b` sin errores;
  `backend/` → `php artisan migrate:status` lista las migraciones.
- **NO tocar:** `MovimientoService`, `InventarioFisicoService`, la base de auth del
  frontend (`services/api.ts`, `AuthContext`, `ProtectedRoute`, `SignInForm`), `useCrud`,
  el cifrado de `credenciales`.
- Migraciones **portables** (MySQL/PostgreSQL). Backend en **puerto 8000**.
- Un **commit por tarea**; probar cada una (curl + `tsc -b` + navegador). Credenciales:
  `gerente@datalan.bo` / `password`. Ante duda de negocio: NO improvisar, preguntar.
- Las librerías `apexcharts` y `react-apexcharts` **ya están** en el frontend.

---

## TAREA 1 — Dashboard con gráficas y estadísticas ⭐ (máxima prioridad)

**Objetivo:** que el **Panel** (`src/pages/Dashboard/Panel.tsx`) sea un dashboard real con
gráficas, no solo tarjetas.

### Backend
Crear `DashboardController` + ruta `GET /dashboard/estadisticas` (permiso `can:reportes.ver`)
que devuelva UN JSON con todo lo necesario (una sola petición):
```json
{
  "kpis": { "productos": 0, "activos": 0, "existencias_bajo_minimo": 0,
            "alertas": 0, "movimientos_mes": 0, "carretes": 0 },
  "movimientos_por_mes": [ { "mes": "2026-02", "entradas": 10, "salidas": 4 } ],   // últimos 6 meses
  "stock_por_categoria":  [ { "categoria": "Consumibles", "total": 1200 } ],
  "activos_por_situacion":[ { "situacion": "en_almacen", "total": 8 } ]
}
```
- `movimientos_por_mes`: contar movimientos por mes y tipo (entrada vs salida), últimos 6 meses.
- `stock_por_categoria`: sumar `existencias.cantidad_actual` agrupado por la categoría del producto.
- `activos_por_situacion`: contar `activos` agrupados por `situacion`.
- Ruta en `routes/modules/dashboard.php`.

### Frontend (`Panel.tsx`)
- Una sola llamada a `/dashboard/estadisticas`.
- Mantener las **tarjetas KPI** (usar los valores de `kpis`).
- Agregar gráficas con **react-apexcharts** (import `Chart from "react-apexcharts"`):
  - **Barra/línea**: movimientos por mes (entradas vs salidas).
  - **Dona (donut)**: stock por categoría **o** activos por situación.
- Mantener la tabla de **movimientos recientes**.
- Diseño limpio y responsivo, con los colores de marca (`brand-*`).

### Terminado
- El Panel muestra KPIs + al menos 2 gráficas con datos reales + movimientos recientes.

---

## TAREA 2 — Pantalla de Bitácora / Auditoría (visor de logs)

**Objetivo:** ver el historial de acciones (quién hizo qué y cuándo). La auditoría ya se
guarda en la tabla `bitacora_actividad` (Spatie Activity Log).

### Backend
- `BitacoraController@index` + `GET /bitacora` (permiso `can:bitacora.ver`, ya existe el permiso).
- Lista **paginada** del modelo `Spatie\Activitylog\Models\Activity` con:
  fecha (`created_at`), `description`, `log_name`, `event`, el **causer** (usuario que hizo
  la acción → cargar `causer`), `subject_type`/`subject_id`, y `properties` (cambios).
- Filtros: `usuario_id` (causer), `desde`, `hasta`, `buscar` (en description).
- Resource `BitacoraResource` que muestre el nombre del usuario causer legible.
- Ruta en `routes/modules/bitacora.php`.

### Frontend
- Pantalla **solo lectura** `src/pages/Administracion/Bitacora.tsx` (tabla + filtros +
  paginación, patrón de `Existencias`). Ruta `/bitacora` + ítem de menú en "Administración".
- Columnas: Fecha, Usuario, Acción (event/description), Entidad (subject_type), Detalle.

### Terminado
- Se ve el historial de cambios con quién y cuándo; filtrable por usuario y fecha.

---

## TAREA 3 — Alertas automáticas de stock mínimo

**Objetivo:** que el sistema **genere solo** las alertas cuando una existencia cae al mínimo.

### Backend
- Comando artisan `app/Console/Commands/GenerarAlertasStock.php` (`php artisan alertas:generar`):
  - Recorre `existencias` donde `cantidad_actual <= cantidad_minima` (y `cantidad_minima > 0`).
  - Por cada una, si NO existe ya una alerta `tipo=stock_minimo` **no leída** para ese
    `producto_id`, crea una: `tipo='stock_minimo'`, `nivel='advertencia'`,
    `mensaje="Stock bajo mínimo: {producto} ({cantidad_actual})"`, `producto_id`, `leida=false`.
  - Evitar duplicados (no crear si ya hay una no leída para ese producto).
- Programarlo en `routes/console.php` (o `bootstrap/app.php` scheduler) para correr **cada hora**
  o **diariamente**. Documentar que en dev se corre a mano con `php artisan alertas:generar`.
- (NO modificar `MovimientoService`.)

### Terminado
- Tras dejar una existencia bajo mínimo y correr `php artisan alertas:generar`, aparece la
  alerta en la pantalla de Alertas.

---

## TAREA 4 — Campana de notificaciones (real) + buscador global

### Campana (header)
- `src/components/header/NotificationDropdown.tsx`: reemplazar el contenido demo por datos
  reales. Consultar `GET /alertas` (filtrar no leídas): mostrar el **contador** de alertas
  no leídas (badge) y la lista. Un clic en una alerta puede marcarla leída
  (`PUT /alertas/{id}` con `leida:true`) o llevar al producto.

### Buscador global (header)
- El input "Search..." del header: al escribir + Enter, navegar a **Productos** filtrando por
  ese texto (ej. `/productos?buscar=XXX`) o mostrar resultados rápidos de productos/activos
  por `codigo`/`nombre`. Mantenerlo simple pero funcional (nada de contenido demo).

### Terminado
- La campana muestra las alertas reales con su contador; el buscador lleva a resultados reales.

---

## TAREA 5 — Nota de despliegue PostgreSQL (regla, no romper)

Al cambiar a PostgreSQL: `.env` → `DB_CONNECTION=pgsql` + credenciales, luego
`php artisan migrate:fresh --seed`. Mantener migraciones portables (enums = string,
`json()`, índice único parcial condicional por driver). No usar SQL específico de un motor.

---

## Orden sugerido y entrega
Tarea 1 (dashboard) → 2 (bitácora) → 3 (alertas auto) → 4 (campana/buscador). Tarea 5 es
regla transversal. Un commit por tarea, probado. Ante duda: preguntar.

# Roadmap de Fases y Distribución del Trabajo — DATALAN

> Plan para terminar el software y **repartir el desarrollo** (equipo + opencode).
> Regla base: todo módulo sigue [`guia-desarrollo-modulos.md`](guia-desarrollo-modulos.md)
> y usa **Productos** como ejemplo canónico.

---

## Estado actual

| Fase | Contenido | Estado |
|---|---|---|
| 0 | Entorno (Sanctum, Spatie, MySQL, CORS) | ✅ |
| 1 | Migraciones (38 tablas, 54 FK) | ✅ |
| 2 | Modelos Eloquent (23) | ✅ |
| 3 | Seeders (roles, usuarios, unidades, categorías, almacenes) | ✅ |
| 4 | Autenticación API (Sanctum token) | ✅ |
| **5** | **API por módulos** | 🔵 En curso — **Productos ✅ (referencia)** |
| 6 | Frontend (TailAdmin) | ⏳ |
| 7 | Reportes / Dashboard / QR / exportación | ⏳ |
| 8 | Pruebas | ⏳ |
| 9 | Despliegue (PostgreSQL) | ⏳ |

Fundación (~35% del software) lista. Falta el grueso funcional.

---

## Fase 5 — API por módulos (paquetes de trabajo)

Cada paquete = uno o varios módulos que siguen la guía. **Productos ya está hecho**
como referencia; los demás se clonan de él.

### 🟢 Delegables a opencode (CRUD independientes, sin lógica crítica)

Cada uno: Controller + Store/Update Request + Resource + 5 rutas `can:` + prueba curl.
Criterio de terminado = checklist §10 de la guía.

| Paquete | Módulos | Notas |
|---|---|---|
| **P5-A** | `categorias`, `marcas`, `modelos`, `unidades_medida` | CRUD simple; `modelos` valida `marca_id` |
| **P5-B** | `almacenes`, `ubicaciones`, `direcciones` | `ubicaciones` filtra por `almacen_id`; almacén liga `direccion_id` |
| **P5-C** | `empresas`, `proveedores`, `tecnicos` | CRUD simple + `direccion_id` |
| **P5-D** | `proyectos` | liga empresa/técnico/dirección; estado enum |
| **P5-E** | `activos`, `activo_historial` | CRUD + regla: `situacion=asignado ⇒ tecnico_id`; registrar historial al cambiar estado/situación. `credenciales` cifradas y nunca en respuesta |
| **P5-F** | `carretes` | CRUD; validar `metraje_disponible ≤ metraje_inicial` |
| **P5-G** | `alertas` | CRUD + marcar leída |

### 🔴 Track principal (NO delegar — lógica crítica e interdependiente)

| Paquete | Módulos | Por qué es central |
|---|---|---|
| **P5-H ⭐** | **`movimientos` + `movimiento_detalles`** | Motor del inventario. Un `MovimientoService` que: valida tipo, aplica `stock = entradas − salidas + devoluciones` sobre `existencias`, descuenta `metraje` de carretes, registra el corte (carrete hijo), y respeta "exactamente uno de {activo_id, carrete_id}". Incluye anulación (soft delete) y **vista/consulta kardex**. |
| **P5-I** | `existencias` (solo lectura + recálculo) | El observer/servicio que mantiene el cache. No se edita a mano. |
| **P5-J** | `inventarios_fisicos` + reconciliación | Genera `movimientos` tipo `ajuste`; depende de P5-H. |

> **Orden dentro de Fase 5:** primero P5-A…P5-D (catálogo/operación, en paralelo por
> opencode) → luego P5-E/P5-F (activos/carretes) → luego P5-H (movimientos, central) →
> P5-I/P5-J. Alertas (P5-G) en cualquier momento.

---

## Fase 6 — Frontend (TailAdmin)

Depende del API de cada módulo. **Paralelizable por pantalla** una vez el endpoint existe.

1. **Base (central):** cliente HTTP con token Sanctum, guard de rutas, login, layout/sidebar.
2. **Delegable a opencode (por módulo):** listado con filtros (tabla), formulario
   crear/editar, ver detalle, eliminar. Reusa componentes de TailAdmin (tablas, forms,
   dropzone para imágenes, ApexCharts para el dashboard).

Orden sugerido: Login → Catálogo (productos) → Inventario → Movimientos → Operación →
Conteo → Dashboard.

---

## Fase 7 — Reportes / Dashboard / QR / Exportación

- **Kardex** (vista SQL / consulta sobre movimientos).
- **Dashboard**: KPIs (stock por almacén, alertas, movimientos recientes).
- **Exportación** Excel/PDF (maatwebsite/excel, dompdf).
- **QR / código de barras** (simple-qrcode) a partir de `productos.codigo` / `activos.codigo_interno`.

Delegable en su mayoría; el diseño de KPIs conviene revisarlo en conjunto.

---

## Fase 8 — Pruebas

- **Feature tests** por módulo (CRUD + permisos 403 + validación 422).
- **Prioridad máxima:** tests del `MovimientoService` (que el stock cuadre, que un corte
  de carrete sume, que el ajuste de conteo funcione). No delegar a ciegas.

---

## Fase 9 — Despliegue (PostgreSQL)

- Cambiar `.env` a `pgsql`, `migrate:fresh --seed`.
- Convertir los índices únicos de códigos a **índice parcial** `WHERE deleted_at IS NULL`.
- Backups automáticos, variables de entorno de producción, build del frontend.

---

## Reglas para delegar a opencode

1. **Contexto obligatorio:** darle esta carpeta `docs/` (guía + este roadmap + el `.dbml`
   + `analisis-normalizacion.md`).
2. **Un paquete a la vez** con su alcance y checklist de terminado (guía §10).
3. **Prohibido inventar patrones**: copiar Productos.
4. **No tocar** el motor de Movimientos ni reglas de negocio transversales.
5. **Entregable probado:** cada módulo debe pasar el flujo curl (crear/listar/ver/editar/
   eliminar + 403 sin permiso).

---

## Ruta crítica

```
Auth ✅ → Productos ✅ (referencia)
   ├─ opencode ∥ : P5-A, P5-B, P5-C, P5-D  (catálogo/operación)
   ├─ principal  : P5-E, P5-F (activos/carretes)
   └─ principal  : P5-H ⭐ Movimientos → P5-I existencias → P5-J conteo
→ Frontend por módulo → Reportes/QR → Tests → Deploy PostgreSQL
```

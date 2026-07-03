# Análisis de Normalización (1FN → 3FN) — Modelo DATALAN

> Objetivo: dejar el modelo `datalan.dbml` limpio hasta **3ª Forma Normal** antes
> de generar migraciones. Este documento registra cada hallazgo, su gravedad, la
> justificación y la decisión tomada. Estado: **v2 en revisión**.

## Recordatorio de las formas normales

- **1FN:** valores atómicos, sin grupos repetidos, PK definida.
- **2FN:** 1FN + ningún atributo no-clave depende de *parte* de una clave compuesta.
- **3FN:** 2FN + ningún atributo no-clave depende de otro atributo no-clave
  (sin dependencias transitivas: `PK → A → B`).

## Resumen de hallazgos

| # | Tabla | Tipo | Gravedad | Estado |
|---|---|---|---|---|
| F1 | productos.tipo_manejo | Dependencia transitiva (3FN) | Alta | ✅ Corregido |
| F2 | productos.consumible | Dependencia transitiva (3FN) | Alta | ✅ Corregido |
| F3 | productos.marca_id + modelo_id | Redundancia (3FN) | Media | ✅ Corregido |
| F4 | activos/carretes.almacen_id + ubicacion_id | Dependencia transitiva (3FN) | Media | ✅ Corregido |
| F5 | movimiento_detalles.unidad_id | Redundancia (3FN) | Baja | ✅ Corregido |
| F6 | movimiento_detalles.estado (texto libre) | Consistencia / dominio | Baja | ✅ Corregido |
| F7 | stocks.cantidad_actual | Dato derivado (desnormalización) | — | ⚠️ Intencional (cache) |
| F8 | empresas vs proveedores | Diseño / DRY | Media | ✅ Resuelto: separadas |
| F9 | stock_minimo/maximo global vs por almacén | Diseño | Media | ✅ Resuelto: por almacén |
| F10 | proyectos.tecnico_id (1 solo técnico) | Cardinalidad | Baja | ✅ Resuelto: 1 responsable |
| F11 | Conteo físico / "faltantes" | Falta entidad | Media | ✅ Agregado |

---

## Hallazgos corregidos en v2

### F1 — `productos.tipo_manejo` es transitivo (3FN) ✅
`tipo_manejo` (cantidad/metraje/serie) queda determinado por la naturaleza de la
categoría (`categorias.tipo_inventario`): consumible→cantidad, cable→metraje,
activo/herramienta→serie. Es decir `producto → categoria → tipo_manejo`:
dependencia transitiva.
**Decisión:** se elimina `tipo_manejo` de `productos`. La única fuente es
`categorias.tipo_inventario`; la aplicación mapea a la tabla física
(stocks / carretes / activos).

### F2 — `productos.consumible` es transitivo (3FN) ✅
`consumible` = (`categorias.tipo_inventario` == consumible). Redundante.
**Decisión:** se elimina el booleano `consumible` de `productos`.

### F3 — `productos.marca_id` + `modelo_id` es redundante (3FN) ✅
Un modelo ya pertenece a una marca (`modelos.marca_id`). Guardar también
`marca_id` en `productos` crea la dependencia `producto → modelo_id → marca_id`
(transitiva) y permite inconsistencias (marca ≠ marca del modelo).
**Decisión:** `productos` conserva **solo** `modelo_id`. La marca se obtiene por
`producto → modelo → marca`. Productos genéricos sin marca ni modelo dejan
`modelo_id = NULL`.
> Implicación de captura de datos: un producto de marca conocida requiere una
> fila en `modelos` (aunque sea un modelo genérico de esa marca). Alternativa si
> se prefiere data-entry más simple: quitar la tabla `modelos` y dejar
> `productos.marca_id` (FK) + `productos.modelo` (texto). Indicar preferencia.

### F4 — `almacen_id` + `ubicacion_id` en activos/carretes es transitivo (3FN) ✅
Una ubicación pertenece a un almacén (`ubicaciones.almacen_id`). Guardar también
`almacen_id` en `activos`/`carretes` es transitivo: `activo → ubicacion → almacen`.
**Decisión:** se elimina `almacen_id` de `activos` y `carretes`; el almacén se
obtiene vía `ubicacion`. Convención: cada almacén tiene una ubicación por defecto
("GENERAL") para ítems sin posición asignada, de modo que `ubicacion_id` siempre
apunte al almacén correcto.
> `stocks` y `movimientos` sí referencian `almacen_id` directamente (trabajan a
> nivel de almacén, no de ubicación) — ahí no hay redundancia.

### F5 — `movimiento_detalles.unidad_id` es redundante (3FN) ✅
La unidad ya está en `productos.unidad_id`; el detalle no maneja conversión de
unidades. `detalle → producto → unidad`: transitivo.
**Decisión:** se elimina `unidad_id` de `movimiento_detalles` (se usa la del
producto). Si en el futuro se requieren conversiones (ej. rollo↔metro) se
reintroduce con una tabla de factores de conversión.

### F6 — `movimiento_detalles.estado` era texto libre ✅
Rompía la consistencia de dominio (el resto usa enums).
**Decisión:** pasa a enum `estado_item` (nuevo/bueno/regular/danado). Representa
el estado del ítem *en ese movimiento* (ej. devuelto dañado), distinto del estado
actual del activo.

### F11 — Faltaba la entidad de conteo físico / "faltantes" ✅
Las fotos 6-7 (NOTAS: "Faltantes — Lógicas vs Físicas") son reconciliaciones de
inventario que el modelo no representaba.
**Decisión:** se agregan `inventarios_fisicos` (cabecera: almacén, fecha,
responsable, estado) e `inventario_fisico_detalles` (producto/activo, cantidad
sistema vs cantidad física). Las diferencias se ajustan generando un
`movimiento` de tipo `ajuste`. La columna "diferencia" **no se almacena** (es
derivable: física − sistema).

---

## Desnormalización intencional (documentada)

### F7 — `stocks.cantidad_actual` ⚠️
Es un dato **derivado** de los movimientos (`entradas − salidas + devoluciones`),
por lo que estrictamente rompe 3FN. Se mantiene a propósito como **cache de
lectura** (rendimiento de dashboards/alertas). Reglas:
- Nunca se edita a mano.
- Se recalcula con un *observer* de Eloquent sobre `movimiento_detalles`.
- La **verdad** son los movimientos; `stocks` es reconstruible en cualquier momento.

---

## Decisiones resueltas

### F8 — ¿Unificar `empresas` y `proveedores`? → **Separadas**
Se mantienen como tablas distintas por claridad semántica. Si más adelante
aparece un tercero que sea cliente y proveedor a la vez, se reevaluará.

### F9 — Stock mínimo/máximo → **Por almacén**
`stock_minimo` y `stock_maximo` se movieron de `productos` a `stocks`, de modo
que cada almacén parametriza su propio punto de reorden y tope por producto.
(Aplica a consumibles, que son los que viven en `stocks`.)

### F10 — ¿Varios técnicos por proyecto? → **Un responsable**
`proyectos.tecnico_id` se mantiene como técnico responsable único. Los demás
técnicos que intervienen quedan registrados en los `movimientos` de cada salida.

### F3 — Marca/Modelo → **Tabla `modelos`**
Se conserva la tabla `modelos` (producto → modelo → marca) por mayor integridad
y para habilitar los reportes de "faltantes por modelo".

---

## Verificación por forma normal (post-v2)

- **1FN:** todas las tablas tienen PK; sin grupos repetidos; valores atómicos. ✔
- **2FN:** las únicas claves compuestas son pivotes (`role_user`, `permiso_role`)
  sin atributos no-clave → 2FN trivial. El resto usa PK simple (`id`). ✔
- **3FN:** eliminadas las dependencias transitivas F1–F5. Excepción documentada:
  `stocks.cantidad_actual` (cache). ✔

## Restricciones que la BD deberá reforzar (no expresables en DBML)

1. `movimiento_detalles`: exactamente uno de {`activo_id`, `carrete_id`} según el
   `tipo_inventario` del producto; los consumibles usan solo `cantidad`.
2. `carretes.metraje_disponible <= metraje_inicial` y `>= 0`.
3. Si `producto.modelo_id` no es nulo, la marca efectiva es la del modelo.
4. `movimientos`: `proveedor_id` solo en entradas; `proyecto_id` solo en salidas.
5. `activos`: si `situacion = asignado`, `tecnico_id` no puede ser nulo (G3).
6. `activos.activo_padre_id` (kits/maletines) no debe formar ciclos (G4).
7. `inventario_fisico_detalles`: a lo sumo uno de {`activo_id`, `carrete_id`} (G6).
8. Índices UNIQUE + soft delete (G2): `productos.codigo`, `activos.codigo_interno`,
   `carretes.codigo`, `usuarios.correo_electronico`/`ci` deben ser UNIQUE
   **incluyendo `deleted_at`** (o validar unicidad solo entre no-borrados), para que
   un registro borrado no bloquee el código.
9. `activos.credenciales` (G10): SIEMPRE cifrado en la app (encrypted cast); nunca
   texto plano.
10. `direcciones` (polimórfica): sin FK real sobre `direccionable_id`; la integridad
    se valida en la aplicación.

---

## Actualizaciones v3 / v4 (registro de cambios de nombres y adiciones)

**v3 — idioma y estructura:**
- Todo el esquema pasó a **español**: `users`→`usuarios`, `stocks`→`existencias`,
  `role_user`→`rol_usuario`, `permiso_role`→`permiso_rol`, `user_id`→`usuario_id`,
  `role_id`→`rol_id`, `bitacora.modelo`→`entidad`.
- `usuarios`: `nombres`, `apellido_paterno`, `apellido_materno`, `ci`,
  `correo_electronico`, `contrasena` (el modelo sobreescribe `getAuthPassword()`).
- Nueva tabla **`direcciones`** polimórfica (usuarios, almacenes, empresas,
  proveedores, proyectos); se quitaron los `direccion` varchar sueltos. Añade `ciudad`.
- G1: `existencias.cantidad_minima` / `cantidad_maxima` (umbral por almacén).

**v4 — observaciones G2..G10 (todas aplicadas):**
- G2 soft delete; G3 `situacion` de activos; G4 kits (`activo_padre_id`);
  G5 `fecha_fabricacion`; G6 `carrete_id` en conteo; G7 `inventario_fisico_id`
  en movimientos; G8 `productos.imagen`; G9 genealogía de carretes
  (`carrete_padre_id`); G10 `credenciales` cifradas.

**Pendiente P3 (costos/ventas):** fase futura. Se agregará
`movimiento_detalles.costo_unitario` + `productos.costo_referencial` sin romper el
modelo actual. El precio de venta corresponde al módulo de ventas (v2).

# Estado y Entrega — Sistema de Inventario DATALAN

> Documento de estado real del proyecto, para gestión y entrega al cliente.
> Fecha de corte: sistema con backend + frontend integrados y funcionando.

---

## 1. Resumen ejecutivo

**Qué es:** Sistema web de gestión de inventario para el **área técnica de DATALAN
Bolivia S.R.L.** (redes y telecomunicaciones). Controla material consumible, cable de
fibra óptica (por metraje), equipos activos y herramientas, basado en **movimientos**
(entradas/salidas/devoluciones/ajustes) con stock siempre cuadrado.

**Arquitectura:** SPA desacoplada.
- **Backend:** API REST en **Laravel 11** (PHP 8.2), autenticación con Sanctum (token),
  roles/permisos con Spatie. BD: **MySQL** en desarrollo, **PostgreSQL** para producción.
- **Frontend:** **React 19 + TypeScript + Vite + TailAdmin**, consumiendo la API.

**Estado general:** **MVP funcional y completo** en su núcleo (todo el inventario opera de
punta a punta). Faltan **detalles de pulido** para una entrega "premium" (ver §3 y §8).

---

## 2. Estado del proyecto y ramas (Git / GitHub)

Repositorio: `github.com/IgnacioZDev7/datalan-project`

| Rama | Rol | Estado |
|---|---|---|
| `main` | Producción | Vacía (solo estructura inicial). Se fusiona al final. |
| `develop` | Pre-producción / integración | Vacía por ahora. |
| **`feature-modelo-bda`** | **Desarrollo — tiene TODO el sistema** | ✅ Al día, en GitHub |
| `feature/cierre`, `feature/frontend-*`, `feature/inventario-*` | Ramas de trabajo (ya fusionadas) | Respaldo/historial |

**Toda la aplicación vive en `feature-modelo-bda`.** Cuando esté 100% pulido:
`feature-modelo-bda` → `develop` → `main`.

---

## 3. Estado del software (honesto)

### ✅ Terminado y funcionando
- **Backend (completo):** 107 rutas, autenticación, todos los módulos CRUD, **motor de
  inventario** (movimientos con recálculo de stock, anulación que revierte, kardex),
  **conteo físico con reconciliación**, usuarios/roles, **reportes Excel/PDF**, auditoría
  (registro de cambios en `bitacora_actividad`), datos reales cargados.
- **Frontend (funcional):** login real, ~20 pantallas (catálogo, terceros, proyectos,
  inventario, movimientos, kardex, conteo, usuarios), dirección inline, generación de
  **etiquetas QR + código de barras**, exportación de reportes, identidad visual DATALAN.

### ⚠️ Pendiente para una entrega pulida (NO está terminado al 100%)
- **Dashboard básico:** el Panel tiene 4 KPIs + movimientos recientes, pero **NO tiene
  gráficas/estadísticas** (se quitaron al limpiar la plantilla). Un dashboard
  administrativo "de verdad" necesita gráficas (ApexCharts ya está disponible).
- **Sin visor de logs/auditoría:** la bitácora se registra en el backend, pero **no hay
  pantalla** para consultarla.
- **Alertas no automáticas:** existe la tabla y la pantalla de alertas, pero **no hay un
  proceso** que genere alertas de stock mínimo automáticamente.
- **Notificaciones (campana) y buscador global:** aún con contenido de la plantilla.
- **Sin pruebas automatizadas.**
- **Aún en MySQL** (falta el cambio a PostgreSQL para producción).

> **Conclusión honesta:** el **sistema de inventario que pidió el cliente está funcional y
> demostrable**. Para entregarlo como producto **pulido/final** conviene completar el
> dashboard con gráficas, el visor de logs, las alertas automáticas y el despliegue.

---

## 4. Funcionalidades del software

**Seguridad:** login por token, roles y permisos, administración de usuarios (crear,
editar, asignar roles, desactivar), auditoría de cambios.

**Catálogo:** productos, categorías, marcas, modelos, unidades de medida.

**Inventario:**
- **Almacenes y ubicaciones** (2 sucursales).
- **Existencias** por almacén (stock, con umbral mínimo/máximo).
- **Activos** (equipos y herramientas, con serie/MAC/estado/situación, credenciales cifradas).
- **Carretes** de fibra (metraje disponible, genealogía de cortes).
- **Movimientos** (entrada/salida/devolución/baja/traslado/ajuste) — el stock siempre cuadra;
  anular revierte. **Kardex** (historial con saldo corrido).
- **Conteo físico** con reconciliación (genera ajuste automático de las diferencias).

**Operación:** empresas (clientes), proveedores, técnicos, proyectos, asignación de
herramientas a técnicos.

**Reportes:** existencias, movimientos y kardex, exportables a **Excel y PDF**.

**Etiquetas:** generación de **QR y código de barras** por producto/activo (imprimibles).

---

## 5. Roles y credenciales de acceso

> Contraseña por defecto de los 3: **`password`** (cambiar antes de producción).

| Rol | Correo | Contraseña | Qué puede hacer |
|---|---|---|---|
| **Gerente General** | `gerente@datalan.bo` | `password` | **Todo**: administración de usuarios/roles, todos los módulos y reportes |
| **Encargado de Almacén** | `almacen@datalan.bo` | `password` | Todo el inventario y catálogo (movimientos, conteo, existencias, productos, activos, carretes, proveedores); **no** gestiona usuarios/roles |
| **Jefe Técnico** | `tecnico@datalan.bo` | `password` | Activos, carretes, movimientos, proyectos, técnicos, asignaciones; **lectura** del catálogo; reportes |

---

## 6. Flujo de uso por rol (para la demostración al cliente)

### Rol GERENTE — supervisión y administración
1. Inicia sesión → **Panel** (indicadores del inventario).
2. **Administración → Usuarios:** crea/edita usuarios y les asigna roles.
3. Consulta cualquier módulo y **exporta reportes** (Excel/PDF).
4. Autoriza/supervisa (los movimientos guardan quién registró y quién autorizó).

### Rol ENCARGADO DE ALMACÉN — el día a día del inventario
1. **Llega material** (cable, conectores, equipos) → **Inventario → Movimientos → Nuevo
   → Entrada** (elige almacén destino + productos + cantidades) → el **stock sube**.
2. Un técnico necesita material → **Movimiento → Salida** (almacén origen + proyecto +
   productos) → el **stock baja**. Para cable, indica el carrete y los metros.
3. Revisa **Existencias** (stock por almacén, ve lo que está bajo mínimo).
4. Periódicamente hace un **Conteo físico** → registra lo contado → **Cerrar** → el sistema
   **genera un ajuste** y el stock cuadra con la realidad.
5. Imprime **etiquetas QR** de los productos.

### Rol JEFE TÉCNICO — equipos, herramientas y proyectos
1. **Proyectos:** crea una instalación (ej. "BTV Oruro") con su empresa cliente y dirección.
2. **Asignaciones:** entrega una herramienta (fusionadora, OTDR) a un técnico de campo;
   registra la devolución al volver.
3. **Activos:** consulta el estado/situación de los equipos (en almacén, instalado, dañado).
4. **Kardex:** revisa el historial de un producto/equipo con su saldo.

### Guion de demostración end-to-end (recomendado)
> Entrada de material (Almacén) → Crear proyecto y asignar herramienta (Jefe Técnico) →
> Salida de material al proyecto (Almacén) → ver el **Kardex** con el saldo → **Conteo
> físico** que detecta una diferencia y la **reconcilia** → **exportar** el reporte de
> existencias → imprimir una **etiqueta QR**. Todo mostrando cómo cada rol ve solo lo suyo.

---

## 7. Detalles de IMPORTANCIA

- **Puertos:** Backend en **`localhost:8000`** (exacto — el frontend lo busca ahí),
  Frontend en **`localhost:5173`**. Si el backend arranca en otro puerto (porque el 8000
  estaba ocupado), el login falla con "credenciales" (en realidad es "no llegó al backend").
  Levantar con `php artisan serve --port=8000`.
- **Base de datos:** desarrollo en **MySQL** (`datalan`). Reconstruir con
  `php artisan migrate:fresh --seed` (carga datos reales). Para producción: cambiar
  `DB_CONNECTION=pgsql` en `.env` y volver a migrar (las migraciones son portables).
- **Credenciales por defecto** (`password`) son de desarrollo → **cambiarlas** antes de
  entregar en producción.
- **Seguridad:** las credenciales de equipos (`activos.credenciales`) van **cifradas**.
- **El stock nunca se edita a mano:** siempre a través de movimientos (regla del sistema).
- **Documentación técnica** en `docs/`: modelo de datos (`database/datalan.dbml`), plan y
  avance, guías de desarrollo y tareas.

---

## 8. Para llegar a la entrega FINAL (pulido)

En orden de importancia para que se vea como un producto terminado:
1. **Dashboard con gráficas/estadísticas** (movimientos por mes, stock por categoría,
   productos bajo mínimo, etc.) — ApexCharts ya está disponible.
2. **Pantalla de bitácora/auditoría** (ver el historial de acciones por usuario).
3. **Alertas automáticas** de stock mínimo (proceso programado).
4. **Conectar la campana** de notificaciones a las alertas reales + buscador global.
5. **Despliegue a PostgreSQL** + backups.
6. (Opcional) pruebas automatizadas del motor de inventario.

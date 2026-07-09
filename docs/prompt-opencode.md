# Prompt e instrucciones para opencode (desarrollo en paralelo)

> Objetivo: que opencode implemente los módulos CRUD **sin pisar** el trabajo del track
> principal. Estrategia definitiva: **2 ramas + aislamiento por worktree**.

---

## Estrategia (leer antes de arrancar)

- **Ramas** (ambas nacen de `feature-modelo-bda`, que tiene toda la fundación):
  - `feature/inventario-core` → **track principal (nosotros):** movimientos, existencias,
    activos, carretes, conteo, alertas.
  - `feature/inventario-modulos` → **opencode:** catálogo, almacenes/ubicaciones/direcciones,
    empresas/proveedores/técnicos, proyectos.
- **Aislamiento total:** opencode trabaja en **su propio worktree** (carpeta separada),
  NUNCA en la carpeta principal. Así no puede tocar nuestros archivos.
  ```
  git worktree add ../datalan-opencode feature/inventario-modulos
  # en ../datalan-opencode/backend:  cp <ruta>/backend/.env .env  &&  composer install
  ```
- **Rutas modulares:** cada módulo trae su `routes/modules/<modulo>.php` propio → nadie
  edita `routes/api.php` → **cero conflictos de merge**.
- **Integración:** al terminar, se mergea `feature/inventario-modulos` → `feature-modelo-bda`
  (verificando que no haya conflictos). El track principal hace lo mismo con su rama.

**Qué NO se toca (ya está hecho y probado):** migraciones, modelos (`app/Models`),
seeders, config, auth, `routes/api.php`, y el módulo Movimientos.

---

## PROMPT (para pegar a opencode)

```
Eres un desarrollador Laravel en el sistema de inventario DATALAN. La base de datos, los
modelos Eloquent, la autenticación (Sanctum) y los seeders YA ESTÁN HECHOS Y PROBADOS.
Tu tarea es implementar módulos de API REST replicando un patrón ya establecido.

TU ENTORNO:
- Trabajas SOLO en esta carpeta (tu worktree): <ruta-del-worktree>
- En la rama: feature/inventario-modulos  (ya creada desde feature-modelo-bda, con toda
  la fundación). NO cambies de rama. NO hagas git checkout a otra rama. NO toques carpetas
  fuera de esta.

PRE-FLIGHT (verifica ANTES de escribir código; si algo falla, DETENTE y avisa):
- ls backend/app/Models  -> deben aparecer 23 modelos (Usuario, Producto, Activo, Carrete...).
- backend/app/Http/Controllers/Api/ProductoController.php  -> debe existir.
- cd backend && php artisan migrate:status  -> deben listarse las migraciones del dominio.
Si NO se cumple, tu rama está mal basada: NO trabajes, reporta el problema.

LEE (obligatorio):
1. docs/guia-desarrollo-modulos.md  — el patrón EXACTO a seguir.
2. docs/roadmap-fases.md            — el alcance de tus módulos.
3. El módulo de referencia YA implementado (cópialo tal cual, adaptando campos):
   backend/app/Http/Controllers/Api/ProductoController.php,
   backend/app/Http/Requests/Producto/*, backend/app/Http/Resources/ProductoResource.php,
   backend/routes/modules/productos.php

TUS MÓDULOS (implementa en este orden, uno a la vez, probando cada uno):
  1) categorias, marcas, modelos, unidades_medida   (catálogo)
  2) almacenes, ubicaciones, direcciones            (ubicación)
  3) empresas, proveedores, tecnicos                (terceros)
  4) proyectos
  5) alertas

POR CADA MÓDULO crea SOLO:
  * app/Http/Controllers/Api/<Entidad>Controller.php
  * app/Http/Requests/<Entidad>/Store<Entidad>Request.php  y  Update<Entidad>Request.php
  * app/Http/Resources/<Entidad>Resource.php   (si ya existe, reúsalo/amplíalo con cuidado)
  * routes/modules/<modulo>.php                 (su archivo de rutas; NO edites routes/api.php)

REGLAS:
- Copia el patrón de Productos EXACTAMENTE. No inventes estructuras nuevas.
- NO toques: migraciones, modelos (app/Models), seeders, config, auth, routes/api.php,
  ni el módulo Movimientos (movimientos/existencias/carretes son del otro track).
- NO ejecutes migrate:fresh (BD compartida). Prueba con datos de códigos únicos.
- Autorización por ruta con can:<modulo>.<accion> (los permisos ya existen sembrados).
- Los Resources CategoriaResource, MarcaResource, ModeloResource, UnidadMedidaResource
  YA EXISTEN: reúsalos. Solo crea sus Controllers + Requests + routes/modules/<x>.php.
- 'modelos' valida marca_id (exists:marcas,id); su unicidad es el par (marca_id, nombre).

DEFINICIÓN DE TERMINADO por módulo (checklist §10 de la guía):
- Controller con index (paginado + filtros buscar/activo/per_page/orden/dir), store, show, update, destroy.
- Store/Update Requests con validación, mensajes en español y unicidad soft-delete-aware.
- Resource sin datos sensibles, con whenLoaded. 5 rutas con su can: correcto.
- PROBADO con curl: login (gerente@datalan.bo / password) → CRUD + un 403 + un 422.

ENTREGA: commits en feature/inventario-modulos con los archivos y la evidencia de las
pruebas curl por módulo. Ante una duda de regla de negocio, NO improvises: anótalo y pregunta.
```

---

## Checklist de arranque (para el humano, una vez)

1. `git checkout feature-modelo-bda` (base con la fundación).
2. `git branch feature/inventario-modulos feature-modelo-bda`
3. `git worktree add ../datalan-opencode feature/inventario-modulos`
4. En `../datalan-opencode/backend`: `cp` del `.env` + `composer install`.
5. Pegar el PROMPT a opencode apuntando a `../datalan-opencode`.
6. Nosotros seguimos en la carpeta principal, rama `feature/inventario-core`.

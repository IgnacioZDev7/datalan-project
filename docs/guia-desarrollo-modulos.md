# Guía de Desarrollo de Módulos (API) — DATALAN

> **Documento de referencia obligatorio.** Todo módulo del backend se construye
> siguiendo este patrón. El módulo **`Productos`** es el ejemplo vivo y canónico:
> ante cualquier duda, mirar sus archivos y replicar.
>
> Público: desarrolladores del equipo y agentes (opencode) que implementen módulos.

---

## 1. Principios

- **Stack:** Laravel 11 · API REST bajo `/api/v1` · Auth Sanctum (token Bearer) ·
  permisos Spatie · BD dev MySQL / final PostgreSQL (migraciones portables).
- **Idioma:** dominio en español (tablas, columnas, rutas, mensajes). Términos de
  framework en inglés.
- **Separación:** validación en **FormRequest**, forma de respuesta en **Resource**,
  autorización en **middleware `can:`**, lógica de negocio en **Service** (solo si la hay).
- **No romper lo existente:** reusar modelos (`app/Models`), enums documentados en el
  `.dbml`, y las reglas de negocio de `docs/database/analisis-normalizacion.md`.

---

## 2. Estructura de un módulo

Para una entidad `Entidad` (ej. `Producto`) se crean:

```
app/Http/Controllers/Api/EntidadController.php   # index, store, show, update, destroy
app/Http/Requests/Entidad/StoreEntidadRequest.php
app/Http/Requests/Entidad/UpdateEntidadRequest.php
app/Http/Resources/EntidadResource.php
routes/modules/entidad.php                        # 5 rutas con middleware can: (ARCHIVO PROPIO)
```

El **modelo** ya existe en `app/Models/`. No se crean Policies: la autorización va por
middleware `can:<permiso>` (Spatie registra cada permiso como Gate automáticamente).

> **Cada módulo tiene su propio archivo de rutas** en `routes/modules/`. `routes/api.php`
> los carga automáticamente (`glob`), así **nadie edita un archivo compartido** y no hay
> conflictos de merge trabajando en paralelo.

---

## 3. Rutas y permisos

Crear **`routes/modules/<modulo>.php`** (se carga solo; hereda el prefijo `v1` y el
middleware `auth:sanctum`). Dentro, **rutas explícitas** (una por acción). El archivo
empieza con sus `use`:

```php
<?php
use App\Http\Controllers\Api\ProductoController;
use Illuminate\Support\Facades\Route;

Route::get   ('productos',            [ProductoController::class, 'index'])  ->middleware('can:productos.ver');
Route::post  ('productos',            [ProductoController::class, 'store'])  ->middleware('can:productos.crear');
Route::get   ('productos/{producto}', [ProductoController::class, 'show'])   ->middleware('can:productos.ver');
Route::put   ('productos/{producto}', [ProductoController::class, 'update']) ->middleware('can:productos.editar');
Route::delete('productos/{producto}', [ProductoController::class, 'destroy'])->middleware('can:productos.eliminar');
```

**Mapeo acción → permiso** (constante en todos los módulos):

| Acción | Método/Ruta | Permiso |
|---|---|---|
| Listar | `GET /modulo` | `modulo.ver` |
| Crear | `POST /modulo` | `modulo.crear` |
| Ver | `GET /modulo/{id}` | `modulo.ver` |
| Editar | `PUT /modulo/{id}` | `modulo.editar` |
| Eliminar | `DELETE /modulo/{id}` | `modulo.eliminar` |

Los permisos ya están sembrados (`RolePermisoSeeder`). Si un módulo necesita un permiso
especial (ej. `movimientos.aprobar`), agregarlo al seeder.

---

## 4. Controller

- Métodos: `index`, `store`, `show`, `update`, `destroy`.
- **Route model binding**: `show(Producto $producto)`.
- `index` **siempre** pagina y soporta filtros estándar.
- Devuelve **Resources** (nunca el modelo crudo).

Filtros estándar en `index` (query params):

| Param | Efecto |
|---|---|
| `buscar` | Búsqueda LIKE en campos de texto clave (nombre, código) |
| `activo` | Filtra por estado (`1`/`0`) si la entidad lo tiene |
| `per_page` | Tamaño de página (default 15) |
| `orden` / `dir` | Ordenamiento (`dir` = `asc`/`desc`) |
| *(FK)* | Filtros por relación cuando aplique (ej. `categoria_id`) |

Ver [`ProductoController`](../backend/app/Http/Controllers/Api/ProductoController.php) como referencia.

---

## 5. FormRequests (validación)

- `authorize(): true` (la autorización va en la ruta).
- Reglas con existencia de FK (`exists:tabla,id`) y **unicidad soft-delete-aware**:

```php
// Store
'codigo' => ['required','string','max:60',
    Rule::unique('productos','codigo')->whereNull('deleted_at')],

// Update (ignora el registro actual)
'codigo' => ['sometimes','required','string','max:60',
    Rule::unique('productos','codigo')->ignore($this->route('producto'))->whereNull('deleted_at')],
```

- **Mensajes en español** en `messages()`.
- En `Update`, los campos van con `sometimes` (actualización parcial permitida).

---

## 6. Resources (forma de respuesta)

- Exponen solo lo necesario; nunca campos sensibles (ej. `credenciales`).
- Incluyen relaciones con `whenLoaded()` para no forzar queries.

```php
'categoria' => new CategoriaResource($this->whenLoaded('categoria')),
```

---

## 7. Formato de respuesta (envelope)

| Caso | Forma | Código |
|---|---|---|
| Recurso único | `{ "data": { ... } }` | 200 / 201 |
| Lista paginada | `{ "data": [...], "links": {...}, "meta": {...} }` | 200 |
| Eliminado | `{ "message": "Eliminado correctamente." }` | 200 |
| Error de validación | `{ "message": "...", "errors": { campo: [...] } }` | 422 |
| No autenticado | `{ "message": "Unauthenticated." }` | 401 |
| Sin permiso | `{ "message": "This action is unauthorized." }` | 403 |
| No encontrado | `{ "message": "..." }` | 404 |

> El único endpoint con forma propia es el login (`{ token, token_type, usuario }`),
> por ser autenticación.

---

## 8. Archivos (imágenes)

Cuando la entidad tiene imagen (ej. `productos.imagen`):

```php
if ($request->hasFile('imagen')) {
    $data['imagen'] = $request->file('imagen')->store('productos', 'public');
}
```

Se guarda la **ruta** (no el binario). Se sirve desde `storage/` (ya hay `storage:link`).
La petición es `multipart/form-data` cuando incluye archivo.

---

## 9. Soft deletes

- `destroy()` hace borrado lógico (el modelo usa `SoftDeletes`).
- La unicidad de códigos se valida solo entre no-borrados (`whereNull('deleted_at')`).
- (Opcional futuro) endpoint `restore` si el módulo lo requiere.

---

## 10. Definición de "Terminado" (checklist por módulo)

Un módulo se considera completo cuando:

- [ ] Controller con los 5 métodos, `index` paginado + filtros estándar.
- [ ] Store/Update FormRequests con validación completa y mensajes en español.
- [ ] Resource que no expone datos sensibles y usa `whenLoaded` para relaciones.
- [ ] 5 rutas con su `can:<permiso>` correcto.
- [ ] Permisos existen en `RolePermisoSeeder` (o se agregan).
- [ ] Probado: login → CRUD completo con token (curl o Postman), incluyendo 403 sin permiso.
- [ ] Respeta el envelope de respuesta y las reglas de negocio del `.dbml`.

---

## 11. Cómo probar (flujo curl)

```bash
# 1. Login (obtener token)
TOKEN=$(curl -s -X POST http://127.0.0.1:8000/api/v1/login \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"correo_electronico":"gerente@datalan.bo","contrasena":"password"}' \
  | python -c "import sys,json;print(json.load(sys.stdin)['token'])")

# 2. Usar el token
curl -s http://127.0.0.1:8000/api/v1/productos \
  -H "Accept: application/json" -H "Authorization: Bearer $TOKEN"
```

Usuarios de prueba (todos con contraseña `password`): `gerente@datalan.bo` (todo),
`almacen@datalan.bo` (inventario), `tecnico@datalan.bo` (activos/movimientos).

---

## 12. Referencia viva

El módulo **Productos** implementa esta guía al 100%. Archivos:
- `app/Http/Controllers/Api/ProductoController.php`
- `app/Http/Requests/Producto/StoreProductoRequest.php`
- `app/Http/Requests/Producto/UpdateProductoRequest.php`
- `app/Http/Resources/ProductoResource.php`
- `routes/modules/productos.php` (su archivo de rutas propio)

**Para implementar un módulo nuevo: copiar Productos, renombrar, ajustar campos,
validaciones y permiso.** No inventar patrones nuevos.

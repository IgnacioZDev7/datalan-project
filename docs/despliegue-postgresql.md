# Guía de Despliegue — PostgreSQL

> Regla transversal: **NO romper la portabilidad de las migraciones.**

---

## Cambio de MySQL a PostgreSQL

### 1. Actualizar `.env`

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=datalan
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_password
```

### 2. Ejecutar migraciones

```bash
php artisan migrate:fresh --seed
```

---

## Reglas de portabilidad (migraciones)

### ✅ HACER

| Requerimiento | Solución portable |
|---|---|
| Enums | Usar `string()` o `enum()` con valores string |
| JSON | Usar `json()` (funciona en MySQL y PostgreSQL) |
| Índices únicos condicionales | Verificar driver con `DB::getDriverName()` |
| Agrupar por fecha | Agrupar en PHP, no en SQL (ej: `substr($fecha, 0, 7)`) |
| Funciones de fecha | Usar Carbon de PHP en vez de SQL específico |

### ❌ NO HACER

| Operación | Por qué |
|---|---|
| `DATE_FORMAT()` | Solo MySQL. Usar agrupación en PHP. |
| `TO_CHAR()` | Solo PostgreSQL. Usar agrupación en PHP. |
| `IFNULL()` | Usar `COALESCE()` (estándar SQL) o manejar en PHP |
| `NOW()` | Usar `Carbon::now()` en PHP o `CURRENT_TIMESTAMP` (estándar) |
| Auto-increment `AUTO_INCREMENT` | Usar `bigIncrements()` o `increments()` de Laravel |
| `TRUNCATE TABLE` | Usar `Schema::dropIfExists()` + `Schema::create()` |

### Ejemplo: índice único condicional portable

```php
// ❌ NO portable (solo MySQL)
$table->unique('codigo')->where('deleted_at', null);

// ✅ Portable: verificar driver
$table->unique('codigo');
// O crear el índice condicional en un Migration::afterCommit() con check de driver
```

### Ejemplo: agrupación por fecha portable

```php
// ❌ NO portable
DB::raw("DATE_FORMAT(fecha, '%Y-%m') as mes")

// ✅ Portable: agrupar en PHP
$registros->groupBy(fn ($r) => substr((string) $r->fecha, 0, 7));
```

---

## Verificación post-despliegue

1. `php artisan migrate:status` — verificar que todas las migraciones pasaron
2. `php artisan db:seed` — cargar datos de prueba
3. Probar login: `gerente@datalan.bo` / `password`
4. Verificar que el dashboard carga correctamente

# 8. Comandos y mantenimiento

[← Índice](./README.md)

## 8.1 Comandos Artisan personalizados

| Comando | Archivo | Descripción |
|---------|---------|-------------|
| `visit-destinations:backfill` | `BackfillVisitDestinations.php` | Asigna destinos a visitas históricas |
| `visit-destinations:assign-user` | `AssignUserVisitDestination.php` | Asigna destino a usuario |
| `import:plaza-venezuela-autoriza` | `ImportPlazaVenezuelaAutoriza.php` | Import Excel columna Autoriza |
| `import:internal-staff-massive {file}` | `ImportInternalStaffMassive.php` | Carga masiva funcionarios |
| `import:mata-de-coco` | `ImportMataDeCocoData.php` | CSV deptos/cargos/empleados Mata de Coco |
| `purposes:normalize` | `NormalizePurposes.php` | Normaliza motivos a mayúsculas |
| `check:user-permissions {id}` | `CheckUserPermissions.php` | Diagnóstico permisos |
| `test:visitor-filters` | `TestVisitorFilters.php` | Prueba filtros por rol |

Registro automático: `app/Console/Kernel.php` carga `app/Console/Commands/`.

## 8.2 Comandos de destinos de visita

### Backfill (seguro con dry-run)

```bash
# Simular sin cambios
php artisan visit-destinations:backfill --dry-run --only-null --headquarters=1

# Aplicar solo filas sin destino
php artisan visit-destinations:backfill --only-null --headquarters=1
```

| Opción | Descripción |
|--------|-------------|
| `--dry-run` | Solo muestra conteos, no escribe |
| `--only-null` | Solo visitas sin `visit_destination_id` |
| `--headquarters=` | Limitar a una sede |

### Asignar recepcionista a destino

```bash
php artisan visit-destinations:assign-user usuario@seniat.gob.ve atencion-contribuyente-mata-coco
```

> Preferir asignación desde **Admin → Destinos de visita** o **Editar empleado** cuando sea posible.

## 8.3 Comandos Laravel estándar (operación)

```bash
# Limpiar caché de configuración (tras cambiar .env)
php artisan config:clear

# Limpiar vistas compiladas
php artisan view:clear

# Limpiar caché de aplicación
php artisan cache:clear

# Optimizar autoload (post-deploy)
composer dump-autoload -o

# Migraciones pendientes
php artisan migrate --force

# Estado migraciones
php artisan migrate:status
```

## 8.4 Troubleshooting frecuente

### Error: Permission denied en `storage/framework/cache`

**Causa:** Archivos caché creados por otro usuario (ej. tinker como `ajvivas`).

**Solución:**

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R ug+rwx storage bootstrap/cache
```

### Cola por destino vacía

**Causas posibles:**

1. Usuario sin destinos asignados.
2. Visitas sin `visit_destination_id` y sin regla employee coincidente.
3. Filtro "Solo hoy" sin visitas del día.

**Diagnóstico:**

```sql
-- Visitas hoy para employee 99
SELECT id, visit_destination_id, employee_id, created_at, checkout_at
FROM visiting_details
WHERE employee_id = 99 AND created_at::date = CURRENT_DATE;
```

**Reparación:** La cola ejecuta `repairMissingDestinations` automáticamente; alternativa manual con backfill.

### Status muestra clave técnica (`statuses.2`)

**Causa:** Uso incorrecto de traducción `statuses` en lugar de `visitor_statuses`.

**Corregido en:** `VisitingDetails::getMyStatusAttribute()` y `VisitDestinationQueueController`.

### Permisos Spatie no aplican tras cambio

```bash
php artisan permission:cache-reset
php artisan config:clear
```

### 500 tras deploy

1. Revisar `storage/logs/laravel.log`
2. Verificar permisos storage
3. `php artisan config:clear`
4. Confirmar migraciones: `php artisan migrate:status`

## 8.5 Imports Excel (producción)

Siempre usar **`--dry-run`** primero cuando el comando lo soporte:

```bash
php artisan import:plaza-venezuela-autoriza ruta/archivo.xlsx --dry-run
php artisan purposes:normalize --dry-run
```

## 8.6 Tests

```bash
php artisan test --filter=InternalStaffDataProductionSafeTest
```

Usa `DatabaseTransactions` — no modifica datos permanentemente.

## 8.7 Monitoreo

| Recurso | Acción |
|---------|--------|
| `storage/logs/laravel.log` | Revisar errores diarios |
| Sentry | Alertas automáticas si DSN configurado |
| Apache error.log | Errores PHP/Apache |

[← Seguridad](./07-seguridad-permisos.md) · [Siguiente: Integraciones →](./09-integraciones.md)

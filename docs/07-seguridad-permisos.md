# 7. Seguridad y permisos

[← Índice](./README.md)

## 7.1 Autenticación

| Guard | Mecanismo | Uso |
|-------|-----------|-----|
| `web` | Sesión Laravel + cookies | Panel `/admin` |
| `api` | JWT (`tymon/jwt-auth`) | App móvil `/api/v1` |

Archivos: `config/auth.php`, `config/jwt.php`

## 7.2 Roles del sistema

| Rol | ID enum | Acceso |
|-----|---------|--------|
| Admin | 1 | Completo |
| Employee | 2 | Anfitrión / app móvil |
| Reception | 3 | Recepción + cola destinos |
| supervisor | — | Admin acotado por sede |

Seeders: `RoleTableSeeder`, `SupervisorRoleSeeder`

## 7.3 Middleware de autorización

| Middleware | Archivo | Función |
|------------|---------|---------|
| `auth` | Laravel | Usuario autenticado |
| `backend_permission` | `IsHasBackendPermission.php` | Roles 1–4 → admin; otros → `/home` |
| `permission:*` | Spatie | Permiso granular por ruta |
| `supervisor.scope:{resource}` | `SupervisorHeadquartersScope.php` | Filtra por sede del supervisor |
| `visit_destination.access:{ability}` | `EnsureVisitDestinationAccess.php` | CRUD/cola destinos |
| `installed` | `Installed.php` | App instalada |
| `frontend` | `FrontEnd.php` | Rutas kiosco |

## 7.4 Permisos Spatie (principales)

```
dashboard
visitors, visitors_create, visitors_edit, visitors_delete, visitors_show
employees, employees_create, ...
pre-registers, ...
departments, designations, headquarters, regions
admin-visitor-report, attendance, setting, role, adminusers
visit-destinations, visit-destinations_create, visit-destinations_edit, visit-destinations_delete
visit-destination-queue
```

Permisos destinos añadidos por migración `2026_06_23_110000_*`.

## 7.5 Scope por sede (supervisor)

El middleware `supervisor.scope` aplica a:

- `designations`, `departments`, `employees`
- `pre-registers`, `visitors`, `reports`

Filtra consultas por `headquarters_id` del empleado vinculado al usuario supervisor/recepción.

## 7.6 Accesos especiales

### Cola por destino

- Requiere destinos asignados en **usuario** o en **empleado vinculado**
- Verificado en `VisitDestinationService::userCanAccessDestinationQueue`

### Carga CDI (sin login)

- Rutas públicas bajo `/admin/internal-staff-data`
- Diseñado para intranet; **no exponer a internet público**
- Validaciones en `InternalStaffDataController`

### Destinos CRUD

- Crear/editar: Admin o supervisor
- Eliminar: **solo Admin** y solo si no hay visitas vinculadas

## 7.7 CSRF y sesiones

- Formularios web protegidos con token CSRF
- Sesiones en `storage/framework/sessions`
- Cookies encriptadas (`EncryptCookies` middleware)

## 7.8 Auditoría

Modelos con `HasAuditColumn` registran usuario creador/editor en cambios.

## 7.9 Recomendaciones de hardening

| Acción | Prioridad |
|--------|-----------|
| Cambiar credenciales seed admin | Alta |
| `APP_DEBUG=false` en producción | Alta |
| Restringir `/cdi` por firewall intranet | Alta |
| Activar Sentry para alertas | Media |
| Revisar permisos Reception periódicamente | Media |
| No commitear `.env` | Alta |
| Backup BD cifrado off-site | Alta |

## 7.8 Diagnóstico de permisos

```bash
php artisan check:user-permissions {user_id}
```

[← Flujos](./06-flujos-trabajo.md) · [Siguiente: Comandos →](./08-comandos-mantenimiento.md)

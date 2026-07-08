# 5. Módulos funcionales

[← Índice](./README.md)

## 5.1 Mapa de módulos

| Módulo | Ruta base | Controlador | Permiso Spatie |
|--------|-----------|-------------|----------------|
| Dashboard | `/admin/dashboard` | `DashboardController` | `dashboard` |
| Visitantes | `/admin/visitors` | `VisitorController` | `visitors*` |
| Empleados | `/admin/employees` | `EmployeeController` | `employees*` |
| Pre-registros | `/admin/pre-registers` | `PreRegisterController` | `pre-registers*` |
| Departamentos | `/admin/departments` | `DepartmentsController` | `departments*` |
| Cargos | `/admin/designations` | `DesignationsController` | `designations*` |
| Sedes | `/admin/headquarters` | `HeadquartersController` | `headquarters*` |
| Asistencia | `/admin/attendance` | `AttendanceController` | `attendance*` |
| Reporte visitas | `/admin/admin-visitor-report` | `VisitorReportController` | `admin-visitor-report` |
| Reporte pre-registros | `/admin/admin-pre-registers-report` | `PreRegistersReportController` | — |
| Reporte asistencia | `/admin/attendance-report` | `AttendanceReportController` | — |
| Usuarios admin | `/admin/adminusers` | `AdminUserController` | `adminusers*` |
| Roles | `/admin/role` | `RoleController` | `role*` |
| Configuración | `/admin/setting` | `SettingController` | `setting` |
| Idiomas | `/admin/language` | `LanguageController` | — |
| Destinos visita | `/admin/visit-destinations` | `VisitDestinationController` | `visit-destinations*` |
| Cola destinos | `/admin/visit-destination-queue` | `VisitDestinationQueueController` | `visit-destination-queue` |
| Carga funcionarios | `/admin/internal-staff-data` | `InternalStaffDataController` | Público (intranet) |

## 5.2 Kiosco público (frontend)

Middleware: `installed`, `frontend`

| Función | Ruta | Método |
|---------|------|--------|
| Inicio / check-in | `/`, `/check-in` | GET |
| Paso 1 — datos | `/check-in/create-step-one` | GET/POST |
| Paso 2 — foto | `/check-in/create-step-two` | GET/POST |
| Visitante recurrente | `/check-in/return` | GET/POST |
| Pre-registrado | `/check-in/pre-registered` | GET/POST |
| Comprobante | `/check-in/show/{id}` | GET |
| Checkout | `/checkout` | GET/POST |
| Confirmar salida | `/checkout/update/{id}` | GET |
| QR público | `/qrcode/{number}` | GET |
| Términos | `/terms_and_conditions` | GET |

**Controladores:** `CheckInController`, `CheckoutController`, `FrontendController`

## 5.3 Panel de visitantes (admin)

Funcionalidades en `VisitorController`:

- Listado DataTables con filtros por sede (supervisor scope)
- Registro manual de visitas
- Cambio de estado (Pendiente/Aceptado/Rechazado)
- Checkout manual desde panel
- Bloqueo de visitante
- Búsqueda por cédula en header ("Introduce CI del Visitante" → Salida)

**Scope supervisor:** middleware `supervisor.scope:visitors` limita datos a `headquarters_id` del empleado vinculado al usuario.

## 5.4 Empleados y funcionarios

- CRUD en `/admin/employees`
- Vinculación `User` ↔ `Employee`
- Asignación de **destinos de visita** en crear/editar empleado (recepcionistas)
- Empleado ficticio **ATENCION CONTRIBUYENTE** (`employee_id=99`) para cola de contribuyentes

## 5.5 Pre-registros

- Invitación previa por empleado
- Flujo en kiosco: `/check-in/pre-registered`
- Reporte dedicado en admin

## 5.6 Sedes, regiones y dependencias

- **Sedes:** CRUD completo (`HeadquartersController`)
- **Regiones:** datos vía seeders; menú existe pero **sin CRUD web** actualmente
- **Dependencias:** seeders; usadas en relaciones regionales

## 5.7 Reportes

| Reporte | Filtros típicos |
|---------|-----------------|
| Visitantes | Rango fechas, sede, empleado |
| Pre-registros | Fechas, estado |
| Asistencia | Fechas, empleado |

Exportación vía formularios POST en vistas admin.

## 5.8 Configuración del sistema

Rutas bajo `/admin/setting/`:

| Sección | Descripción |
|---------|-------------|
| Site | Nombre, logo, timezone |
| SMS | Twilio |
| FCM | Push notifications |
| Email | SMTP |
| Notification | Plantillas y toggles |
| Homepage | Textos kiosco |
| WhatsApp | Mensajería opcional |

## 5.9 Carga interna de funcionarios (CDI)

- **URL:** `/admin/internal-staff-data` o `/cdi`
- **Sin login** — acceso por intranet
- Individual o **Excel masivo**
- Servicio: `InternalStaffImportService`
- Tests: `tests/Feature/InternalStaffDataProductionSafeTest.php`

## 5.10 API REST móvil

Base: `/api/v1/`

| Grupo | Endpoints |
|-------|-----------|
| Auth | `POST login`, `POST logout`, `GET me`, refresh token |
| Visitantes | CRUD, check-in/out, change-status |
| Pre-registros | CRUD, búsqueda |
| Empleados | listado, detalle |
| Asistencia | clock-in/out |
| FCM | subscribe/unsubscribe |
| Settings | configuración app |

Autenticación: **JWT** (`auth:api` middleware).

## 5.11 Menú lateral dinámico

- Tabla `backend_menus` + permisos Spatie
- Composer: `BackendMenuComposer`
- Fallback para Admin/supervisor en ítems específicos (ej. destinos)

[← Base de datos](./04-base-de-datos.md) · [Siguiente: Flujos →](./06-flujos-trabajo.md)

# 1. Introducción

[← Índice](./README.md)

## 1.1 ¿Qué es este sistema?

El **Sistema de Gestión de Visitas del SENIAT** es una aplicación web intranet para:

- Registrar la **entrada y salida** de visitantes en sedes del SENIAT.
- Gestionar **funcionarios anfitriones**, departamentos, cargos y sedes.
- Emitir **códigos QR** y comprobantes de visita.
- Generar **reportes** de visitas, pre-registros y asistencia.
- Notificar a empleados y recepcionistas mediante **email, SMS y push (FCM)**. **NO FUNCIONANDO**
- Operar una **cola por destino** para recepciones secundarias (ej. Atención al Contribuyente).

## 1.2 Usuarios del sistema

| Perfil | Interacción |
|--------|-------------|
| **Visitante** | Usa el kiosco público (`/check-in`, `/checkout`) sin cuenta |
| **Recepcionista** | Panel admin: registro manual, salidas rápidas, cola por destino |
| **Supervisor de sede** | Admin limitado a su sede: visitantes, empleados, reportes |
| **Administrador TI** | Configuración, roles, destinos, integraciones |
| **Funcionario anfitrión** | Recibe visitas; puede usar app móvil vía API JWT |
| **Personal RRHH / CDI** | Carga de funcionarios vía formulario intranet |

## 1.3 Componentes principales

```
┌─────────────────────────────────────────────────────────────┐
│                    Navegador / App móvil                     │
├──────────────────────┬──────────────────────────────────────┤
│  Kiosco (Blade)      │  Panel Admin (Blade + DataTables)    │
│  /check-in           │  /admin/*                             │
│  /checkout           │                                       │
├──────────────────────┴──────────────────────────────────────┤
│              Laravel 9 (PHP 8.1) — /var/www/html             │
│  Controllers · Services · Models · Middleware · Jobs         │
├─────────────────────────────────────────────────────────────┤
│                    PostgreSQL + storage/                     │
└─────────────────────────────────────────────────────────────┘
```

## 1.4 Conceptos de negocio

| Concepto | Descripción |
|----------|-------------|
| **Visitante (`visitors`)** | Persona física identificada por cédula; datos maestros reutilizables |
| **Visita (`visiting_details`)** | Cada evento de entrada: empleado destino, sede, motivo, hora entrada/salida |
| **Pre-registro (`pre_registers`)** | Invitación previa de un empleado a un visitante |
| **Empleado ficticio** | Registro especial (ej. `employee_id=99` = ATENCION CONTRIBUYENTE) para destinos operativos sin persona física |
| **Destino de visita** | Área lógica (piso, módulo) con reglas de enrutamiento y cola propia |
| **Registro diario (`reg_no`)** | Número correlativo del día para checkout y QR |

## 1.5 Estados de una visita

| Valor | Clave interna | Texto en UI (es) |
|-------|---------------|------------------|
| 1 | `VisitorStatus::PENDDING` | Pendiente |
| 2 | `VisitorStatus::ACCEPT` | Aceptado |
| 3 | `VisitorStatus::REJECT` | Rechazado |

Archivo: `app/Enums/VisitorStatus.php` · Traducciones: `lang/es/visitor_statuses.php`

## 1.6 Idiomas

- Español (`es`) e inglés (`en`) en `lang/`
- Cambio de idioma en admin: `/admin/lang/{locale}`
- Middleware `Localization` aplica locale por sesión

## 1.7 Alcance de esta documentación

Esta serie de documentos cubre el **código desplegado en producción** en `/var/www/html`. Complementa (no reemplaza) el [`README.md`](../README.md) de instalación desde cero.

[Siguiente: Arquitectura →](./02-arquitectura.md)

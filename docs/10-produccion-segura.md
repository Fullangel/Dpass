# 10. Operación segura en producción

[← Índice](./README.md)

> Guía para administradores y desarrolladores: qué hacer correctamente y qué evitar para no afectar el servicio en `visitas.seniat.gob.ve`.

## 10.1 Reglas de oro

| ✅ Hacer | ❌ No hacer |
|----------|-------------|
| Backup BD antes de migraciones | `migrate:fresh`, `migrate:rollback` masivo |
| `--dry-run` en imports y backfill | Importar Excel sin revisar en staging |
| `php artisan config:clear` tras `.env` | Dejar `APP_DEBUG=true` en prod |
| Probar cambios en horario de baja actividad | `php artisan tinker` en prod como usuario personal |
| Usar `--force` solo en migrate planificado | Borrar archivos de `storage/` manualmente |
| Documentar cambios en Git | Editar vendor/ o core Laravel |
| Asignar destinos desde admin UI | Hardcodear IDs en BD sin registro |

## 10.2 Checklist de despliegue de cambios

```text
□ Backup PostgreSQL completado
□ git pull / rsync código
□ composer install --no-dev --optimize-autoloader
□ php artisan migrate --force (si hay migraciones)
□ php artisan config:clear && php artisan view:clear
□ Verificar permisos storage (www-data)
□ Smoke test: kiosco /check-in
□ Smoke test: admin login + listado visitantes
□ Smoke test: cola por destino (si aplica)
□ Revisar laravel.log por 15 min
```

## 10.3 Feature flags

| Flag | Ubicación | Efecto |
|------|-----------|--------|
| `VISIT_DESTINATIONS_ENABLED` | `.env` → `config/visit_destinations.php` | `false`: cola funciona, **sin push** a recepcionistas destino. `true`: activa notificaciones push. |

La asignación de `visit_destination_id` en visitas nuevas **siempre ocurre** (independiente del flag).

## 10.4 Gestión de destinos sin romper datos

### Crear nuevo destino

1. Admin → **Destinos de visita** → Crear
2. Definir sede, nombre, reglas (employee_id, etc.)
3. Asignar recepcionistas con rol **Reception**
4. Opcional: backfill histórico con `--dry-run` primero

### No eliminar destinos con visitas

El sistema bloquea eliminación si existen filas en `visiting_details` con ese destino.

### Empleado ficticio

Para destinos operativos (contribuyentes, mesa de ayuda), crear empleado dedicado y regla `employee_id` apuntando a él.

## 10.5 Permisos storage (obligatorio)

Después de cualquier operación CLI como usuario no-www-data:

```bash
sudo chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
sudo chmod -R ug+rwx /var/www/html/storage /var/www/html/bootstrap/cache
```

## 10.6 Rollback de código

1. Restaurar commit anterior vía Git
2. `composer install --no-dev`
3. **No** revertir migraciones si ya aplicaron datos — evaluar migración compensatoria
4. `php artisan config:clear`

## 10.7 Ventanas de mantenimiento

1. Avisar a recepción
2. Opcional: `php artisan down` (modo mantenimiento Laravel)
3. Ejecutar cambios
4. `php artisan up`
5. Verificar kiosco y cola

## 10.8 Datos sensibles

| Dato | Tratamiento |
|------|-------------|
| `.env` | Nunca en Git; permisos 640 |
| Fotos visitantes | `storage/app`, acceso vía media library |
| Logs | Pueden contener IPs; rotar periódicamente |
| Backups BD | Cifrar en reposo |

## 10.9 Contactos operativos sugeridos

Documentar internamente:

- Administrador BD PostgreSQL
- Administrador Apache/SSL
- Responsable recepción (validación funcional)
- Desarrollador/mantenimiento aplicación

[← Integraciones](./09-integraciones.md) · [Siguiente: Destinos de visita →](./11-destinos-visita.md)

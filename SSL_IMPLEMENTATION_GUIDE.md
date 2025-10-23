# Guía de Implementación SSL - Sistema Quick Pass

## 📋 Resumen de la Auditoría SSL

### ✅ Certificados Optimizados
Se ha realizado una auditoría exhaustiva y optimización de los certificados SSL:

**Certificados Mantenidos:**
- `ca.cert.pem` - Certificado de la Autoridad Certificadora (CA)
- `intranet.seniat.local.cert.pem` - Certificado del servidor principal
- `seniat.visitas.cert.pem` - Certificado con múltiples SAN (Subject Alternative Names)

**Archivos Eliminados:**
- Certificados duplicados y obsoletos
- Archivos de respaldo innecesarios (`*.old`)
- Archivos CSR duplicados

## 🔍 Análisis del Error "Invalid request (Unsupported SSL request)"

### Causa Identificada:
El error ocurre cuando se intenta acceder mediante HTTPS (`https://127.0.0.1:8000`) a un servidor que solo está configurado para HTTP. El servidor PHP interno recibe una solicitud SSL que no puede procesar.

### Solución Inmediata:
1. Usar HTTP en lugar de HTTPS: `http://127.0.0.1:8000`
2. O configurar un servidor web real (Apache/Nginx) con SSL

## 🔒 Configuración de Seguridad SSL Recomendada

### 1. Protocolos Seguros (Deshabilitar versiones vulnerables)
```apache
SSLProtocol -all +TLSv1.2 +TLSv1.3
```

### 2. Cifrados Seguros
```apache
SSLCipherSuite ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384
```

### 3. Headers de Seguridad
- Strict-Transport-Security (HSTS)
- X-Content-Type-Options
- X-Frame-Options
- X-XSS-Protection
- Referrer-Policy

## 🚀 Implementación Paso a Paso

### Opción A: Configuración con Apache

1. **Instalar Apache y módulos SSL:**
```bash
sudo apt update
sudo apt install apache2
sudo a2enmod ssl
sudo a2enmod headers
sudo systemctl restart apache2
```

2. **Crear archivo de configuración SSL:**
```bash
sudo nano /etc/apache2/sites-available/intranet-ssl.conf
```

3. **Copiar la configuración del archivo `config/ssl-config.php`**

4. **Habilitar el sitio:**
```bash
sudo a2ensite intranet-ssl
sudo systemctl reload apache2
```

### Opción B: Configuración con Nginx

1. **Instalar Nginx:**
```bash
sudo apt update
sudo apt install nginx
```

2. **Crear archivo de configuración SSL:**
```bash
sudo nano /etc/nginx/sites-available/intranet-ssl
```

3. **Copiar la configuración del archivo `config/ssl-config.php`**

4. **Habilitar el sitio:**
```bash
sudo ln -s /etc/nginx/sites-available/intranet-ssl /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

## ⚙️ Configuración de la Aplicación

### Actualizar variables de entorno (`.env`):
```env
APP_URL=https://intranet.seniat.local
APP_ENV=production
APP_DEBUG=false
```

### Configuración de base de datos con SSL:
```env
MYSQL_ATTR_SSL_CA=/var/www/html/ssl-ca/ca.cert.pem
```

## 🧪 Verificación de la Implementación

### 1. Pruebas de conectividad:
```bash
# Verificar puerto 443
sudo netstat -tlnp | grep :443

# Probar conexión SSL
openssl s_client -connect intranet.seniat.local:443 -showcerts
```

### 2. Herramientas de validación:
- [SSL Labs Test](https://www.ssllabs.com/ssltest/)
- [Security Headers](https://securityheaders.com/)
- [SSL Checker](https://www.sslchecker.com/sslchecker)

## 🛡️ Mejores Prácticas de Seguridad Implementadas

### ✅ Protocolos Seguros:
- ✅ TLS 1.2 y TLS 1.3 habilitados
- ❌ SSLv2, SSLv3, TLS 1.0, TLS 1.1 deshabilitados

### ✅ Cifrados Robustos:
- Solo cifrados de alta seguridad (AES-256-GCM, CHACHA20-POLY1305)
- Forward secrecy con ECDHE

### ✅ Headers de Seguridad:
- HSTS con preload
- Protección contra clickjacking
- Prevención de XSS

### ✅ Características Avanzadas:
- OCSP Stapling habilitado
- Compresión SSL deshabilitada
- Session resumption optimizada

## 📁 Archivos de Configuración Creados

1. **`config/ssl-security.php`** - Configuración de seguridad SSL
2. **`config/ssl-config.php`** - Configuraciones para Apache/Nginx
3. **Este archivo** - Guía de implementación

## ⚠️ Notas Importantes

1. **Entorno de Desarrollo vs Producción:**
   - En desarrollo: usar HTTP (`php -S 127.0.0.1:8000`)
   - En producción: configurar SSL con Apache/Nginx

2. **Renovación de Certificados:**
   - Los certificados actuales expiran el 19/10/2035
   - Implementar renovación automática con Let's Encrypt si es posible

3. **Monitoreo:**
   - Configurar alertas para expiración de certificados
   - Monitorear logs de SSL regularmente

## 🎯 Siguientes Pasos

1. Elegir servidor web (Apache o Nginx)
2. Implementar configuración SSL
3. Actualizar DNS/aplicación para usar HTTPS
4. Realizar pruebas de seguridad
5. Monitorear rendimiento y seguridad

---

**Última actualización:** Octubre 2025
**Versión:** 1.0
**Responsable:** Sistema Quick Pass
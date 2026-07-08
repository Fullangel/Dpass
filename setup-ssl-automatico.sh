#!/bin/bash
# =============================================================================
# Script de Configuración SSL Automatizada para Entornos de Desarrollo
# =============================================================================
# Este script realiza una configuración SSL completa y automatizada:
# 1. Genera un certificado SAN (Subject Alternative Name) autofirmado.
# 2. Configura el archivo /etc/hosts para la resolución de DNS local.
# 3. Resuelve el problema de que Apache sirva `index.html` en lugar de la app.
# 4. Crea y habilita un VirtualHost SSL en Apache.
# 5. Actualiza el .env de Laravel con los valores correctos.
# 6. Realiza pruebas para verificar que el sitio funciona correctamente.
# =============================================================================

set -e # Salir inmediatamente si un comando falla.

# --- Configuración ---
DOMAIN="seniat-visitas"
PROJECT_ROOT="/var/www/html"
SSL_DIR="/var/www/ssl-ca"

# Información para el certificado
COUNTRY="VE"
STATE="Distrito Capital"
CITY="Caracas"
ORG="SENIAT"
OU="TI"

# --- Colores para la salida ---
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # Sin Color

# --- Funciones de Log ---
log_info() { echo -e "${BLUE}[INFO]${NC} $1"; }
log_success() { echo -e "${GREEN}[SUCCESS]${NC} $1"; }
log_warning() { echo -e "${YELLOW}[WARNING]${NC} $1"; }
log_error() { echo -e "${RED}[ERROR]${NC} $1"; exit 1; }

# --- Funciones Principales ---

check_requirements() {
    log_info "Verificando requisitos del sistema (sudo, openssl, apache2)..."
    if [[ $EUID -ne 0 ]]; then
        log_error "Este script debe ejecutarse como root o con sudo."
    fi
    command -v openssl >/dev/null 2>&1 || log_error "OpenSSL no está instalado."
    command -v apache2 >/dev/null 2>&1 || log_error "Apache2 no está instalado."
    log_success "Requisitos cumplidos."
}

setup_directories() {
    log_info "Creando estructura de directorios para SSL en $SSL_DIR..."
    mkdir -p "$SSL_DIR/certs" "$SSL_DIR/private"
    chmod 700 "$SSL_DIR/private"
    log_success "Directorios SSL creados."
}

generate_san_cert() {
    log_info "Generando certificado SAN para $DOMAIN, localhost y *.localhost..."
    local KEY_FILE="$SSL_DIR/private/$DOMAIN.key"
    local CERT_FILE="$SSL_DIR/certs/$DOMAIN.crt"
    local OPENSSL_CNF="$SSL_DIR/openssl.cnf"

    cat > "$OPENSSL_CNF" <<EOF
[req]
default_bits       = 2048
prompt             = no
distinguished_name = dn
req_extensions     = v3_req

[dn]
C  = $COUNTRY
ST = $STATE
L  = $CITY
O  = $ORG
OU = $OU
CN = $DOMAIN

[v3_req]
subjectAltName = @alt_names

[alt_names]
DNS.1 = $DOMAIN
DNS.2 = localhost
DNS.3 = $DOMAIN.localhost
EOF

    openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
        -keyout "$KEY_FILE" \
        -out "$CERT_FILE" \
        -config "$OPENSSL_CNF"

    log_success "Certificado SAN y clave generados:"
    log_info "  - Certificado: $CERT_FILE"
    log_info "  - Clave: $KEY_FILE"
}

update_hosts_file() {
    log_info "Actualizando /etc/hosts para resolución local..."
    local entry="127.0.0.1 $DOMAIN $DOMAIN.localhost localhost"
    if grep -q "$DOMAIN" /etc/hosts; then
        log_warning "La entrada para '$DOMAIN' ya existe en /etc/hosts. Omitiendo."
    else
        echo "$entry" >> /etc/hosts
        log_success "Añadido '$entry' a /etc/hosts."
    fi
}

remove_public_index_html() {
    local index_html="$PROJECT_ROOT/public/index.html"
    if [ -f "$index_html" ]; then
        log_warning "Se encontró '$index_html', que interfiere con Laravel."
        mv "$index_html" "${index_html}.bak"
        log_success "Se ha renombrado a '${index_html}.bak' para servir la aplicación Laravel."
    fi
}

configure_apache_vhost() {
    log_info "Configurando VirtualHost de Apache para SSL..."
    local APACHE_CONF="/etc/apache2/sites-available/$DOMAIN-ssl.conf"

    tee "$APACHE_CONF" > /dev/null <<EOF
<VirtualHost *:443>
    ServerName $DOMAIN
    ServerAlias localhost $DOMAIN.localhost
    DocumentRoot $PROJECT_ROOT/public

    SSLEngine on
    SSLCertificateFile $SSL_DIR/certs/$DOMAIN.crt
    SSLCertificateKeyFile $SSL_DIR/private/$DOMAIN.key

    <Directory $PROJECT_ROOT/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/${DOMAIN}-error.log
    CustomLog \${APACHE_LOG_DIR}/${DOMAIN}-access.log combined
</VirtualHost>

<VirtualHost *:80>
    ServerName $DOMAIN
    ServerAlias localhost $DOMAIN.localhost
    Redirect permanent / https://\${HTTP_HOST}/
</VirtualHost>
EOF

    a2enmod ssl headers rewrite
    a2ensite "$DOMAIN-ssl"
    a2dissite 000-default || true

    log_info "Probando configuración de Apache..."
    if apache2ctl configtest; then
        systemctl reload apache2
        log_success "VirtualHost SSL configurado y Apache recargado."
    else
        log_error "La configuración de Apache falló. Revisa los errores."
    fi
}

update_laravel_env() {
    log_info "Actualizando el archivo .env de Laravel..."
    local ENV_FILE="$PROJECT_ROOT/.env"
    local app_url="https://seniat-visitas.localhost"

    if [ -f "$ENV_FILE" ]; then
        # Usamos sed con un delimitador diferente para evitar conflictos con las barras de la URL
        if grep -q "^APP_URL=" "$ENV_FILE"; then
            sed -i "s|^APP_URL=.*|APP_URL=$app_url|" "$ENV_FILE"
        else
            echo "APP_URL=$app_url" >> "$ENV_FILE"
        fi
        
        # Añadir FORCE_HTTPS si no existe, si no, la actualiza
        if grep -q "^FORCE_HTTPS=" "$ENV_FILE"; then
            sed -i "s|^FORCE_HTTPS=.*|FORCE_HTTPS=true|" "$ENV_FILE"
        else
            echo "FORCE_HTTPS=true" >> "$ENV_FILE"
        fi

        cd "$PROJECT_ROOT"
        php artisan config:clear 2>/dev/null || true
        php artisan route:clear 2>/dev/null || true
        php artisan cache:clear 2>/dev/null || true
        log_success "Archivo .env actualizado y caché de Laravel limpiada."
    else
        log_warning "No se encontró el archivo .env. Asegúrate de configurarlo manually."
    fi
    
    echo ""
    log_info "La URL para tu archivo .env es: APP_URL=$app_url"
    echo ""
}

run_tests() {
    log_info "Ejecutando pruebas de conectividad..."
    local url_to_test="https://seniat-visitas.localhost"
    
    # Espera un momento para que Apache se recargue completamente
    sleep 2
    
    # Usamos --insecure porque el certificado es autofirmado
    # Usamos -L para seguir redirecciones, -I para obtener solo headers
    if curl --insecure -LI "$url_to_test" 2>/dev/null | grep -E -q "HTTP/(2|1\.1) 200"; then
        log_success "Prueba de conexión a $url_to_test exitosa (HTTP 200 OK)."
    else
        log_error "La prueba de conexión a $url_to_test falló."
        curl -vkI "$url_to_test"
    fi
}

show_summary() {
    local app_url="https://seniat-visitas.localhost"
    echo ""
    log_success "¡Configuración SSL completada!"
    echo -e "${YELLOW}===============================================================${NC}"
    echo "Puedes acceder a tu aplicación en las siguientes URLs:"
    echo "  - https://seniat-visitas"
    echo "  - https://localhost"
    echo "  - $app_url (Recomendada para desarrollo)"
    echo ""
    echo "Detalles:"
    echo "  - El archivo /etc/hosts ha sido modificado para resolver estos dominios."
    echo "  - Se ha generado un certificado SAN autofirmado."
    echo "  - El archivo public/index.html fue renombrado a index.html.bak."
    echo "  - El valor recomendado para APP_URL en tu .env es: $app_url"
    echo -e "${YELLOW}===============================================================${NC}"
    echo "NOTA: Tu navegador mostrará una advertencia de seguridad. Debes aceptarla para continuar."
}

main() {
    check_requirements
    setup_directories
    generate_san_cert
    update_hosts_file
    remove_public_index_html
    configure_apache_vhost
    update_laravel_env
    run_tests
    show_summary
}

main "$@"
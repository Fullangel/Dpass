#!/bin/bash

# =============================================================================
# Script de Configuración SSL Automatizada para SENIAT Visitor Management
# =============================================================================
# Este script automatiza la generación y configuración de certificados SSL
# para el sistema de gestión de visitas en entorno de intranet empresarial
# =============================================================================

set -e  # Salir si hay algún error

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Variables de configuración
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
SSL_DIR="$PROJECT_ROOT/ssl-ca"
CERTS_DIR="$SSL_DIR/certs"
PRIVATE_DIR="$SSL_DIR/private"
CSR_DIR="$SSL_DIR/csr"
CONFIG_DIR="$PROJECT_ROOT/config"

# Configuración por defecto
DEFAULT_DOMAIN="intranet.seniat.local"
DEFAULT_COUNTRY="VE"
DEFAULT_STATE="Distrito Capital"
DEFAULT_CITY="Caracas"
DEFAULT_ORG="SENIAT"
DEFAULT_OU="TI"
DEFAULT_DAYS=365
DEFAULT_KEY_SIZE=2048

# Funciones auxiliares
log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Función para mostrar banner
show_banner() {
    echo -e "${BLUE}"
    echo "╔══════════════════════════════════════════════════════════════════════════════╗"
    echo "║           CONFIGURACIÓN SSL AUTOMATIZADA - SENIAT VISITOR MGMT           ║"
    echo "╚══════════════════════════════════════════════════════════════════════════════╝"
    echo -e "${NC}"
    echo ""
}

# Función para verificar requisitos
check_requirements() {
    log_info "Verificando requisitos del sistema..."
    
    # Verificar OpenSSL
    if ! command -v openssl &> /dev/null; then
        log_error "OpenSSL no está instalado. Instalando..."
        sudo apt-get update && sudo apt-get install -y openssl
    fi
    
    # Verificar Apache
    if ! command -v apache2 &> /dev/null && ! command -v httpd &> /dev/null; then
        log_warning "Apache no detectado. Asegúrate de instalarlo antes de continuar."
    fi
    
    # Verificar permisos
    if [[ $EUID -ne 0 ]]; then
        log_warning "Este script debe ejecutarse con sudo para configurar Apache."
        SUDO_REQUIRED=true
    else
        SUDO_REQUIRED=false
    fi
    
    log_success "Verificación de requisitos completada"
}

# Función para crear estructura de directorios
setup_directories() {
    log_info "Configurando estructura de directorios SSL..."
    
    # Crear directorios necesarios
    mkdir -p "$CERTS_DIR"
    mkdir -p "$PRIVATE_DIR"
    mkdir -p "$CSR_DIR"
    mkdir -p "$SSL_DIR/newcerts"
    mkdir -p "$SSL_DIR/crl"
    
    # Establecer permisos seguros
    chmod 700 "$PRIVATE_DIR"
    chmod 755 "$CERTS_DIR"
    chmod 755 "$CSR_DIR"
    
    log_success "Estructura de directorios creada"
}

# Función para obtener configuración del usuario
get_user_config() {
    echo ""
    log_info "Configuración del certificado SSL"
    echo "=================================="
    echo ""
    
    # Dominio
    read -p "Nombre de dominio [$DEFAULT_DOMAIN]: " DOMAIN
    DOMAIN=${DOMAIN:-$DEFAULT_DOMAIN}
    
    # País
    read -p "Código de país (2 letras) [$DEFAULT_COUNTRY]: " COUNTRY
    COUNTRY=${COUNTRY:-$DEFAULT_COUNTRY}
    
    # Estado/Provincia
    read -p "Estado/Provincia [$DEFAULT_STATE]: " STATE
    STATE=${STATE:-$DEFAULT_STATE}
    
    # Ciudad
    read -p "Ciudad [$DEFAULT_CITY]: " CITY
    CITY=${CITY:-$DEFAULT_CITY}
    
    # Organización
    read -p "Organización [$DEFAULT_ORG]: " ORG
    ORG=${ORG:-$DEFAULT_ORG}
    
    # Unidad organizativa
    read -p "Unidad Organizativa [$DEFAULT_OU]: " OU
    OU=${OU:-$DEFAULT_OU}
    
    # Días de validez
    read -p "Días de validez del certificado [$DEFAULT_DAYS]: " DAYS
    DAYS=${DAYS:-$DEFAULT_DAYS}
    
    # Tamaño de clave
    read -p "Tamaño de clave RSA [$DEFAULT_KEY_SIZE]: " KEY_SIZE
    KEY_SIZE=${KEY_SIZE:-$DEFAULT_KEY_SIZE}
    
    echo ""
    log_info "Tipo de certificado:"
    echo "1) Certificado autofirmado (recomendado para desarrollo)"
    echo "2) Solicitud CSR para CA interna (recomendado para producción)"
    read -p "Selecciona opción [1]: " CERT_TYPE
    CERT_TYPE=${CERT_TYPE:-1}
    
    # Configuración de Apache
    echo ""
    read -p "¿Configurar Apache automáticamente? [S/n]: " CONFIG_APACHE
    CONFIG_APACHE=${CONFIG_APACHE:-S}
    
    # Configuración de renovación automática
    echo ""
    read -p "¿Configurar renovación automática? [S/n]: " AUTO_RENEW
    AUTO_RENEW=${AUTO_RENEW:-S}
}

# Función para generar certificado autofirmado
generate_self_signed_cert() {
    log_info "Generando certificado autofirmado..."
    
    local KEY_FILE="$PRIVATE_DIR/${DOMAIN}.key"
    local CERT_FILE="$CERTS_DIR/${DOMAIN}.crt"
    local CSR_FILE="$CSR_DIR/${DOMAIN}.csr"
    
    # Generar clave privada
    openssl genrsa -out "$KEY_FILE" "$KEY_SIZE"
    chmod 600 "$KEY_FILE"
    
    # Generar CSR
    openssl req -new -key "$KEY_FILE" -out "$CSR_FILE" \
        -subj "/C=$COUNTRY/ST=$STATE/L=$CITY/O=$ORG/OU=$OU/CN=$DOMAIN"
    
    # Generar certificado autofirmado
    openssl x509 -req -days "$DAYS" -in "$CSR_FILE" \
        -signkey "$KEY_FILE" -out "$CERT_FILE"
    
    # Establecer permisos
    chmod 644 "$CERT_FILE"
    
    log_success "Certificado autofirmado generado:"
    log_info "  - Clave privada: $KEY_FILE"
    log_info "  - Certificado: $CERT_FILE"
    log_info "  - CSR: $CSR_FILE"
}

# Función para generar CSR para CA interna
generate_csr() {
    log_info "Generando solicitud CSR para CA interna..."
    
    local KEY_FILE="$PRIVATE_DIR/${DOMAIN}.key"
    local CSR_FILE="$CSR_DIR/${DOMAIN}.csr"
    local CONFIG_FILE="$SSL_DIR/openssl.cnf"
    
    # Crear configuración OpenSSL
    cat > "$CONFIG_FILE" <<EOF
[ req ]
default_bits = $KEY_SIZE
distinguished_name = req_distinguished_name
req_extensions = v3_req
prompt = no

[ req_distinguished_name ]
C = $COUNTRY
ST = $STATE
L = $CITY
O = $ORG
OU = $OU
CN = $DOMAIN

[ v3_req ]
keyUsage = keyEncipherment, dataEncipherment
extendedKeyUsage = serverAuth
subjectAltName = @alt_names

[ alt_names ]
DNS.1 = $DOMAIN
DNS.2 = *.${DOMAIN#*.}
IP.1 = 127.0.0.1
EOF
    
    # Generar clave privada
    openssl genrsa -out "$KEY_FILE" "$KEY_SIZE"
    chmod 600 "$KEY_FILE"
    
    # Generar CSR con configuración
    openssl req -new -key "$KEY_FILE" -out "$CSR_FILE" -config "$CONFIG_FILE"
    
    log_success "CSR generado para CA interna:"
    log_info "  - Clave privada: $KEY_FILE"
    log_info "  - Solicitud CSR: $CSR_FILE"
    log_warning "Entrega el archivo CSR a tu equipo de seguridad para firmarlo"
}

# Función para configurar Apache
configure_apache() {
    if [[ "$CONFIG_APACHE" != "S" && "$CONFIG_APACHE" != "s" ]]; then
        log_info "Configuración de Apache omitida"
        return
    fi
    
    log_info "Configurando Apache para SSL..."
    
    # Habilitar módulos necesarios
    sudo a2enmod ssl
    sudo a2enmod headers
    sudo a2enmod rewrite
    
    # Ruta del archivo de configuración
    local APACHE_CONF="/etc/apache2/sites-available/${DOMAIN}-ssl.conf"
    
    # Crear configuración Apache
    sudo tee "$APACHE_CONF" > /dev/null <<EOF
<VirtualHost *:443>
    ServerName $DOMAIN
    DocumentRoot $PROJECT_ROOT/public
    
    # Configuración SSL
    SSLEngine on
    SSLCertificateFile $CERTS_DIR/${DOMAIN}.crt
    SSLCertificateKeyFile $PRIVATE_DIR/${DOMAIN}.key
    
    # Configuración de seguridad SSL
    SSLProtocol -all +TLSv1.2 +TLSv1.3
    SSLCipherSuite ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384:ECDHE-ECDSA-CHACHA20-POLY1305:ECDHE-RSA-CHACHA20-POLY1305:DHE-RSA-AES128-GCM-SHA256:DHE-RSA-AES256-GCM-SHA384
    SSLHonorCipherOrder on
    SSLCompression off
    
    # Headers de seguridad
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains; preload"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    
    # Configuración de directorio
    <Directory $PROJECT_ROOT/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    # Logs
    ErrorLog \${APACHE_LOG_DIR}/${DOMAIN}-error.log
    CustomLog \${APACHE_LOG_DIR}/${DOMAIN}-access.log combined
</VirtualHost>

# Redirección HTTP a HTTPS
<VirtualHost *:80>
    ServerName $DOMAIN
    Redirect permanent / https://$DOMAIN/
</VirtualHost>
EOF
    
    # Desactivar sitio por defecto
    sudo a2dissite 000-default 2>/dev/null || true
    
    # Activar nuevo sitio
    sudo a2ensite "${DOMAIN}-ssl"
    
    # Verificar configuración
    if sudo apache2ctl configtest; then
        log_success "Configuración de Apache verificada"
    else
        log_error "Error en la configuración de Apache"
        exit 1
    fi
    
    # Reiniciar Apache
    sudo systemctl restart apache2
    sudo systemctl enable apache2
    
    log_success "Apache configurado y reiniciado"
}

# Función para configurar renovación automática
setup_auto_renewal() {
    if [[ "$AUTO_RENEW" != "S" && "$AUTO_RENEW" != "s" ]]; then
        log_info "Renovación automática omitida"
        return
    fi
    
    log_info "Configurando renovación automática..."
    
    # Crear script de renovación
    local RENEW_SCRIPT="$SSL_DIR/renew-cert.sh"
    
    cat > "$RENEW_SCRIPT" <<EOF
#!/bin/bash
# Script de renovación automática de certificados SSL

SSL_DIR="$SSL_DIR"
DOMAIN="$DOMAIN"
DAYS=$DAYS

cd "$PROJECT_ROOT"

# Generar nuevo certificado
openssl req -x509 -nodes -days \$DAYS -newkey rsa:$KEY_SIZE \
  -keyout \$SSL_DIR/private/\$DOMAIN.key \
  -out \$SSL_DIR/certs/\$DOMAIN.crt \
  -subj "/C=$COUNTRY/ST=$STATE/L=$CITY/O=$ORG/OU=$OU/CN=\$DOMAIN"

# Establecer permisos
chmod 600 \$SSL_DIR/private/\$DOMAIN.key
chmod 644 \$SSL_DIR/certs/\$DOMAIN.crt

# Reiniciar Apache
sudo systemctl reload apache2

# Registrar en log
echo "[\$(date)] Certificado SSL renovado para \$DOMAIN" >> \$SSL_DIR/renewal.log
EOF
    
    chmod +x "$RENEW_SCRIPT"
    
    # Agregar a crontab (renovar cada 6 meses)
    (crontab -l 2>/dev/null; echo "0 2 1 */6 * $RENEW_SCRIPT") | crontab -
    
    log_success "Renovación automática configurada"
    log_info "El certificado se renovará automáticamente cada 6 meses"
}

# Función para actualizar configuración de Laravel
update_laravel_config() {
    log_info "Actualizando configuración de Laravel..."
    
    # Actualizar archivo .env
    local ENV_FILE="$PROJECT_ROOT/.env"
    
    if [[ -f "$ENV_FILE" ]]; then
        # Actualizar URLs
        sed -i "s|^APP_URL=.*|APP_URL=https://$DOMAIN|" "$ENV_FILE"
        sed -i "s|^FORCE_HTTPS=.*|FORCE_HTTPS=true|" "$ENV_FILE"
        
        # Actualizar rutas de certificados
        sed -i "s|^SSL_CERT_PATH=.*|SSL_CERT_PATH=$CERTS_DIR/${DOMAIN}.crt|" "$ENV_FILE"
        sed -i "s|^SSL_KEY_PATH=.*|SSL_KEY_PATH=$PRIVATE_DIR/${DOMAIN}.key|" "$ENV_FILE"
        
        log_success "Archivo .env actualizado"
    else
        log_warning "Archivo .env no encontrado. Asegúrate de configurar manualmente:"
        log_info "  APP_URL=https://$DOMAIN"
        log_info "  FORCE_HTTPS=true"
        log_info "  SSL_CERT_PATH=$CERTS_DIR/${DOMAIN}.crt"
        log_info "  SSL_KEY_PATH=$PRIVATE_DIR/${DOMAIN}.key"
    fi
    
    # Limpiar caché de Laravel
    cd "$PROJECT_ROOT"
    php artisan config:clear 2>/dev/null || true
    php artisan cache:clear 2>/dev/null || true
    
    log_success "Configuración de Laravel actualizada"
}

# Función para mostrar resumen
show_summary() {
    echo ""
    log_success "✅ Configuración SSL completada exitosamente!"
    echo ""
    echo -e "${BLUE}═══════════════════════════════════════════════════════════════${NC}"
    echo -e "${BLUE}                    RESUMEN DE CONFIGURACIÓN                   ${NC}"
    echo -e "${BLUE}═══════════════════════════════════════════════════════════════${NC}"
    echo ""
    echo -e "🌐 ${YELLOW}Dominio:${NC} $DOMAIN"
    echo -e "🔒 ${YELLOW}Tipo de certificado:${NC} $([[ $CERT_TYPE == "1" ]] && echo "Autofirmado" || echo "CSR para CA interna")"
    echo -e "📍 ${YELLOW}Ruta del certificado:${NC} $CERTS_DIR/${DOMAIN}.crt"
    echo -e "🔑 ${YELLOW}Ruta de la clave privada:${NC} $PRIVATE_DIR/${DOMAIN}.key"
    echo ""
    
    if [[ "$CONFIG_APACHE" == "S" || "$CONFIG_APACHE" == "s" ]]; then
        echo -e "🌐 ${YELLOW}URL de acceso:${NC} https://$DOMAIN"
        echo -e "📁 ${YELLOW}Configuración Apache:${NC} /etc/apache2/sites-available/${DOMAIN}-ssl.conf"
        echo ""
    fi
    
    if [[ "$AUTO_RENEW" == "S" || "$AUTO_RENEW" == "s" ]]; then
        echo -e "🔄 ${YELLOW}Renovación automática:${NC} Configurada (cada 6 meses)"
        echo -e "📜 ${YELLOW}Script de renovación:${NC} $SSL_DIR/renew-cert.sh"
        echo ""
    fi
    
    echo -e "${BLUE}═══════════════════════════════════════════════════════════════${NC}"
    echo ""
    
    # Próximos pasos
    log_info "Próximos pasos:"
    echo "1. Verifica que la aplicación funcione en: https://$DOMAIN"
    echo "2. Actualiza tu DNS interno para apuntar $DOMAIN a este servidor"
    echo "3. Si usas CSR, entrega el archivo a tu equipo de seguridad"
    echo "4. Configura los clientes para confiar en el certificado si es autofirmado"
    echo ""
}

# Función principal
main() {
    show_banner
    check_requirements
    setup_directories
    get_user_config
    
    # Generar certificado según tipo seleccionado
    if [[ "$CERT_TYPE" == "1" ]]; then
        generate_self_signed_cert
    else
        generate_csr
    fi
    
    configure_apache
    setup_auto_renewal
    update_laravel_config
    show_summary
}

# Manejo de señales
trap 'log_error "Script interrumpido"; exit 1' INT TERM

# Ejecutar función principal
main "$@"
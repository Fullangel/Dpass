#!/bin/bash

# =============================================================================
# Script de Verificación SSL - SENIAT Visitor Management
# =============================================================================
# Este script verifica el estado y configuración de los certificados SSL
# =============================================================================

set -e

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

# Funciones auxiliares
log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[✓]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[⚠]${NC} $1"
}

log_error() {
    echo -e "${RED}[✗]${NC} $1"
}

# Función para mostrar banner
show_banner() {
    echo -e "${BLUE}"
    echo "╔══════════════════════════════════════════════════════════════════════════════╗"
    echo "║                    VERIFICACIÓN SSL - SENIAT VISITOR MGMT                  ║"
    echo "╚══════════════════════════════════════════════════════════════════════════════╝"
    echo -e "${NC}"
    echo ""
}

# Función para verificar estructura de directorios
check_ssl_structure() {
    log_info "Verificando estructura de directorios SSL..."
    
    local errors=0
    
    # Verificar directorios principales
    if [[ -d "$SSL_DIR" ]]; then
        log_success "Directorio SSL encontrado: $SSL_DIR"
    else
        log_error "Directorio SSL no encontrado: $SSL_DIR"
        ((errors++))
    fi
    
    # Verificar subdirectorios
    local subdirs=("certs" "private" "csr" "newcerts" "crl")
    for subdir in "${subdirs[@]}"; do
        if [[ -d "$SSL_DIR/$subdir" ]]; then
            log_success "Subdirectorio encontrado: $subdir"
        else
            log_warning "Subdirectorio no encontrado: $subdir"
        fi
    done
    
    return $errors
}

# Función para verificar certificados
check_certificates() {
    log_info "Verificando certificados SSL..."
    
    local certs_dir="$SSL_DIR/certs"
    local private_dir="$SSL_DIR/private"
    local errors=0
    
    # Buscar certificados
    if [[ -d "$certs_dir" ]]; then
        local cert_files=("$(find "$certs_dir" -name "*.crt" -o -name "*.pem" 2>/dev/null)")
        
        if [[ ${#cert_files[@]} -gt 0 ]]; then
            for cert_file in "${cert_files[@]}"; do
                if [[ -f "$cert_file" ]]; then
                    log_success "Certificado encontrado: $(basename "$cert_file")"
                    
                    # Verificar validez del certificado
                    if openssl x509 -in "$cert_file" -noout -checkend 86400 &>/dev/null; then
                        log_success "  Certificado válido por más de 24 horas"
                    else
                        log_warning "  Certificado expira pronto o está expirado"
                    fi
                    
                    # Mostrar información del certificado
                    local subject=$(openssl x509 -in "$cert_file" -noout -subject 2>/dev/null | cut -d'=' -f2-)
                    local issuer=$(openssl x509 -in "$cert_file" -noout -issuer 2>/dev/null | cut -d'=' -f2-)
                    local expiry=$(openssl x509 -in "$cert_file" -noout -enddate 2>/dev/null | cut -d'=' -f2-)
                    
                    echo "    Sujeto: $subject"
                    echo "    Emisor: $issuer"
                    echo "    Expira: $expiry"
                fi
            done
        else
            log_warning "No se encontraron certificados en $certs_dir"
        fi
    fi
    
    # Verificar claves privadas
    if [[ -d "$private_dir" ]]; then
        local key_files=("$(find "$private_dir" -name "*.key" -o -name "*.pem" 2>/dev/null)")
        
        if [[ ${#key_files[@]} -gt 0 ]]; then
            for key_file in "${key_files[@]}"; do
                if [[ -f "$key_file" ]]; then
                    log_success "Clave privada encontrada: $(basename "$key_file")"
                    
                    # Verificar permisos
                    local perms=$(stat -c "%a" "$key_file")
                    if [[ "$perms" == "600" ]]; then
                        log_success "  Permisos correctos (600)"
                    else
                        log_warning "  Permisos incorrectos ($perms), deberían ser 600"
                    fi
                fi
            done
        else
            log_warning "No se encontraron claves privadas en $private_dir"
        fi
    fi
    
    return $errors
}

# Función para verificar configuración de Apache
check_apache_config() {
    log_info "Verificando configuración de Apache..."
    
    local errors=0
    
    # Verificar si Apache está instalado
    if command -v apache2 &>/dev/null; then
        log_success "Apache está instalado"
        
        # Verificar módulos SSL
        if apache2ctl -M | grep -q ssl; then
            log_success "Módulo SSL está habilitado"
        else
            log_error "Módulo SSL no está habilitado"
            ((errors++))
        fi
        
        # Verificar configuración de sintaxis
        if apache2ctl configtest &>/dev/null; then
            log_success "Configuración de Apache es válida"
        else
            log_error "Configuración de Apache tiene errores"
            apache2ctl configtest
            ((errors++))
        fi
        
        # Verificar puertos
        if netstat -tlnp | grep -q ":443"; then
            log_success "Puerto 443 (HTTPS) está escuchando"
        else
            log_warning "Puerto 443 (HTTPS) no está escuchando"
        fi
        
        if netstat -tlnp | grep -q ":80"; then
            log_success "Puerto 80 (HTTP) está escuchando"
        else
            log_warning "Puerto 80 (HTTP) no está escuchando"
        fi
        
    else
        log_error "Apache no está instalado"
        ((errors++))
    fi
    
    return $errors
}

# Función para verificar configuración Laravel
check_laravel_config() {
    log_info "Verificando configuración de Laravel..."
    
    local errors=0
    local env_file="$PROJECT_ROOT/.env"
    
    if [[ -f "$env_file" ]]; then
        log_success "Archivo .env encontrado"
        
        # Verificar configuración SSL
        if grep -q "^FORCE_HTTPS=true" "$env_file"; then
            log_success "HTTPS forzado está habilitado"
        else
            log_warning "HTTPS forzado no está habilitado"
        fi
        
        # Verificar URLs
        local app_url=$(grep "^APP_URL=" "$env_file" | cut -d'=' -f2-)
        if [[ "$app_url" == https://* ]]; then
            log_success "APP_URL usa HTTPS: $app_url"
        else
            log_warning "APP_URL no usa HTTPS: $app_url"
        fi
        
        # Verificar rutas de certificados
        local ssl_cert_path=$(grep "^SSL_CERT_PATH=" "$env_file" | cut -d'=' -f2-)
        local ssl_key_path=$(grep "^SSL_KEY_PATH=" "$env_file" | cut -d'=' -f2-)
        
        if [[ -n "$ssl_cert_path" && -f "$ssl_cert_path" ]]; then
            log_success "Ruta de certificado SSL válida"
        else
            log_warning "Ruta de certificado SSL no válida"
        fi
        
        if [[ -n "$ssl_key_path" && -f "$ssl_key_path" ]]; then
            log_success "Ruta de clave privada SSL válida"
        else
            log_warning "Ruta de clave privada SSL no válida"
        fi
        
    else
        log_error "Archivo .env no encontrado"
        ((errors++))
    fi
    
    return $errors
}

# Función para verificar conectividad SSL
check_ssl_connectivity() {
    log_info "Verificando conectividad SSL..."
    
    local errors=0
    local domain="intranet.seniat.local"
    
    # Obtener dominio del archivo .env
    local env_file="$PROJECT_ROOT/.env"
    if [[ -f "$env_file" ]]; then
        local app_url=$(grep "^APP_URL=" "$env_file" | cut -d'=' -f2-)
        if [[ -n "$app_url" ]]; then
            domain=$(echo "$app_url" | sed 's|https://||' | sed 's|http://||' | cut -d'/' -f1)
        fi
    fi
    
    # Verificar conexión SSL
    if command -v openssl &>/dev/null; then
        if timeout 5 openssl s_client -connect "${domain}:443" -servername "$domain" </dev/null 2>/dev/null | grep -q "Certificate chain"; then
            log_success "Conexión SSL establecida con $domain"
            
            # Obtener información del certificado
            local cert_info=$(timeout 5 openssl s_client -connect "${domain}:443" -servername "$domain" </dev/null 2>/dev/null | openssl x509 -noout -dates 2>/dev/null)
            if [[ -n "$cert_info" ]]; then
                echo "  Información del certificado:"
                echo "$cert_info" | sed 's/^/    /'
            fi
        else
            log_error "No se pudo establecer conexión SSL con $domain"
            ((errors++))
        fi
    else
        log_warning "OpenSSL no está disponible para verificación de conectividad"
    fi
    
    return $errors
}

# Función principal
main() {
    show_banner
    
    local total_errors=0
    
    check_ssl_structure
    ((total_errors += $?))
    
    check_certificates
    ((total_errors += $?))
    
    check_apache_config
    ((total_errors += $?))
    
    check_laravel_config
    ((total_errors += $?))
    
    check_ssl_connectivity
    ((total_errors += $?))
    
    echo ""
    echo -e "${BLUE}═══════════════════════════════════════════════════════════════════════════════${NC}"
    
    if [[ $total_errors -eq 0 ]]; then
        log_success "✅ Todos los chequeos SSL pasaron exitosamente!"
        echo ""
        log_info "Tu configuración SSL está funcionando correctamente."
        log_info "Puedes acceder a tu aplicación en: https://intranet.seniat.local"
    else
        log_error "❌ Se encontraron $total_errors problema(s) en la configuración SSL."
        echo ""
        log_info "Por favor revisa los mensajes de error anteriores y soluciona los problemas."
        log_info "Consulta la sección de solución de problemas en el README.md para más ayuda."
    fi
    
    echo -e "${BLUE}═══════════════════════════════════════════════════════════════════════════════${NC}"
    echo ""
}

# Manejo de señales
trap 'log_error "Script interrumpido"; exit 1' INT TERM

# Ejecutar función principal
main "$@"
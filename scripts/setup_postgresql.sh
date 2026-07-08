#!/bin/bash

# Script de preparación de entorno PostgreSQL para Laravel
# Este script configura PostgreSQL y prepara la base de datos para Laravel

set -e

echo "🐘 Preparando entorno PostgreSQL para Laravel..."

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

print_message() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Verificar si PostgreSQL está instalado
if ! command -v psql &> /dev/null; then
    print_error "PostgreSQL no está instalado. Por favor, instálalo primero:"
    print_message "Ubuntu/Debian: sudo apt-get install postgresql postgresql-contrib"
    print_message "CentOS/RHEL: sudo yum install postgresql-server postgresql-contrib"
    print_message "macOS: brew install postgresql"
    exit 1
fi

print_success "PostgreSQL está instalado"

# Verificar si el servicio está corriendo
if ! pg_isready -q; then
    print_error "PostgreSQL no está corriendo. Por favor, inicia el servicio:"
    print_message "sudo systemctl start postgresql"
    print_message "o"
    print_message "sudo service postgresql start"
    exit 1
fi

print_success "PostgreSQL está corriendo"

# Solicitar información de configuración
print_message "Configurando nueva base de datos PostgreSQL..."
read -p "Nombre de la base de datos [laravel_app]: " DB_NAME
read -p "Usuario de la base de datos [laravel_user]: " DB_USER
read -sp "Contraseña del usuario: " DB_PASSWORD
echo ""
read -p "¿Deseas crear el usuario? (s/n) [s]: " CREATE_USER

# Valores por defecto
DB_NAME=${DB_NAME:-laravel_app}
DB_USER=${DB_USER:-laravel_user}
CREATE_USER=${CREATE_USER:-s}

# Verificar si somos superusuario o necesitamos sudo
SUDO=""
if [ "$EUID" -ne 0 ] && [ "$(whoami)" != "postgres" ]; then
    SUDO="sudo -u postgres"
    print_message "Se usará sudo para ejecutar comandos como usuario postgres"
fi

# Crear usuario si es necesario
if [ "$CREATE_USER" = "s" ] || [ "$CREATE_USER" = "S" ]; then
    print_message "Creando usuario PostgreSQL..."
    $SUDO psql -c "CREATE USER $DB_USER WITH PASSWORD '$DB_PASSWORD';" 2>/dev/null || {
        print_warning "El usuario ya existe o no se pudo crear"
    }
fi

# Crear base de datos
print_message "Creando base de datos..."
$SUDO createdb -O $DB_USER $DB_NAME 2>/dev/null || {
    print_warning "La base de datos ya existe o no se pudo crear"
}

# Otorgar privilegios
print_message "Otorgando privilegios..."
$SUDO psql -c "GRANT ALL PRIVILEGES ON DATABASE $DB_NAME TO $DB_USER;"

# Configurar PostgreSQL para aceptar conexiones locales con contraseña
print_message "Verificando configuración de PostgreSQL..."

# Verificar archivo pg_hba.conf
PG_HBA_FILE=$($SUDO psql -t -P format=unaligned -c "SHOW hba_file;" 2>/dev/null | head -n 1)
if [ -n "$PG_HBA_FILE" ]; then
    print_message "Archivo pg_hba.conf encontrado: $PG_HBA_FILE"
    
    # Verificar si ya tiene configuración md5 para localhost
    if ! $SUDO grep -q "host.*all.*all.*127.0.0.1/32.*md5" "$PG_HBA_FILE"; then
        print_warning "Agregando configuración de autenticación para localhost..."
        echo "host    all             all             127.0.0.1/32            md5" | $SUDO tee -a "$PG_HBA_FILE" > /dev/null
    fi
    
    if ! $SUDO grep -q "host.*all.*all.*::1/128.*md5" "$PG_HBA_FILE"; then
        echo "host    all             all             ::1/128                 md5" | $SUDO tee -a "$PG_HBA_FILE" > /dev/null
    fi
else
    print_warning "No se pudo localizar el archivo pg_hba.conf. Por favor, verifica la configuración manualmente."
fi

# Verificar postgresql.conf
POSTGRES_CONF=$($SUDO psql -t -P format=unalike -c "SHOW config_file;" 2>/dev/null | head -n 1)
if [ -n "$POSTGRES_CONF" ]; then
    print_message "Archivo postgresql.conf encontrado: $POSTGRES_CONF"
    
    # Verificar listen_addresses
    if ! $SUDO grep -q "^listen_addresses.*'*'" "$POSTGRES_CONF" && ! $SUDO grep -q "^listen_addresses.*'localhost'" "$POSTGRES_CONF"; then
        print_warning "Configurando PostgreSQL para aceptar conexiones locales..."
        echo "listen_addresses = 'localhost'" | $SUDO tee -a "$POSTGRES_CONF" > /dev/null
    fi
fi

# Reiniciar PostgreSQL para aplicar cambios
print_message "Reiniciando PostgreSQL para aplicar cambios..."
if command -v systemctl &> /dev/null; then
    sudo systemctl restart postgresql
elif command -v service &> /dev/null; then
    sudo service postgresql restart
else
    print_warning "Por favor, reinicia PostgreSQL manualmente"
fi

print_success "Configuración de PostgreSQL completada"

# Crear archivo .env.postgresql
print_message "Creando archivo de configuración .env.postgresql..."
cat > .env.postgresql << EOF
# Configuración PostgreSQL para Laravel
# Generado automáticamente el $(date)

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=$DB_NAME
DB_USERNAME=$DB_USER
DB_PASSWORD=$DB_PASSWORD

# Configuración adicional recomendada
DB_SCHEMA=public
DB_SSLMODE=prefer
EOF

print_success "Archivo .env.postgresql creado"

# Probar conexión
print_message "Probando conexión a la base de datos..."
export PGPASSWORD="$DB_PASSWORD"
if psql -h 127.0.0.1 -p 5432 -U "$DB_USER" -d "$DB_NAME" -c "SELECT current_database(), current_user;" > /dev/null 2>&1; then
    print_success "Conexión exitosa a PostgreSQL"
else
    print_error "No se pudo conectar a la base de datos. Por favor, verifica la configuración."
fi

# Mostrar resumen
print_success "¡Preparación de PostgreSQL completada!"
echo ""
print_message "Resumen de la configuración:"
print_message "  Base de datos: $DB_NAME"
print_message "  Usuario: $DB_USER"
print_message "  Host: 127.0.0.1:5432"
print_message ""
print_message "Para usar PostgreSQL en tu aplicación Laravel:"
print_message "1. Copia .env.postgresql a .env"
print_message "2. Ejecuta: php artisan config:clear"
print_message "3. Ejecuta: php artisan migrate:fresh --seed"
print_message "4. Prueba tu aplicación"
print_message ""
print_message "Para migrar datos desde MySQL, ejecuta:"
print_message "  bash scripts/migrate_mysql_to_postgresql.sh"
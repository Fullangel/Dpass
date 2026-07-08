#!/bin/bash

# Script de configuración PostgreSQL específico para WSL Ubuntu 24.04
# Este script configura PostgreSQL con las credenciales existentes

set -e

echo "🐘 Configurando PostgreSQL para WSL Ubuntu 24.04..."

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

# Verificar que PostgreSQL esté instalado
if ! command -v psql &> /dev/null; then
    print_error "PostgreSQL no está instalado. Instalando..."
    sudo apt update
    sudo apt install -y postgresql postgresql-contrib
fi

# Verificar que el servicio esté corriendo
if ! sudo pg_isready -q; then
    print_message "Iniciando servicio PostgreSQL..."
    sudo service postgresql start
    sleep 3
fi

# Credenciales desde el archivo .env
if [ -f "/var/www/html/.env" ]; then
    DB_DATABASE=$(grep "^DB_DATABASE=" /var/www/html/.env | cut -d'=' -f2)
    DB_USERNAME=$(grep "^DB_USERNAME=" /var/www/html/.env | cut -d'=' -f2)
    DB_PASSWORD=$(grep "^DB_PASSWORD=" /var/www/html/.env | cut -d'=' -f2)
elif [ -f "../.env" ]; then
    DB_DATABASE=$(grep "^DB_DATABASE=" ../.env | cut -d'=' -f2)
    DB_USERNAME=$(grep "^DB_USERNAME=" ../.env | cut -d'=' -f2)
    DB_PASSWORD=$(grep "^DB_PASSWORD=" ../.env | cut -d'=' -f2)
elif [ -f "./.env" ]; then
    DB_DATABASE=$(grep "^DB_DATABASE=" ./.env | cut -d'=' -f2)
    DB_USERNAME=$(grep "^DB_USERNAME=" ./.env | cut -d'=' -f2)
    DB_PASSWORD=$(grep "^DB_PASSWORD=" ./.env | cut -d'=' -f2)
else
    print_error "No se encontró el archivo .env"
    exit 1
fi

print_message "Configuración detectada del .env:"
print_message "  Base de datos: $DB_DATABASE"
print_message "  Usuario: $DB_USERNAME"
print_message "  Contraseña: [OCULTA]"

# Verificar si el usuario existe
print_message "Verificando usuario PostgreSQL..."
USER_EXISTS=$(sudo -u postgres psql -tAc "SELECT 1 FROM pg_roles WHERE rolname='$DB_USERNAME'" 2>/dev/null || echo "0")

if [ "$USER_EXISTS" = "1" ]; then
    print_success "El usuario $DB_USERNAME ya existe"
else
    print_message "Creando usuario PostgreSQL $DB_USERNAME..."
    sudo -u postgres psql -c "CREATE USER $DB_USERNAME WITH PASSWORD '$DB_PASSWORD';" 2>/dev/null || {
        print_error "No se pudo crear el usuario $DB_USERNAME"
        exit 1
    }
    print_success "Usuario $DB_USERNAME creado exitosamente"
fi

# Verificar si la base de datos existe
print_message "Verificando base de datos..."
DB_EXISTS=$(sudo -u postgres psql -tAc "SELECT 1 FROM pg_database WHERE datname='$DB_DATABASE'" 2>/dev/null || echo "0")

if [ "$DB_EXISTS" = "1" ]; then
    print_success "La base de datos $DB_DATABASE ya existe"
else
    print_message "Creando base de datos $DB_DATABASE..."
    sudo -u postgres createdb -O "$DB_USERNAME" "$DB_DATABASE" 2>/dev/null || {
        print_error "No se pudo crear la base de datos $DB_DATABASE"
        exit 1
    }
    print_success "Base de datos $DB_DATABASE creada exitosamente"
fi

# Otorgar privilegios
print_message "Otorgando privilegios al usuario $DB_USERNAME sobre $DB_DATABASE..."
sudo -u postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE $DB_DATABASE TO $DB_USERNAME;" 2>/dev/null || {
    print_warning "No se pudieron otorgar todos los privilegios (esto es normal si ya existen)"
}

# Configurar PostgreSQL para aceptar conexiones locales
print_message "Configurando PostgreSQL para conexiones locales..."

# Obtener archivo pg_hba.conf
PG_HBA_FILE=$(sudo -u postgres psql -t -P format=unaligned -c "SHOW hba_file;" 2>/dev/null | head -n 1)

if [ -n "$PG_HBA_FILE" ]; then
    print_message "Archivo pg_hba.conf encontrado: $PG_HBA_FILE"
    
    # Verificar si ya tiene configuración md5 para localhost
    if ! sudo grep -q "host.*all.*all.*127.0.0.1/32.*md5" "$PG_HBA_FILE"; then
        print_message "Agregando configuración de autenticación para localhost..."
        echo "host    all             all             127.0.0.1/32            md5" | sudo tee -a "$PG_HBA_FILE" > /dev/null
        echo "host    all             all             ::1/128                 md5" | sudo tee -a "$PG_HBA_FILE" > /dev/null
        CONFIG_UPDATED=true
    fi
else
    print_warning "No se pudo localizar el archivo pg_hba.conf"
fi

# Configurar listen_addresses si es necesario
POSTGRES_CONF=$(sudo -u postgres psql -t -P format=unaligned -c "SHOW config_file;" 2>/dev/null | head -n 1)

if [ -n "$POSTGRES_CONF" ]; then
    print_message "Archivo postgresql.conf encontrado: $POSTGRES_CONF"
    
    # Verificar listen_addresses
    if ! sudo grep -q "^listen_addresses" "$POSTGRES_CONF"; then
        print_message "Configurando listen_addresses..."
        echo "listen_addresses = 'localhost'" | sudo tee -a "$POSTGRES_CONF" > /dev/null
        CONFIG_UPDATED=true
    fi
fi

# Reiniciar PostgreSQL si se hicieron cambios de configuración
if [ "$CONFIG_UPDATED" = "true" ]; then
    print_message "Reiniciando PostgreSQL para aplicar cambios..."
    sudo service postgresql restart
    sleep 3
fi

# Probar conexión
print_message "Probando conexión a PostgreSQL..."
export PGPASSWORD="$DB_PASSWORD"

if psql -h 127.0.0.1 -p 5432 -U "$DB_USERNAME" -d "$DB_DATABASE" -c "SELECT current_database(), current_user;" > /dev/null 2>&1; then
    print_success "✅ Conexión exitosa a PostgreSQL!"
    print_message "  Base de datos: $DB_DATABASE"
    print_message "  Usuario: $DB_USERNAME"
    print_message "  Host: 127.0.0.1:5432"
else
    print_error "❌ No se pudo conectar a PostgreSQL con las credenciales proporcionadas"
    print_message "Por favor verifica:"
    print_message "  1. Que el servicio PostgreSQL esté corriendo: sudo service postgresql status"
    print_message "  2. Que las credenciales en .env sean correctas"
    print_message "  3. Que el usuario y base de datos existan"
    exit 1
fi

# Verificar versión de PostgreSQL
PG_VERSION=$(psql -h 127.0.0.1 -p 5432 -U "$DB_USERNAME" -d "$DB_DATABASE" -tAc "SELECT version();" 2>/dev/null | head -n 1)
print_success "PostgreSQL versión: $PG_VERSION"

# Crear archivo de configuración adicional si es necesario
cat > /var/www/html/.env.postgresql.wsl << EOF
# Configuración PostgreSQL para WSL Ubuntu 24.04
# Generado automáticamente el $(date)

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=$DB_DATABASE
DB_USERNAME=$DB_USERNAME
DB_PASSWORD=$DB_PASSWORD

# Configuración adicional para PostgreSQL
DB_SCHEMA=public
DB_SSLMODE=prefer
EOF

print_success "✅ Configuración de PostgreSQL para WSL completada!"
print_message ""
print_message "Resumen:"
print_message "  ✅ PostgreSQL está corriendo"
print_message "  ✅ Usuario $DB_USERNAME creado/configurado"
print_message "  ✅ Base de datos $DB_DATABASE creada/configurada"
print_message "  ✅ Conexión probada exitosamente"
print_message ""
print_message "Próximos pasos:"
print_message "  1. Tu archivo .env ya está configurado correctamente"
print_message "  2. Ejecuta: php artisan config:clear"
print_message "  3. Prueba: php artisan migrate:status"
print_message "  4. Si todo está bien, ejecuta: php artisan migrate:fresh --seed"
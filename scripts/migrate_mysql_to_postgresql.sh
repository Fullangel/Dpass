#!/bin/bash

# Script de migración de MySQL a PostgreSQL
# Autor: Sistema de migración automatizada
# Fecha: $(date)

set -e # Salir inmediatamente si un comando falla

echo "🚀 Iniciando migración de MySQL a PostgreSQL..."

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Función para imprimir mensajes
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

# Verificar que exista el archivo .env
if [ ! -f ".env" ]; then
    print_error "No se encontró el archivo .env en el directorio actual"
    exit 1
fi

# Leer configuración de MySQL desde .env
MYSQL_HOST=$(grep "^DB_HOST=" .env | cut -d'=' -f2)
MYSQL_PORT=$(grep "^DB_PORT=" .env | cut -d'=' -f2)
MYSQL_DATABASE=$(grep "^DB_DATABASE=" .env | cut -d'=' -f2)
MYSQL_USERNAME=$(grep "^DB_USERNAME=" .env | cut -d'=' -f2)
MYSQL_PASSWORD=$(grep "^DB_PASSWORD=" .env | cut -d'=' -f2)

# Valores por defecto si no están definidos
MYSQL_HOST=${MYSQL_HOST:-localhost}
MYSQL_PORT=${MYSQL_PORT:-3306}

print_message "Configuración MySQL detectada:"
print_message "  Host: $MYSQL_HOST:$MYSQL_PORT"
print_message "  Base de datos: $MYSQL_DATABASE"
print_message "  Usuario: $MYSQL_USERNAME"

# Solicitar configuración de PostgreSQL
print_message "Por favor, ingresa la configuración de PostgreSQL:"
read -p "Host PostgreSQL [localhost]: " PG_HOST
read -p "Puerto PostgreSQL [5432]: " PG_PORT
read -p "Nombre de base de datos PostgreSQL: " PG_DATABASE
read -p "Usuario PostgreSQL: " PG_USERNAME
read -sp "Contraseña PostgreSQL: " PG_PASSWORD
echo ""

# Valores por defecto
PG_HOST=${PG_HOST:-localhost}
PG_PORT=${PG_PORT:-5432}

print_message "Configuración PostgreSQL:"
print_message "  Host: $PG_HOST:$PG_PORT"
print_message "  Base de datos: $PG_DATABASE"
print_message "  Usuario: $PG_USERNAME"

# Verificar conexión a MySQL
print_message "Verificando conexión a MySQL..."
mysql -h "$MYSQL_HOST" -P "$MYSQL_PORT" -u "$MYSQL_USERNAME" -p"$MYSQL_PASSWORD" -e "SELECT 1;" "$MYSQL_DATABASE" > /dev/null 2>&1
if [ $? -ne 0 ]; then
    print_error "No se pudo conectar a MySQL. Verifica las credenciales."
    exit 1
fi
print_success "Conexión a MySQL exitosa"

# Verificar conexión a PostgreSQL
print_message "Verificando conexión a PostgreSQL..."
export PGPASSWORD="$PG_PASSWORD"
psql -h "$PG_HOST" -p "$PG_PORT" -U "$PG_USERNAME" -d "$PG_DATABASE" -c "SELECT 1;" > /dev/null 2>&1
if [ $? -ne 0 ]; then
    print_error "No se pudo conectar a PostgreSQL. Verifica las credenciales."
    exit 1
fi
print_success "Conexión a PostgreSQL exitosa"

# Crear archivo de configuración para pgloader
PGLOADER_CONFIG=$(cat <<EOF
LOAD DATABASE
    FROM mysql://$MYSQL_USERNAME:$MYSQL_PASSWORD@$MYSQL_HOST:$MYSQL_PORT/$MYSQL_DATABASE
    INTO postgresql://$PG_USERNAME:$PG_PASSWORD@$PG_HOST:$PG_PORT/$PG_DATABASE

WITH include drop, create tables, create indexes, reset sequences,
     workers = 8, concurrency = 4,
     multiple readers per thread, rows per range = 50000,
     preserve index names

CAST type tinyint when (= 1 precision) to boolean drop default drop not null using tinyint-to-boolean,
     type date drop not null drop default using zero-dates-to-null,
     type datetime drop not null drop default using zero-dates-to-null,
     type time drop not null drop default using zero-dates-to-null,
     type json to jsonb drop default drop not null using json-to-jsonb,
     type varchar to varchar drop default drop not null using varchar-to-varchar

-- Excluir tablas de migración de Laravel si es necesario
-- EXCLUDING TABLE NAMES MATCHING ~/^migrations/

-- Materializar vistas como tablas
-- MATERIALIZE VIEWS

-- Configuración adicional para tipos específicos
BEFORE LOAD DO
    \$\$ CREATE SCHEMA IF NOT EXISTS public; \$\$,
    \$\$ SET search_path TO public; \$\$
;
EOF
)

# Guardar configuración de pgloader
print_message "Creando configuración de pgloader..."
echo "$PGLOADER_CONFIG" > pgloader_config.load

# Realizar migración con pgloader
print_message "Iniciando migración de datos con pgloader..."
print_warning "Este proceso puede tomar varios minutos dependiendo del tamaño de la base de datos..."

pgloader pgloader_config.load

if [ $? -ne 0 ]; then
    print_error "La migración con pgloader falló"
    print_message "Intentando migración manual con mysqldump y psql..."
    
    # Método alternativo con mysqldump
    print_message "Exportando datos de MySQL..."
    mysqldump -h "$MYSQL_HOST" -P "$MYSQL_PORT" -u "$MYSQL_USERNAME" -p"$MYSQL_PASSWORD" \
        --compatible=postgresql \
        --default-character-set=utf8 \
        --skip-add-drop-table \
        --no-create-db \
        --skip-lock-tables \
        --complete-insert \
        --extended-insert=FALSE \
        "$MYSQL_DATABASE" > mysql_export.sql
    
    print_message "Importando datos a PostgreSQL..."
    psql -h "$PG_HOST" -p "$PG_PORT" -U "$PG_USERNAME" -d "$PG_DATABASE" -f mysql_export.sql
    
    if [ $? -ne 0 ]; then
        print_error "La migración manual también falló"
        exit 1
    fi
fi

print_success "Migración de datos completada exitosamente"

# Ajustar secuencias en PostgreSQL
print_message "Ajustando secuencias en PostgreSQL..."
cat > fix_sequences.sql << 'EOF'
-- Ajustar todas las secuencias para que continúen desde el valor máximo actual
DO $$
DECLARE
    seq_record RECORD;
    max_val BIGINT;
BEGIN
    FOR seq_record IN 
        SELECT schemaname, sequencename 
        FROM pg_sequences 
        WHERE schemaname = 'public'
    LOOP
        -- Obtener el valor máximo de la tabla relacionada
        EXECUTE format('SELECT COALESCE(MAX(id), 0) FROM public.%I', 
                      regexp_replace(seq_record.sequencename, '_id_seq$', ''))
        INTO max_val;
        
        -- Ajustar la secuencia
        EXECUTE format('ALTER SEQUENCE public.%I RESTART WITH %s', 
                      seq_record.sequencename, max_val + 1);
        
        RAISE NOTICE 'Secuencia % ajustada a %', seq_record.sequencename, max_val + 1;
    END LOOP;
END $$;
EOF

psql -h "$PG_HOST" -p "$PG_PORT" -U "$PG_USERNAME" -d "$PG_DATABASE" -f fix_sequences.sql

# Limpiar archivos temporales
rm -f pgloader_config.load mysql_export.sql fix_sequences.sql

print_success "Ajuste de secuencias completado"

# Verificar integridad de datos
print_message "Verificando integridad de datos..."
cat > verify_migration.sql << 'EOF'
-- Verificar cantidad de tablas
SELECT 'Total de tablas: ' || COUNT(*) 
FROM information_schema.tables 
WHERE table_schema = 'public' AND table_type = 'BASE TABLE';

-- Verificar algunas tablas clave
SELECT 'Tabla users: ' || COUNT(*) || ' registros' FROM users;
SELECT 'Tabla employees: ' || COUNT(*) || ' registros' FROM employees;
SELECT 'Tabla visitors: ' || COUNT(*) || ' registros' FROM visitors;
SELECT 'Tabla visiting_details: ' || COUNT(*) || ' registros' FROM visiting_details;

-- Verificar foreign keys activas
SELECT 'Foreign keys activas: ' || COUNT(*) 
FROM information_schema.table_constraints 
WHERE constraint_type = 'FOREIGN KEY' 
AND table_schema = 'public';
EOF

psql -h "$PG_HOST" -p "$PG_PORT" -U "$PG_USERNAME" -d "$PG_DATABASE" -f verify_migration.sql
rm -f verify_migration.sql

print_success "Verificación de integridad completada"
print_success "¡Migración de MySQL a PostgreSQL finalizada exitosamente!"

print_message "Próximos pasos:"
print_message "1. Actualiza tu archivo .env: DB_CONNECTION=pgsql"
print_message "2. Ajusta DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD"
print_message "3. Ejecuta: php artisan config:clear"
print_message "4. Prueba tu aplicación en el nuevo entorno PostgreSQL"
print_message "5. Realiza pruebas exhaustivas antes de poner en producción"
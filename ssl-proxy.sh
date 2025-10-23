#!/bin/bash

# Script interactivo para ejecutar Laravel con SSL usando proxy
# Este script inicia Laravel en el puerto 8000 y el proxy SSL en el 8443

echo "🚀 Iniciador de Laravel con SSL Proxy"
echo "======================================"
echo ""

# Verificar que existan los certificados
SSL_CERT="/var/www/html/ssl-ca/temp-cert.pem"
SSL_KEY="/var/www/html/ssl-ca/private/temp-key.pem"

if [[ ! -f "$SSL_CERT" ]]; then
    echo "❌ Error: Certificado SSL no encontrado en: $SSL_CERT"
    exit 1
fi

if [[ ! -f "$SSL_KEY" ]]; then
    echo "❌ Error: Clave privada SSL no encontrada en: $SSL_KEY"
    exit 1
fi

# Verificar que el script proxy existe
PROXY_SCRIPT="/var/www/html/ssl-proxy.php"
if [[ ! -f "$PROXY_SCRIPT" ]]; then
    echo "❌ Error: Script proxy no encontrado en: $PROXY_SCRIPT"
    exit 1
fi

# Verificar que Laravel esté disponible
if [[ ! -f "artisan" ]]; then
    echo "❌ Error: No se encontró artisan en el directorio actual"
    echo "   Asegúrate de ejecutar este script desde la raíz de tu proyecto Laravel"
    exit 1
fi

echo "✅ Verificaciones completadas"
echo ""

# Preguntar puertos
read -p "Puerto SSL (8443): " SSL_PORT
SSL_PORT=${SSL_PORT:-8443}

read -p "Puerto HTTP backend (8000): " HTTP_PORT
HTTP_PORT=${HTTP_PORT:-8000}

read -p "Host (127.0.0.1): " HOST
HOST=${HOST:-127.0.0.1}

echo ""
echo "🔄 Iniciando Laravel en el puerto $HTTP_PORT..."

# Verificar si Laravel ya está ejecutándose
if curl -s "http://$HOST:$HTTP_PORT" > /dev/null; then
    echo "✅ Laravel ya está ejecutándose en http://$HOST:$HTTP_PORT"
else
    # Iniciar Laravel
    php artisan serve --host=$HOST --port=$HTTP_PORT > /dev/null 2>&1 &
    LARAVEL_PID=$!
    
    # Esperar a que Laravel se inicie
    echo "⏳ Esperando a que Laravel se inicie..."
    for i in {1..10}; do
        if curl -s "http://$HOST:$HTTP_PORT" > /dev/null; then
            echo "✅ Laravel iniciado correctamente"
            break
        fi
        sleep 1
    done
    
    # Verificar si Laravel se inició
    if ! curl -s "http://$HOST:$HTTP_PORT" > /dev/null; then
        echo "❌ Error: Laravel no se pudo iniciar"
        kill $LARAVEL_PID 2>/dev/null
        exit 1
    fi
fi

echo ""
echo "🔒 Iniciando proxy SSL en el puerto $SSL_PORT..."
echo "🌐 URL de acceso: https://$HOST:$SSL_PORT"
echo "🔗 Backend HTTP: http://$HOST:$HTTP_PORT"
echo ""
echo "ℹ️  Presiona Ctrl+C para detener el proxy SSL"
echo "ℹ️  Para detener Laravel, usa: php artisan serve:stop"
echo ""

# Iniciar proxy SSL
php "$PROXY_SCRIPT" "$SSL_PORT" "$HTTP_PORT" "$HOST"
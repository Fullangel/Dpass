#!/usr/bin/env php
<?php
/**
 * Proxy SSL para Laravel Artisan Serve
 * 
 * Esta solución usa php artisan serve como backend y agrega SSL al frente
 * Evita completamente el problema de constantes redefinidas
 * 
 * Uso: php ssl-proxy.php [puerto_ssl] [puerto_http] [host]
 * Ejemplo: php ssl-proxy.php 8443 8000 127.0.0.1
 */

$sslPort = $argv[1] ?? '8443';
$httpPort = $argv[2] ?? '8000';
$host = $argv[3] ?? '127.0.0.1';

// Verificar que existan los certificados SSL
$sslCert = __DIR__ . '/ssl-ca/temp-cert.pem';
$sslKey = __DIR__ . '/ssl-ca/private/temp-key.pem';

if (!file_exists($sslCert)) {
    echo "❌ Error: No se encontró el certificado SSL en: $sslCert\n";
    exit(1);
}

if (!file_exists($sslKey)) {
    echo "❌ Error: No se encontró la clave privada SSL en: $sslKey\n";
    exit(1);
}

echo "🔒 Iniciando Proxy SSL para Laravel\n";
echo "📍 SSL: https://$host:$sslPort\n";
echo "🔗 HTTP Backend: http://$host:$httpPort\n";
echo "🔐 Certificado: $sslCert\n\n";

// Verificar que Laravel esté ejecutándose
$ch = curl_init("http://$host:$httpPort");
curl_setopt($ch, CURLOPT_TIMEOUT, 2);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 0) {
    echo "⚠️  Advertencia: No se detectó Laravel en http://$host:$httpPort\n";
    echo "🔄 Por favor inicia Laravel primero:\n";
    echo "   php artisan serve --host=$host --port=$httpPort\n\n";
    echo "¿Deseas iniciar Laravel automáticamente? (s/n): ";
    $respuesta = trim(fgets(STDIN));
    
    if (strtolower($respuesta) === 's') {
        echo "🚀 Iniciando Laravel automáticamente...\n";
        exec("cd \"" . __DIR__ . "\" && php artisan serve --host=$host --port=$httpPort > /dev/null 2>&1 &");
        sleep(3);
    } else {
        exit(1);
    }
} else {
    echo "✅ Laravel detectado en http://$host:$httpPort\n";
}

// Contexto SSL
$context = stream_context_create([
    'ssl' => [
        'local_cert' => $sslCert,
        'local_pk' => $sslKey,
        'verify_peer' => false,
        'allow_self_signed' => true,
        'crypto_method' => STREAM_CRYPTO_METHOD_TLSv1_2_SERVER | STREAM_CRYPTO_METHOD_TLSv1_3_SERVER,
    ],
]);

// Crear servidor SSL
$server = stream_socket_server(
    "ssl://$host:$sslPort",
    $errno,
    $errstr,
    STREAM_SERVER_BIND | STREAM_SERVER_LISTEN,
    $context
);

if (!$server) {
    echo "❌ Error al crear servidor SSL: $errstr ($errno)\n";
    exit(1);
}

echo "✅ Proxy SSL iniciado correctamente\n";
echo "🌐 Accede a: https://$host:$sslPort\n";
echo "⚠️  Presiona Ctrl+C para detener el servidor\n\n";

// Manejador de señales
if (function_exists('pcntl_signal')) {
    pcntl_signal(SIGINT, function() use ($server) {
        echo "\n🛑 Deteniendo proxy SSL...\n";
        fclose($server);
        exit(0);
    });
}

// Bucle principal
while (true) {
    $client = @stream_socket_accept($server, -1);
    
    if ($client === false) {
        continue;
    }
    
    // Leer petición completa
    $request = '';
    while (!feof($client)) {
        $line = fgets($client, 1024);
        $request .= $line;
        if ($line === "\r\n" || $line === "\n") {
            break;
        }
    }
    
    // Leer body si existe
    $contentLength = 0;
    if (preg_match('/Content-Length:\s*(\d+)/i', $request, $matches)) {
        $contentLength = intval($matches[1]);
    }
    
    if ($contentLength > 0) {
        $body = fread($client, $contentLength);
        $request .= $body;
    }
    
    // Procesar petición
    processRequest($client, $request, $host, $httpPort);
    fclose($client);
}

/**
 * Procesar petición y crear proxy
 */
function processRequest($client, $request, $host, $httpPort) {
    // Parsear primera línea
    $lines = explode("\n", $request);
    $firstLine = trim($lines[0]);
    
    if (!preg_match('/^(GET|POST|PUT|DELETE|HEAD|OPTIONS) \/([^ ]*) HTTP/', $firstLine, $matches)) {
        sendError($client, "Petición inválida");
        return;
    }
    
    $method = $matches[1];
    $path = '/' . $matches[2];
    
    echo "[" . date('Y-m-d H:i:s') . "] $method $path\n";
    
    // Construir URL para backend HTTP
    $backendUrl = "http://$host:$httpPort$path";
    
    // Extraer y modificar headers
    $headers = [];
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        if (strpos($line, 'GET') === 0 || strpos($line, 'POST') === 0 || strpos($line, 'PUT') === 0 || strpos($line, 'DELETE') === 0) {
            continue;
        }
        if (stripos($line, 'Host:') === 0) {
            $headers[] = "Host: $host:$httpPort";
        } else if (stripos($line, 'Accept-Encoding:') === 0) {
            // Saltar compresión para simplificar
            continue;
        } else {
            $headers[] = $line;
        }
    }
    
    // Agregar headers de proxy
    $headers[] = "X-Forwarded-Proto: https";
    $headers[] = "X-Forwarded-Port: 8443";
    $headers[] = "X-Forwarded-Host: $host:8443";
    
    // Construir petición para backend
    $backendRequest = "$method $path HTTP/1.1\r\n";
    foreach ($headers as $header) {
        $backendRequest .= "$header\r\n";
    }
    $backendRequest .= "\r\n";
    
    // Agregar body si existe
    if (preg_match('/Content-Length:\s*(\d+)/i', $request, $matches)) {
        $contentLength = intval($matches[1]);
        if ($contentLength > 0) {
            $bodyStart = strpos($request, "\r\n\r\n") + 4;
            $body = substr($request, $bodyStart, $contentLength);
            $backendRequest .= $body;
        }
    }
    
    // Realizar petición al backend
    $ch = curl_init("http://$host:$httpPort$path");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    // Agregar body si existe
    if (preg_match('/Content-Length:\s*(\d+)/i', $request, $matches)) {
        $contentLength = intval($matches[1]);
        if ($contentLength > 0) {
            $bodyStart = strpos($request, "\r\n\r\n") + 4;
            $body = substr($request, $bodyStart, $contentLength);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);
    
    if ($response === false) {
        sendError($client, "Error al conectar con el backend HTTP");
        return;
    }
    
    // Separar headers y body
    $responseHeaders = substr($response, 0, $headerSize);
    $responseBody = substr($response, $headerSize);
    
    // Modificar headers de respuesta
    $modifiedHeaders = [];
    $lines = explode("\n", $responseHeaders);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        
        // Modificar Location headers para HTTPS
        if (stripos($line, 'Location:') === 0) {
            $location = trim(substr($line, 9));
            if (strpos($location, 'http://') === 0) {
                $location = str_replace('http://', 'https://', $location);
                $location = str_replace(":$httpPort", ':8443', $location);
            }
            $modifiedHeaders[] = "Location: $location";
        } else if (stripos($line, 'Content-Length:') === 0) {
            // Recalcular Content-Length
            continue;
        } else if (stripos($line, 'Connection:') === 0) {
            // Forzar close connection
            $modifiedHeaders[] = "Connection: close";
        } else {
            $modifiedHeaders[] = $line;
        }
    }
    
    // Agregar Content-Length actualizado
    $modifiedHeaders[] = "Content-Length: " . strlen($responseBody);
    
    // Enviar respuesta al cliente
    $finalResponse = implode("\r\n", $modifiedHeaders) . "\r\n\r\n" . $responseBody;
    fwrite($client, $finalResponse);
}

/**
 * Enviar respuesta de error
 */
function sendError($client, $message) {
    $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>Error SSL Proxy - Laravel</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            margin: 40px; 
            background: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .error { 
            background: #f8d7da; 
            color: #721c24; 
            padding: 20px; 
            border-radius: 5px; 
            border-left: 4px solid #dc3545;
            margin-bottom: 20px;
        }
        .info { 
            background: #d1ecf1; 
            color: #0c5460; 
            padding: 20px; 
            border-radius: 5px; 
            border-left: 4px solid #17a2b8;
        }
        .code {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            border: 1px solid #e9ecef;
        }
        h1 {
            color: #dc3545;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚨 Error en Proxy SSL Laravel</h1>
        <div class="error">
            <strong>Error:</strong> {$message}
        </div>
        <div class="info">
            <h3>Soluciones rápidas:</h3>
            <ol>
                <li>Asegúrate de que Laravel esté ejecutándose:</li>
                <li><span class="code">php artisan serve --host=127.0.0.1 --port=8000</span></li>
                <li>Verifica que el puerto 8000 esté disponible</li>
                <li>Reinicia el proxy SSL: <span class="code">Ctrl+C</span> y ejecuta de nuevo</li>
            </ol>
        </div>
    </div>
</body>
</html>
HTML;
    
    $response = "HTTP/1.1 500 Internal Server Error\r\n";
    $response .= "Content-Type: text/html; charset=UTF-8\r\n";
    $response .= "Content-Length: " . strlen($html) . "\r\n";
    $response .= "Connection: close\r\n";
    $response .= "\r\n";
    $response .= $html;
    
    fwrite($client, $response);
}
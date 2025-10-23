<?php
/**
 * Configuración SSL para Apache/Nginx
 * 
 * Instrucciones de configuración para habilitar SSL correctamente
 * en el servidor web
 */

return [
    /**
     * === CONFIGURACIÓN PARA APACHE ===
     * 
     * Agregar al archivo de configuración del sitio (/etc/apache2/sites-available/):
     */
    'apache_config' => <<<'APACHE'
<VirtualHost *:443>
    ServerName intranet.seniat.local
    DocumentRoot /var/www/html/public
    
    # Certificados SSL
    SSLEngine on
    SSLCertificateFile /var/www/html/ssl-ca/intranet.seniat.local.cert.pem
    SSLCertificateKeyFile /var/www/html/ssl-ca/private/intranet.seniat.local.key.pem
    SSLCertificateChainFile /var/www/html/ssl-ca/ca.cert.pem
    
    # Configuración de seguridad SSL
    SSLProtocol -all +TLSv1.2 +TLSv1.3
    SSLCipherSuite ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384:ECDHE-ECDSA-CHACHA20-POLY1305:ECDHE-RSA-CHACHA20-POLY1305:DHE-RSA-AES128-GCM-SHA256:DHE-RSA-AES256-GCM-SHA384
    SSLHonorCipherOrder on
    SSLCompression off
    SSLSessionTickets off
    
    # OCSP Stapling
    SSLUseStapling on
    SSLStaplingResponderTimeout 5
    SSLStaplingReturnResponderErrors off
    SSLStaplingCache shmcb:/var/run/ocsp(128000)
    
    # Headers de seguridad
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains; preload"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-Frame-Options "DENY"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    
    <Directory /var/www/html/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/intranet-ssl-error.log
    CustomLog ${APACHE_LOG_DIR}/intranet-ssl-access.log combined
</VirtualHost>

# Redirección HTTP a HTTPS
<VirtualHost *:80>
    ServerName intranet.seniat.local
    Redirect permanent / https://intranet.seniat.local/
</VirtualHost>
APACHE
,

    /**
     * === CONFIGURACIÓN PARA NGINX ===
     * 
     * Agregar al archivo de configuración del sitio (/etc/nginx/sites-available/):
     */
    'nginx_config' => <<<'NGINX'
server {
    listen 80;
    server_name intranet.seniat.local;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name intranet.seniat.local;
    root /var/www/html/public;
    index index.php index.html;
    
    # Certificados SSL
    ssl_certificate /var/www/html/ssl-ca/intranet.seniat.local.cert.pem;
    ssl_certificate_key /var/www/html/ssl-ca/private/intranet.seniat.local.key.pem;
    ssl_trusted_certificate /var/www/html/ssl-ca/ca.cert.pem;
    
    # Configuración de seguridad SSL
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384:ECDHE-ECDSA-CHACHA20-POLY1305:ECDHE-RSA-CHACHA20-POLY1305:DHE-RSA-AES128-GCM-SHA256:DHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;
    ssl_session_timeout 1d;
    ssl_session_cache shared:SSL:10m;
    ssl_session_tickets off;
    
    # OCSP Stapling
    ssl_stapling on;
    ssl_stapling_verify on;
    resolver 8.8.8.8 8.8.4.4 valid=300s;
    resolver_timeout 5s;
    
    # Headers de seguridad
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-Frame-Options "DENY" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
NGINX
,

    /**
     * === PASOS DE IMPLEMENTACIÓN ===
     */
    'implementation_steps' => [
        '1. Instalar el servidor web (Apache/Nginx)',
        '2. Habilitar módulos SSL: sudo a2enmod ssl (Apache)',
        '3. Copiar la configuración correspondiente',
        '4. Verificar sintaxis: sudo apache2ctl configtest (Apache) o sudo nginx -t (Nginx)',
        '5. Reiniciar el servidor: sudo systemctl restart apache2/nginx',
        '6. Actualizar APP_URL en .env a https://intranet.seniat.local',
        '7. Configurar el archivo hosts si es necesario',
    ],
    
    /**
     * === VERIFICACIÓN DE SEGURIDAD ===
     */
    'security_tests' => [
        'SSL Labs Test' => 'https://www.ssllabs.com/ssltest/',
        'TLS Checker' => 'https://www.cdn77.com/tls-test',
        'Security Headers' => 'https://securityheaders.com/',
        'SSL Checker' => 'https://www.sslchecker.com/sslchecker',
    ],
];
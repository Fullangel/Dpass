<?php
/**
 * Configuración de Seguridad SSL/TLS
 * 
 * Este archivo contiene las configuraciones recomendadas para un SSL seguro
 * Basado en mejores prácticas de seguridad actualizadas
 */

return [
    /**
     * Protocolos SSL/TLS permitidos
     * Deshabilitar versiones vulnerables (SSLv2, SSLv3, TLS 1.0, TLS 1.1)
     */
    'allowed_protocols' => [
        'TLSv1.2',
        'TLSv1.3'
    ],
    
    /**
     * Cifrados recomendados para TLS 1.2 y 1.3
     * Basados en OWASP y NIST guidelines
     */
    'recommended_ciphers' => [
        'TLS_AES_256_GCM_SHA384',
        'TLS_CHACHA20_POLY1305_SHA256',
        'TLS_AES_128_GCM_SHA256',
        'ECDHE-ECDSA-AES256-GCM-SHA384',
        'ECDHE-RSA-AES256-GCM-SHA384',
        'ECDHE-ECDSA-AES128-GCM-SHA256',
        'ECDHE-RSA-AES128-GCM-SHA256',
        'ECDHE-ECDSA-AES256-SHA384',
        'ECDHE-RSA-AES256-SHA384',
        'ECDHE-ECDSA-AES128-SHA256',
        'ECDHE-RSA-AES128-SHA256'
    ],
    
    /**
     * Configuración de certificados SSL
     */
    'certificates' => [
        'ca_certificate' => env('SSL_CA_CERT', '/var/www/html/ssl-ca/ca.cert.pem'),
        'server_certificate' => env('SSL_SERVER_CERT', '/var/www/html/ssl-ca/intranet.seniat.local.cert.pem'),
        'private_key' => env('SSL_PRIVATE_KEY', '/var/www/html/ssl-ca/private/intranet.seniat.local.key.pem'),
        'certificate_chain' => env('SSL_CERT_CHAIN', null),
    ],
    
    /**
     * Configuraciones de seguridad adicionales
     */
    'security_headers' => [
        'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains; preload',
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'DENY',
        'X-XSS-Protection' => '1; mode=block',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Content-Security-Policy' => "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:; connect-src 'self';",
    ],
    
    /**
     * Validación de certificados
     */
    'certificate_validation' => [
        'verify_peer' => true,
        'verify_peer_name' => true,
        'allow_self_signed' => false,
        'cafile' => env('SSL_CA_CERT', '/var/www/html/ssl-ca/ca.cert.pem'),
        'verify_depth' => 10,
    ],
    
    /**
     * Renegociación segura
     */
    'secure_renegotiation' => true,
    
    /**
     * Compresión SSL (deshabilitar por seguridad)
     */
    'ssl_compression' => false,
    
    /**
     * Session resumption (habilitar para rendimiento)
     */
    'session_resumption' => true,
    
    /**
     * OCSP Stapling (habilitar para mejor seguridad y rendimiento)
     */
    'ocsp_stapling' => true,
    'ocsp_stapling_verify' => true,
];
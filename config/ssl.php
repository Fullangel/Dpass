<?php

return [

    /*
    |--------------------------------------------------------------------------
    | SSL Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains SSL/HTTPS configuration for your Laravel application.
    | These settings are used to force HTTPS and handle SSL-related features.
    |
    */

    'force_https' => env('FORCE_HTTPS', true),
    'ssl_port' => env('SSL_PORT', 8443),
    'http_port' => env('HTTP_PORT', 8000),
    'ssl_cert_path' => env('SSL_CERT_PATH', base_path('ssl-ca/temp-cert.pem')),
    'ssl_key_path' => env('SSL_KEY_PATH', base_path('ssl-ca/private/temp-key.pem')),

    /*
    |--------------------------------------------------------------------------
    | Proxy Headers
    |--------------------------------------------------------------------------
    |
    | Headers used by proxy servers to indicate original protocol and port.
    |
    */
    'trusted_proxies' => [
        '127.0.0.1',
        'localhost',
        '10.0.0.0/8',
        '172.16.0.0/12',
        '192.168.0.0/16',
    ],

    'trusted_headers' => [
        'X-Forwarded-Proto',
        'X-Forwarded-For',
        'X-Forwarded-Host',
        'X-Forwarded-Port',
    ],

];
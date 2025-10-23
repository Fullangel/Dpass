<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class SSLServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Forzar HTTPS si está configurado
        if (config('ssl.force_https', true)) {
            $this->forceHTTPS();
        }

        // Configurar proxies de confianza
        $this->configureTrustedProxies();
    }

    /**
     * Force HTTPS for all URLs
     *
     * @return void
     */
    protected function forceHTTPS()
    {
        // Detectar si estamos detrás de un proxy SSL
        $isBehindSSLProxy = $this->isBehindSSLProxy();
        
        // Si estamos detrás de proxy SSL o en HTTPS directo
        if ($isBehindSSLProxy || request()->isSecure()) {
            URL::forceScheme('https');
            
            // Asegurar que el esquema esté configurado correctamente
            $this->app['request']->server->set('HTTPS', 'on');
            $this->app['request']->server->set('SERVER_PORT', 443);
        }
    }

    /**
     * Configure trusted proxies
     *
     * @return void
     */
    protected function configureTrustedProxies()
    {
        // Configurar proxies de confianza
        $trustedProxies = config('ssl.trusted_proxies', ['127.0.0.1', 'localhost']);
        
        if (method_exists($this->app['request'], 'setTrustedProxies')) {
            $this->app['request']->setTrustedProxies($trustedProxies, \Illuminate\Http\Request::HEADER_X_FORWARDED_ALL);
        }
    }

    /**
     * Check if we're behind an SSL proxy
     *
     * @return bool
     */
    protected function isBehindSSLProxy()
    {
        $trustedHeaders = config('ssl.trusted_headers', [
            'X-Forwarded-Proto',
            'X-Forwarded-For',
            'X-Forwarded-Host',
            'X-Forwarded-Port',
        ]);

        // Verificar headers de proxy SSL
        $request = request();
        
        // Verificar X-Forwarded-Proto
        if ($request->header('X-Forwarded-Proto') === 'https') {
            return true;
        }

        // Verificar X-Forwarded-Port
        if ($request->header('X-Forwarded-Port') == '8443') {
            return true;
        }

        // Verificar si el puerto indica SSL
        if ($request->getPort() == 8443) {
            return true;
        }

        return false;
    }
}
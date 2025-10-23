<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceSSL
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Verificar si debemos forzar HTTPS
        if (config('ssl.force_https', true)) {
            // Detectar si estamos detrás de un proxy SSL o en HTTPS directo
            $isSecure = $request->isSecure() || 
                       $request->header('X-Forwarded-Proto') === 'https' ||
                       $request->header('X-Forwarded-Port') == '8443' ||
                       $request->getPort() == 8443;

            // Si no es seguro y no es una petición AJAX, redirigir a HTTPS
            if (!$isSecure && !$request->ajax()) {
                return redirect()->secure($request->getRequestUri());
            }

            // Si estamos detrás de proxy SSL, actualizar el esquema
            if ($isSecure) {
                $request->server->set('HTTPS', 'on');
                $request->server->set('SERVER_PORT', 443);
                \URL::forceScheme('https');
            }
        }

        return $next($request);
    }
}
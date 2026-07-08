<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class IsHasBackendPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $permissionRole = [1, 2, 3, 4]; // Admin, Employee, Reception, Supervisor
        if (Auth::user() && in_array(Auth::user()->myrole, $permissionRole)) {
            return $next($request);
        }

        // Si el usuario no tiene un rol con permiso de backend,
        // redirigimos a la pantalla de login del panel admin
        // para evitar un bucle de redirecciones con la ruta '/'.
        return redirect('/home');
    }
}

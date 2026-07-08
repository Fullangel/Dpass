<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureVisitDestinationAccess
{
    public function handle(Request $request, Closure $next, string $ability = 'view')
    {
        $user = auth()->user();

        if (! $user) {
            abort(403);
        }

        if ($user->hasRole('Admin') || $user->hasRole('supervisor')) {
            if ($ability === 'queue') {
                return $user->visitDestinations()->active()->exists();
            }

            if ($ability === 'delete' && ! $user->hasRole('Admin')) {
                abort(403);
            }

            return $next($request);
        }

        if ($ability === 'queue') {
            if ($user->hasRole('Reception') && $user->visitDestinations()->active()->exists()) {
                return $next($request);
            }

            abort(403);
        }

        $permissionMap = [
            'view' => 'visit-destinations',
            'create' => 'visit-destinations_create',
            'edit' => 'visit-destinations_edit',
            'delete' => 'visit-destinations_delete',
            'queue' => 'visit-destination-queue',
        ];

        $permission = $permissionMap[$ability] ?? $ability;

        if ($user->can($permission)) {
            return $next($request);
        }

        abort(403);
    }
}

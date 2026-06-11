<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    /**
     * Vérifie que l'utilisateur possède au moins un des rôles requis.
     * Usage : ->middleware('role:admin')
     *         ->middleware('role:admin,agent')
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @param  string[] ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Non authentifié.',
            ], 401);
        }

        $userRoles = $user->roles()->pluck('name')->toArray();

        foreach ($roles as $role) {
            if (in_array($role, $userRoles, true)) {
                return $next($request);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Accès refusé. Permission insuffisante.',
        ], 403);
    }
}

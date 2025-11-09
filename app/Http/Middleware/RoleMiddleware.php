<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     * Usage in routes: ->middleware('role:admin') or 'role:manager|sales_rep'
     */
    public function handle(Request $request, Closure $next, string $roles = null)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated.'], 401);
        }

        if (empty($roles)) {
            return $next($request);
        }

        // Accept pipe (|) or comma-separated lists
        $allowed = preg_split('/[|,]/', $roles, -1, PREG_SPLIT_NO_EMPTY);

        foreach ($allowed as $role) {
            $role = trim($role);
            // Compare user_type string with given role
            if ($user->user_type === $role) {
                return $next($request);
            }
        }

        return response()->json(['status' => 'error', 'message' => 'Forbidden.'], 403);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // FR : « role:1,3 » transmet des chaînes ; on les convertit avant la comparaison stricte.
        // EN: “role:1,3” supplies strings; convert them before comparing strictly.
        $allowedRoles = array_map('intval', $roles);

        if (! $request->user() || ! in_array((int) $request->user()->id_role, $allowedRoles, true)) {
            abort(403, 'Accès interdit.');
        }

        return $next($request);
    }
}

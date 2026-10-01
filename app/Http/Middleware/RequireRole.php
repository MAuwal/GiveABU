<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequireRole
{
    public function handle(Request $request, Closure $next, string $role = 'admin')
    {
        $user = $request->user();
        abort_unless($user, 401);
        abort_unless($user->role && strtolower($user->role->role_title) === strtolower($role), 403);

        return $next($request);
    }
}

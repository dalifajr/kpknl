<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect('login');
        }

        $userRole = auth()->user()->role;

        // Role maintenance memiliki hak akses penuh administratif setara superadmin
        if ($userRole === 'maintenance' && (in_array('maintenance', $roles, true) || in_array('superadmin', $roles, true) || in_array('admin', $roles, true))) {
            return $next($request);
        }

        if (!in_array($userRole, $roles, true)) {
            abort(403, 'Akses Ditolak.');
        }

        return $next($request);
    }
}

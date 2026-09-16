<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;

class CheckMaintenanceMode
{
    public function handle(Request $request, Closure $next): Response
    {
        $isMaintenance = Setting::get('maintenance_mode', 'inactive') === 'active';

        if (!$isMaintenance) {
            return $next($request);
        }

        // 1. If logged in as Non-Superadmin AND Non-Maintenance, force logout immediately
        if (Auth::check() && !Auth::user()->isSuperadmin() && !Auth::user()->isMaintenance()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson() || $request->is('api/*') || $request->is('oauth/*')) {
                return response()->json([
                    'error' => 'service_unavailable',
                    'message' => 'Sistem SSO sedang dalam pemeliharaan (Maintenance Mode).'
                ], 503);
            }

            $message = Setting::get('maintenance_message', 'Sistem Single Sign-On (SSO) KPKNL Palembang sedang dalam pemeliharaan berkala.');
            return response()->view('maintenance', compact('message'), 503);
        }

        // 2. Allow authenticated Superadmins and Maintenance full access
        if (Auth::check() && (Auth::user()->isSuperadmin() || Auth::user()->isMaintenance())) {
            return $next($request);
        }

        // 3. Allow public access ONLY to login form & logout endpoint
        if ($request->is('/') || $request->is('login') || $request->routeIs('login') || $request->routeIs('logout')) {
            return $next($request);
        }

        // 4. For JSON / API / OAuth endpoints during maintenance mode
        if ($request->expectsJson() || $request->is('api/*') || $request->is('oauth/*')) {
            return response()->json([
                'error' => 'service_unavailable',
                'message' => 'Sistem SSO sedang dalam pemeliharaan (Maintenance Mode).'
            ], 503);
        }

        // 5. Render maintenance page for all other requests
        $message = Setting::get('maintenance_message', 'Sistem Single Sign-On (SSO) KPKNL Palembang sedang dalam pemeliharaan berkala.');
        return response()->view('maintenance', compact('message'), 503);
    }
}

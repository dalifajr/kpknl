<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\LoginSession;
use Symfony\Component\HttpFoundation\Response;

class EnsureSessionIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $sessionId = session()->getId();
            
            $loginSession = LoginSession::where('session_id', $sessionId)->first();

            if ($loginSession && !$loginSession->is_active) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->with('error', 'Sesi Anda telah diakhiri dari perangkat lain.');
            }
        }

        return $next($request);
    }
}

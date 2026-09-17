<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class EnsureSsoSessionIsValid
{
    /**
     * Handle an incoming request and ensure SSO session is still active.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $token = session('sso_access_token');

            // If user logged in via SSO, verify active session
            if ($token) {
                // Check on every refresh or request after 2 seconds
                $lastChecked = session('sso_last_checked_at');
                if (!$lastChecked || (microtime(true) - $lastChecked) > 2.0) {
                    $isValid = $this->verifyToken($token);

                    if (!$isValid) {
                        Auth::logout();
                        $request->session()->invalidate();
                        $request->session()->regenerateToken();

                        return redirect()->route('login')->with('warning', 'Sesi SSO Anda telah berakhir atau Anda telah logout dari Portal SSO.');
                    }

                    session(['sso_last_checked_at' => microtime(true)]);
                }
            }
        }

        return $next($request);
    }

    /**
     * Verify token validity against SSO Server.
     */
    protected function verifyToken(string $token): bool
    {
        try {
            $ssoBaseUrl = rtrim(env('SSO_BASE_URL', 'http://localhost/sso/public'), '/');
            
            $response = Http::withToken($token)
                ->withoutVerifying()
                ->timeout(2)
                ->get($ssoBaseUrl . '/api/sso/verify-session');

            if ($response->status() === 401) {
                // Token explicitly revoked or expired
                return false;
            }

            return $response->successful() && ($response->json('valid') === true);
        } catch (\Throwable $e) {
            // Fail open on transient network errors
            return true;
        }
    }
}

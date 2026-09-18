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
            // Allow Laravel unit tests actingAs() to run unless testing SSO session specifically
            if (app()->runningUnitTests() && !session()->has('sso_access_token') && !session()->has('_enforce_sso_check')) {
                return $next($request);
            }

            $token = session('sso_access_token');

            // In an SSO-authenticated app, an authenticated session WITHOUT an SSO token is invalid
            if (!$token || !$this->verifyToken($token)) {
                return $this->terminateSession($request, 'Sesi SSO Anda telah berakhir atau Anda telah logout dari Portal SSO.');
            }
        }

        return $next($request);
    }

    /**
     * Terminate local session and redirect to login.
     */
    protected function terminateSession(Request $request, string $message): Response
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Clear remember_web cookie if present
        if (isset($_COOKIE[Auth::getRecallerName()])) {
            \Illuminate\Support\Facades\Cookie::queue(\Illuminate\Support\Facades\Cookie::forget(Auth::getRecallerName()));
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'error' => 'session_terminated',
                'message' => $message,
                'redirect' => route('login'),
            ], 401);
        }

        return redirect()->route('login')->with('warning', $message);
    }

    /**
     * Fast verify token validity against SSO Server.
     */
    protected function verifyToken(string $token): bool
    {
        try {
            // 1. Instant direct database check if on localhost MySQL (<1ms)
            try {
                $pdo = new \PDO("mysql:host=127.0.0.1;dbname=sso_kpknl_palembang", "root", "", [
                    \PDO::ATTR_TIMEOUT => 1,
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                ]);
                $stmt = $pdo->prepare("SELECT revoked, expires_at FROM oauth_tokens WHERE access_token = :token LIMIT 1");
                $stmt->execute(['token' => $token]);
                $row = $stmt->fetch(\PDO::FETCH_ASSOC);

                if ($row) {
                    if ((int) $row['revoked'] === 1 || strtotime($row['expires_at']) < time()) {
                        return false;
                    }
                    return true;
                }
            } catch (\Throwable $dbEx) {
                // Fallback to HTTP API
            }

            // 2. HTTP API fallback
            $ssoBaseUrl = rtrim(env('SSO_BASE_URL', 'http://localhost/sso/public'), '/');
            
            $response = Http::withToken($token)
                ->withoutVerifying()
                ->timeout(2)
                ->get($ssoBaseUrl . '/api/sso/verify-session');

            if ($response->status() === 401) {
                return false;
            }

            return $response->successful() && ($response->json('valid') === true);
        } catch (\Throwable $e) {
            return false;
        }
    }
}

<?php

namespace App\Http\Controllers\OAuth;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Services\OAuthService;
use Illuminate\Http\Request;

class AuthorizationController extends Controller
{
    public function authorize(Request $request)
    {
        $clientId = $request->input('client_id');
        $redirectUri = $request->input('redirect_uri');
        $state = $request->input('state');

        $app = Application::where('client_id', $clientId)
            ->where('status', 'active')
            ->first();

        if (!$app) {
            return response()->json(['error' => 'invalid_client', 'error_description' => 'Aplikasi tidak ditemukan atau tidak aktif.'], 400);
        }

        // Strict RFC 6749 redirect_uri validation (prevent Open Redirect & code interception)
        if ($redirectUri) {
            $parsedRequested = parse_url($redirectUri);
            $parsedRegistered = parse_url($app->redirect_uri);

            $requestedHost = strtolower($parsedRequested['host'] ?? '');
            $registeredHost = strtolower($parsedRegistered['host'] ?? '');
            $currentHost = strtolower($request->getHost());

            $allowedHosts = array_unique(array_filter([
                $registeredHost,
                $currentHost,
                'localhost',
                '127.0.0.1',
            ]));

            $requestedPath = rtrim($parsedRequested['path'] ?? '', '/');
            $registeredPath = rtrim($parsedRegistered['path'] ?? '', '/');

            $isHostValid = in_array($requestedHost, $allowedHosts, true);
            $isPathValid = ($requestedPath === $registeredPath);

            if (!$isHostValid || !$isPathValid) {
                return response()->json([
                    'error' => 'redirect_uri_mismatch',
                    'error_description' => 'Parameter redirect_uri tidak cocok dengan konfigurasi aplikasi terdaftar.',
                ], 400);
            }
        }

        $user = auth()->user();

        // Check if application is assigned to user or user is superadmin
        if (!$user->hasApplicationAccess($app)) {
            return response()->view('oauth.error', [
                'error' => 'Akses Ditolak',
                'message' => "Anda tidak memiliki hak akses ke aplikasi '{$app->name}'. Silakan hubungi Superadmin KPKNL Palembang.",
            ], 403);
        }

        // Check if application is in maintenance mode (503)
        if ($app->is_maintenance && !$user->isSuperadmin() && !$user->isMaintenance()) {
            return response()->view('oauth.error', [
                'error' => 'Aplikasi Dalam Pemeliharaan (503)',
                'message' => "Aplikasi '{$app->name}' sedang dalam pemeliharaan sistem (Status 503). Silakan coba beberapa saat lagi atau hubungi Administrator IT KPKNL Palembang.",
            ], 503);
        }

        // If direct access from SSO dashboard or auto-approve
        $code = OAuthService::createAuthCode($user, $app);

        $rawTarget = $request->input('redirect_uri') ?: $app->redirect_uri;
        $targetUrl = $app->adaptHostToCurrentRequest($rawTarget);

        if (str_contains($targetUrl, '?')) {
            $targetUrl .= "&code={$code}";
        } else {
            $targetUrl .= "?code={$code}";
        }

        if ($state) {
            $targetUrl .= "&state={$state}";
        }

        return redirect()->away($targetUrl);
    }
}

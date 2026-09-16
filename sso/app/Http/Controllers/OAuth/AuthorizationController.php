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

        $user = auth()->user();

        // Check if application is assigned to user or user is superadmin
        if (!$user->hasApplicationAccess($app)) {
            return response()->view('oauth.error', [
                'error' => 'Akses Ditolak',
                'message' => "Anda tidak memiliki hak akses ke aplikasi '{$app->name}'. Silakan hubungi Superadmin KPKNL Palembang.",
            ], 403);
        }

        // If direct access from SSO dashboard or auto-approve
        $code = OAuthService::createAuthCode($user, $app);

        $targetUrl = $app->redirect_uri;
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

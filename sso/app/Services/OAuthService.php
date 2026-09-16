<?php

namespace App\Services;

use App\Models\Application;
use App\Models\OAuthToken;
use App\Models\User;
use Illuminate\Support\Str;

class OAuthService
{
    public static function createAuthCode(User $user, Application $app): string
    {
        $code = Str::random(64);

        OAuthToken::create([
            'user_id' => $user->id,
            'application_id' => $app->id,
            'access_token' => 'pending_' . Str::random(32),
            'code' => $code,
            'expires_at' => now()->addMinutes(10),
        ]);

        ActivityLogService::log(
            'oauth_code_generated',
            "Generasi OAuth authorization code untuk aplikasi '{$app->name}'",
            $user->id,
            $app->id
        );

        return $code;
    }

    public static function exchangeCodeForToken(string $code, string $clientId, string $clientSecret): ?array
    {
        $app = Application::where('client_id', $clientId)
            ->where('client_secret', $clientSecret)
            ->where('status', 'active')
            ->first();

        if (!$app) {
            return null;
        }

        $tokenRecord = OAuthToken::where('code', $code)
            ->where('application_id', $app->id)
            ->where('revoked', false)
            ->where('expires_at', '>', now())
            ->first();

        if (!$tokenRecord) {
            return null;
        }

        $accessToken = Str::random(80);
        $refreshToken = Str::random(80);

        $tokenRecord->update([
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'code' => null, // invalidate auth code after use
            'expires_at' => now()->addHours(8),
        ]);

        ActivityLogService::log(
            'oauth_token_issued',
            "OAuth Access Token diberikan untuk aplikasi '{$app->name}'",
            $tokenRecord->user_id,
            $app->id
        );

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
            'expires_in' => 28800, // 8 jam
        ];
    }

    public static function validateAccessToken(string $accessToken): ?User
    {
        $tokenRecord = OAuthToken::where('access_token', $accessToken)
            ->where('revoked', false)
            ->where('expires_at', '>', now())
            ->first();

        return $tokenRecord ? $tokenRecord->user : null;
    }
}

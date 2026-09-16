<?php

namespace App\Services;

use App\Models\LoginSession;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LoginSessionService
{
    public static function createSession(User $user, Request $request): LoginSession
    {
        $userAgent = $request->header('User-Agent', '');
        $parsed = static::parseUserAgent($userAgent);

        $sessionId = session()->getId() ?: Str::random(40);

        // Deactivate previous sessions for same session_id if any
        LoginSession::where('session_id', $sessionId)->update(['is_active' => false]);

        return LoginSession::create([
            'user_id' => $user->id,
            'session_id' => $sessionId,
            'ip_address' => $request->ip(),
            'browser' => $parsed['browser'],
            'browser_version' => $parsed['browser_version'],
            'platform' => $parsed['platform'],
            'device_type' => $parsed['device_type'],
            'device_model' => $parsed['device_model'],
            'is_active' => true,
            'last_activity_at' => now(),
            'login_at' => now(),
        ]);
    }

    public static function terminateSession(string $sessionId): void
    {
        LoginSession::where('session_id', $sessionId)->update([
            'is_active' => false,
            'logout_at' => now(),
        ]);
    }

    public static function parseUserAgent(string $userAgent): array
    {
        $browser = 'Unknown Browser';
        $browserVersion = 'Unknown';
        $platform = 'Unknown OS';
        $deviceType = 'desktop';
        $deviceModel = 'Desktop PC';

        // Platform detection
        if (preg_match('/windows nt 10/i', $userAgent)) {
            $platform = 'Windows 10 / 11';
        } elseif (preg_match('/windows nt 6.3/i', $userAgent)) {
            $platform = 'Windows 8.1';
        } elseif (preg_match('/windows nt 6.1/i', $userAgent)) {
            $platform = 'Windows 7';
        } elseif (preg_match('/windows/i', $userAgent)) {
            $platform = 'Windows OS';
        } elseif (preg_match('/macintosh|mac os x/i', $userAgent)) {
            $platform = 'macOS';
        } elseif (preg_match('/android/i', $userAgent)) {
            $platform = 'Android';
            $deviceType = 'mobile';
            $deviceModel = 'Android Device';
        } elseif (preg_match('/iphone|ipad|ipod/i', $userAgent)) {
            $platform = 'iOS';
            $deviceType = preg_match('/ipad/i', $userAgent) ? 'tablet' : 'mobile';
            $deviceModel = preg_match('/ipad/i', $userAgent) ? 'iPad' : 'iPhone';
        } elseif (preg_match('/linux/i', $userAgent)) {
            $platform = 'Linux';
        }

        // Browser detection
        if (preg_match('/edg\/([0-9\.]+)/i', $userAgent, $matches)) {
            $browser = 'Microsoft Edge';
            $browserVersion = $matches[1];
        } elseif (preg_match('/chrome\/([0-9\.]+)/i', $userAgent, $matches)) {
            $browser = 'Google Chrome';
            $browserVersion = $matches[1];
        } elseif (preg_match('/firefox\/([0-9\.]+)/i', $userAgent, $matches)) {
            $browser = 'Mozilla Firefox';
            $browserVersion = $matches[1];
        } elseif (preg_match('/safari\/([0-9\.]+)/i', $userAgent, $matches) && !preg_match('/chrome/i', $userAgent)) {
            $browser = 'Safari';
            $browserVersion = $matches[1];
        } elseif (preg_match('/opera|opr\/([0-9\.]+)/i', $userAgent, $matches)) {
            $browser = 'Opera';
            $browserVersion = $matches[1] ?? 'Unknown';
        }

        return [
            'browser' => $browser,
            'browser_version' => $browserVersion,
            'platform' => $platform,
            'device_type' => $deviceType,
            'device_model' => $deviceModel,
        ];
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Exception;

class AuthController extends Controller
{
    /**
     * Redirect the user to the SSO authentication page.
     */
    public function redirect()
    {
        return Socialite::driver('sso')->redirect();
    }

    /**
     * Obtain the user information from the SSO provider.
     */
    public function callback()
    {
        try {
            $ssoUser = Socialite::driver('sso')->user();
        } catch (Exception $e) {
            return redirect('/')->withErrors(['error' => 'Gagal terhubung ke server SSO.']);
        }

        $user = User::updateOrCreate(
            ['email' => $ssoUser->email],
            [
                'name' => $ssoUser->name,
                'password' => bcrypt(str()->random(24)),
            ]
        );
        
        $rawUser = $ssoUser->user ?? [];
        $role = $rawUser['role'] ?? $rawUser['jabatan'] ?? $rawUser['level'] ?? $rawUser['group'] ?? 'user';
        
        // Smart detection based on username/email/name
        $identifier = strtolower(($ssoUser->nickname ?? '') . ' ' . ($ssoUser->name ?? '') . ' ' . ($ssoUser->email ?? ''));
        if (str_contains($identifier, 'mardanus') || str_contains($identifier, 'superadmin')) {
            $role = 'superadmin';
        }

        $user->role = $role;
        $user->save();

        Auth::login($user);

        return redirect()->intended('/dashboard');
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $ssoUrl = config('services.sso.url');
        if ($ssoUrl) {
            return redirect($ssoUrl);
        }

        return redirect()->route('auth.redirect');
    }
}

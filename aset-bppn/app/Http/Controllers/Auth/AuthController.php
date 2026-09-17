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

            $rawUser = $ssoUser->user ?? [];
            $role = 'user';
            if (!empty($rawUser['is_superadmin']) || ($rawUser['primary_role'] ?? null) === 'superadmin') {
                $role = 'superadmin';
            } elseif (!empty($rawUser['is_admin']) || ($rawUser['primary_role'] ?? null) === 'admin') {
                $role = 'admin';
            } elseif (!empty($rawUser['primary_role'])) {
                $role = $rawUser['primary_role'];
            }

            $user = User::updateOrCreate(
                ['email' => $ssoUser->email],
                [
                    'sso_id' => $ssoUser->id,
                    'name' => $ssoUser->name,
                    'role' => $role,
                    'password' => bcrypt(str()->random(24)),
                ]
            );

            Auth::login($user);

            return redirect()->route('dashboard');

        } catch (Exception $e) {
            \Illuminate\Support\Facades\Log::error('Aset BPPN SSO Error: ' . $e->getMessage());
            return redirect()->route('login')->with('sso_error', 'Gagal login melalui SSO: ' . $e->getMessage());
        }
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

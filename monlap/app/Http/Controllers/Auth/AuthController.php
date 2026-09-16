<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('sso')->stateless()->redirect();
    }

    public function callback()
    {
        try {
            $ssoUser = Socialite::driver('sso')->stateless()->user();
            
            $user = User::firstOrNew(['sso_id' => $ssoUser->id]);
            $user->name = $ssoUser->name;
            $user->email = $ssoUser->email;
            $user->username = $ssoUser->nickname ?: ($ssoUser->user['username'] ?? \Illuminate\Support\Str::slug($ssoUser->name, '.') . '_' . $ssoUser->id);
            $user->last_login_at = now();
            
            // Hanya update role dari SSO, dan gunakan primary_role
            if (isset($ssoUser->user['primary_role'])) {
                $user->role = $ssoUser->user['primary_role'];
            }
            
            $user->save();

            Auth::login($user);

            return redirect()->route('dashboard');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Monlap SSO Login Error: ' . $e->getMessage());
            return redirect('/')->withErrors(['error' => 'Gagal login melalui SSO: ' . $e->getMessage()]);
        }
    }

    public function logout()
    {
        Auth::logout();
        return redirect(config('services.sso.url') . '/dashboard');
    }
}

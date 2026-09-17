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
            
            // Hanya update role dari SSO, dan gunakan primary_role
            if (isset($ssoUser->user['primary_role'])) {
                $user->role = $ssoUser->user['primary_role'];
            }
            
            $user->save();

            $user->update(['last_login_at' => now()]);

            Auth::login($user);

            return redirect()->route('dashboard');

        } catch (\Exception $e) {
            return redirect('/')->withErrors(['error' => 'Gagal login melalui SSO: ' . $e->getMessage()]);
        }
    }

    public function logout()
    {
        Auth::logout();
        return redirect(config('services.sso.url') . '/dashboard');
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class AuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('sso')->redirect();
    }

    public function callback()
    {
        try {
            try {
                $ssoUser = Socialite::driver('sso')->user();
            } catch (InvalidStateException $stateEx) {
                // If IdP-Initiated from SSO Dashboard, state was not set in client session; exchange code statelessly
                $ssoUser = Socialite::driver('sso')->stateless()->user();
            }
            
            $user = User::firstOrNew(['sso_id' => $ssoUser->id]);
            $user->name = $ssoUser->name;
            $user->email = $ssoUser->email;
            
            $role = \App\Support\SsoRole::resolve($ssoUser->user ?? []);
            $user->role = in_array($role, ['maintenance', 'superadmin', 'admin'], true) ? $role : 'user';
            $user->username = $ssoUser->user['username'] ?? $user->username;
            
            $user->save();

            $user->update(['last_login_at' => now()]);

            session([
                'sso_access_token' => $ssoUser->token,
                'sso_user_id' => $ssoUser->id,
            ]);

            Auth::login($user, false);

            return redirect()->route('dashboard');

        } catch (\Exception $e) {
            return redirect('/')->withErrors(['error' => 'Gagal login melalui SSO: ' . $e->getMessage()]);
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect(config('services.sso.url') . '/dashboard');
    }
}

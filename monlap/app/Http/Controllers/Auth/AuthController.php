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
            
            $ssoId = (string) $ssoUser->id;
            $email = $ssoUser->email;
            $username = $ssoUser->user['username'] ?? $ssoUser->nickname ?? null;
            if (empty($username) && !empty($email)) {
                $username = explode('@', $email)[0];
            }

            // 1. Cari berdasarkan email terlebih dahulu (identitas unik utama)
            $user = null;
            if (!empty($email)) {
                $user = User::where('email', $email)->first();
            }

            // 2. Jika tidak ditemukan via email, cari via sso_id
            if (!$user && !empty($ssoId)) {
                $user = User::where('sso_id', $ssoId)->first();
            }

            // 3. Jika tidak ditemukan, cari via username
            if (!$user && !empty($username)) {
                $user = User::where('username', $username)->first();
            }

            // 4. Jika baru sama sekali, instansiasi
            if (!$user) {
                $user = new User();
            }

            // 5. Bersihkan sso_id pada record lain yang mungkin bentrok
            if (!empty($ssoId)) {
                User::where('sso_id', $ssoId)
                    ->where('id', '!=', $user->id ?? 0)
                    ->update(['sso_id' => null]);
            }

            // 6. Bersihkan username pada record lain jika bentrok
            if (!empty($username)) {
                User::where('username', $username)
                    ->where('id', '!=', $user->id ?? 0)
                    ->update(['username' => null]);
            }

            $user->sso_id = $ssoId;
            $user->name = $ssoUser->name;
            $user->email = $email;
            $user->username = $username;
            
            $rawProfile = is_array($ssoUser->user) ? $ssoUser->user : [];
            if (!empty($ssoUser->role) && empty($rawProfile['app_role']) && empty($rawProfile['primary_role'])) {
                $rawProfile['app_role'] = $ssoUser->role;
            }
            $role = \App\Support\SsoRole::resolve($rawProfile);
            $user->role = in_array($role, ['maintenance', 'superadmin', 'admin'], true) ? $role : 'user';
            
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

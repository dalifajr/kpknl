<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\LoginSessionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $loginInfoStatus = \App\Models\Setting::get('login_info_status', 'active');
        $loginInfoBadge = \App\Models\Setting::get('login_info_badge', 'INFO LAYANAN & KEAMANAN');
        $loginInfoTitle = \App\Models\Setting::get('login_info_title', 'Selamat Datang di Portal Single Sign-On (SSO)');
        $loginInfoContent = \App\Models\Setting::get('login_info_content', 'Satu akun resmi untuk mengautentikasi dan mengakses seluruh aplikasi internal Kekayaan Negara & Lelang Palembang secara aman, efisien, dan terintegrasi.');

        $isMaintenance = \App\Models\Setting::get('maintenance_mode', 'inactive') === 'active';
        $maintenanceMessage = \App\Models\Setting::get('maintenance_message', 'Sistem Single Sign-On (SSO) KPKNL Palembang sedang dalam pemeliharaan berkala.');

        return view('auth.login', compact('loginInfoStatus', 'loginInfoBadge', 'loginInfoTitle', 'loginInfoContent', 'isMaintenance', 'maintenanceMessage'));
    }



    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ], [
            'login.required' => 'Username atau Email wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $loginInput = $request->input('login');

        // Check if user is locked out by IP or login
        $rateKey = 'login_attempts:' . strtolower($loginInput) . '|' . $request->ip();
        $attempts = cache()->get($rateKey, 0);

        if ($attempts >= 5) {
            ActivityLogService::log(
                'login_failed_lockout',
                "Percobaan login terblokir karena salah password 5x untuk '{$loginInput}'",
                null,
                null,
                ['ip' => $request->ip()]
            );

            return back()->withInput()->withErrors([
                'login' => 'Akun/IP Anda sementara dikunci selama 15 menit karena 5x kesalahan password berturut-turut.',
            ]);
        }

        // Determine if login is email or username
        $fieldType = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $user = User::where($fieldType, strtolower($loginInput))->first();

        // Strict Maintenance Mode Check: Prevent non-superadmin from logging in
        if (\App\Models\Setting::get('maintenance_mode', 'inactive') === 'active') {
            if (!$user || !$user->isSuperadmin()) {
                ActivityLogService::log(
                    'login_blocked_maintenance',
                    "Percobaan login ditolak karena Maintenance Mode aktif untuk '{$loginInput}'",
                    $user ? $user->id : null
                );

                return back()->withInput()->withErrors([
                    'login' => 'Sistem SSO sedang dalam pemeliharaan (Maintenance Mode). Hanya Superadmin yang diperbolehkan untuk masuk saat ini.',
                ]);
            }
        }

        if (!$user || !Auth::validate([$fieldType => $loginInput, 'password' => $request->password])) {
            cache()->put($rateKey, $attempts + 1, now()->addMinutes(15));

            ActivityLogService::log(
                'login_failed',
                "Percobaan login gagal untuk '{$loginInput}'",
                $user ? $user->id : null
            );

            $remaining = 5 - ($attempts + 1);
            $errMsg = 'Username/Email atau password salah.';
            if ($remaining > 0) {
                $errMsg .= " Sisa percobaan: {$remaining}x sebelum akun dikunci.";
            }

            return back()->withInput()->withErrors(['login' => $errMsg]);
        }

        if ($user->status !== 'active') {
            return back()->withInput()->withErrors([
                'login' => 'Akun Anda dalam status ' . $user->status . '. Silakan hubungi Superadmin.',
            ]);
        }



        // Login success - clear rate limit
        cache()->forget($rateKey);

        Auth::login($user, $request->has('remember'));
        $request->session()->regenerate();

        // Update user last login
        $user->update(['last_login_at' => now()]);

        // Create login session record (device tracking)
        LoginSessionService::createSession($user, $request);

        // Activity log
        ActivityLogService::log(
            'login_success',
            "User {$user->name} ({$user->username}) berhasil login ke SSO",
            $user->id
        );

        // Create personal notification entry for SsoNotification Hub
        \App\Models\SsoNotification::create([
            'user_id' => $user->id,
            'title' => 'Login Sesi Berhasil',
            'message' => 'Anda telah berhasil masuk ke SSO KPKNL Palembang dari IP ' . $request->ip(),
            'type' => 'login',
            'icon' => 'fa-solid fa-right-to-bracket text-primary',
            'app_name' => 'SSO Identity Provider',
            'link' => route('login-sessions.index'),
        ]);

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();

            ActivityLogService::log(
                'logout',
                "User {$user->name} ({$user->username}) logout dari SSO",
                $user->id
            );

            LoginSessionService::terminateSession($request->session()->getId());
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar dari sistem.');
    }
}

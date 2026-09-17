<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class SsoController extends Controller
{
    /**
     * Show dedicated Login Page without text/password form
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Redirect to SSO KPKNL Palembang OAuth2 Server
     */
    public function redirect(Request $request)
    {
        $ssoBaseUrl = rtrim(env('SSO_BASE_URL', 'http://sso.test'), '/');
        $clientId = env('SSO_CLIENT_ID', 'client_simpatik_kepegawaian');
        $redirectUri = env('SSO_REDIRECT_URI', url('/auth/sso/callback'));
        $state = Str::random(40);

        session(['sso_state' => $state]);

        $query = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'state' => $state,
        ]);

        return redirect($ssoBaseUrl . '/oauth/authorize?' . $query);
    }

    /**
     * Handle OAuth2 Callback from SSO KPKNL Palembang
     */
    public function callback(Request $request)
    {
        $code = $request->input('code');
        $state = $request->input('state');
        $savedState = session('sso_state');

        if (!$code) {
            return redirect()->route('login')->with('error', 'Otorisasi SSO dibatalkan atau tidak menerima kode otorisasi.');
        }

        // Anti-OAuth Login CSRF validation (RFC 6749 Section 10.12)
        if (!$state || !$savedState || !hash_equals((string) $savedState, (string) $state)) {
            return redirect()->route('login')->with('error', 'Validasi token keamanan sesi (OAuth state) gagal atau sesi Anda telah kedaluwarsa. Silakan ulangi proses masuk.');
        }

        // Clear used state to prevent replay
        session()->forget('sso_state');

        try {
            $ssoBaseUrl = rtrim(env('SSO_BASE_URL', 'http://sso.test'), '/');
            $clientId = env('SSO_CLIENT_ID', 'client_simpatik_kepegawaian');
            $clientSecret = env('SSO_CLIENT_SECRET', 'secret_simpatik_kpknl_2026');
            $redirectUri = env('SSO_REDIRECT_URI', url('/auth/sso/callback'));

            // 1. Exchange Authorization Code for Access Token
            $response = Http::asForm()->timeout(15)->post($ssoBaseUrl . '/oauth/token', [
                'grant_type' => 'authorization_code',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'redirect_uri' => $redirectUri,
                'code' => $code,
            ]);

            if (!$response->successful()) {
                Log::warning('Gagal exchange token SSO SI-KEP: ' . $response->body());
                return redirect()->route('login')->with('error', 'Gagal memverifikasi token ke server SSO KPKNL Palembang.');
            }

            $tokenData = $response->json();
            $accessToken = $tokenData['access_token'] ?? null;

            if (!$accessToken) {
                return redirect()->route('login')->with('error', 'Access token tidak ditemukan dalam respons SSO.');
            }

            // 2. Fetch User Profile from SSO
            $userResponse = Http::withToken($accessToken)->timeout(15)->get($ssoBaseUrl . '/api/user');
            if (!$userResponse->successful()) {
                return redirect()->route('login')->with('error', 'Gagal mengambil data profil dari server SSO.');
            }

            $userData = $userResponse->json();
            $ssoUser = $userData['data'] ?? $userData;

            if (!$ssoUser || !isset($ssoUser['id'])) {
                return redirect()->route('login')->with('error', 'Data profil SSO tidak valid.');
            }

            // 3. Find or Create Local User
            $email = $ssoUser['email'] ?? ($ssoUser['username'] . '@kpknl.go.id');
            $role = $ssoUser['primary_role'] ?? ($ssoUser['is_superadmin'] ? 'superadmin' : 'user');

            $localUser = User::updateOrCreate(
                ['sso_user_id' => $ssoUser['id']],
                [
                    'name' => $ssoUser['name'],
                    'username' => $ssoUser['username'] ?? explode('@', $email)[0],
                    'email' => $email,
                    'role' => $role,
                    'password' => bcrypt(Str::random(32)),
                ]
            );

            // 4. Authenticate in SI-KEP Application and persist SSO token in session
            session([
                'sso_access_token' => $accessToken,
                'sso_user_id' => $ssoUser['id'],
            ]);

            Auth::login($localUser, true);

            return redirect()->intended(route('dashboard'))
                ->with('success', "Selamat datang kembali, {$localUser->name}! Anda berhasil masuk melalui SSO KPKNL Palembang.");

        } catch (Exception $e) {
            Log::error('SSO Callback Exception: ' . $e->getMessage());
            return redirect()->route('login')->with('error', 'Terjadi kesalahan sistem saat otentikasi SSO: ' . $e->getMessage());
        }
    }

    /**
     * Logout and destroy session
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar dari sistem SI-KEP.');
    }
}

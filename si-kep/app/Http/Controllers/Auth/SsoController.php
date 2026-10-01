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
     * Resolve SSO base URL adapting dynamically to current request host (e.g. LAN IP 10.24.7.207 vs localhost).
     */
    protected function getSsoBaseUrl(Request $request): string
    {
        $baseUrl = env('SSO_BASE_URL', 'http://localhost/sso/public');
        $currentHost = $request->getHost();

        if ($currentHost && !in_array(strtolower($currentHost), ['localhost', '127.0.0.1'])) {
            $parsed = parse_url($baseUrl);
            $targetHost = $parsed['host'] ?? 'localhost';
            if (in_array(strtolower($targetHost), ['localhost', '127.0.0.1'])) {
                $scheme = $request->getScheme();
                $port = $request->getPort() && !in_array($request->getPort(), [80, 443]) ? ':' . $request->getPort() : '';
                $path = $parsed['path'] ?? '/sso/public';
                return "{$scheme}://{$currentHost}{$port}{$path}";
            }
        }

        return rtrim($baseUrl, '/');
    }

    /**
     * Resolve SSO Redirect URI matching current request host dynamically.
     */
    protected function getRedirectUri(Request $request): string
    {
        $configuredUri = env('SSO_REDIRECT_URI');
        if ($configuredUri) {
            $currentHost = $request->getHost();
            if ($currentHost && !in_array(strtolower($currentHost), ['localhost', '127.0.0.1'])) {
                $parsed = parse_url($configuredUri);
                $targetHost = $parsed['host'] ?? 'localhost';
                if (in_array(strtolower($targetHost), ['localhost', '127.0.0.1'])) {
                    $scheme = $request->getScheme();
                    $port = $request->getPort() && !in_array($request->getPort(), [80, 443]) ? ':' . $request->getPort() : '';
                    $path = $parsed['path'] ?? '/si-kep/public/auth/sso/callback';
                    return "{$scheme}://{$currentHost}{$port}{$path}";
                }
            }
            return $configuredUri;
        }

        return url('/auth/sso/callback');
    }

    /**
     * Direct redirect to SSO KPKNL Palembang login / authorization page.
     * Eliminates intermediate landing screen as requested.
     */
    public function showLogin(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        // If an explicit error occurred (e.g. CSRF state mismatch or auth canceled),
        // render the login error card so the user can inspect the issue and retry.
        if (session('error')) {
            return view('auth.login');
        }

        return $this->redirect($request);
    }

    /**
     * Redirect to SSO KPKNL Palembang OAuth2 Server
     */
    public function redirect(Request $request)
    {
        if (Auth::check() && !$request->has('force') && !$request->has('reauth')) {
            return redirect()->route('dashboard');
        }

        if ($request->has('force') || $request->has('reauth')) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        $ssoBaseUrl = $this->getSsoBaseUrl($request);
        $clientId = env('SSO_CLIENT_ID', 'client_simpatik_kepegawaian');
        $redirectUri = $this->getRedirectUri($request);
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

        // Anti-OAuth Login CSRF validation: Required if login was initiated from client login page
        if ($savedState && (!$state || !hash_equals((string) $savedState, (string) $state))) {
            return redirect()->route('login')->with('error', 'Validasi token keamanan sesi (OAuth state) gagal atau sesi Anda telah kedaluwarsa. Silakan ulangi proses masuk.');
        }

        // Clear used state to prevent replay
        session()->forget('sso_state');

        try {
            $ssoBaseUrl = $this->getSsoBaseUrl($request);
            $clientId = env('SSO_CLIENT_ID', 'client_simpatik_kepegawaian');
            $clientSecret = env('SSO_CLIENT_SECRET', 'secret_simpatik_kpknl_2026');
            $redirectUri = $this->getRedirectUri($request);

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
            $ssoUserId = $ssoUser['id'] ?? null;
            $email = $ssoUser['email'] ?? ($ssoUser['username'] . '@kpknl.go.id');
            $username = $ssoUser['username'] ?? explode('@', $email)[0];
            $name = $ssoUser['name'] ?? $username;
            $avatarUrl = $ssoUser['avatar_url'] ?? null;

            $role = \App\Support\SsoRole::resolve($ssoUser);
            $role = match ($role) {
                'maintenance', 'superadmin', 'administrator' => $role,
                'admin' => 'administrator',
                default => 'user',
            };

            // Cari user lokal: prioritas email (identitas unik utama), lalu sso_user_id, lalu username
            $localUser = null;
            if (!empty($email)) {
                $localUser = User::where('email', $email)->first();
            }

            if (!$localUser && !empty($ssoUserId)) {
                $localUser = User::where('sso_user_id', $ssoUserId)->first();
            }

            if (!$localUser && !empty($username)) {
                $localUser = User::where('username', $username)->first();
            }

            // Bersihkan sso_user_id pada record lain yang mungkin bentrok untuk menjaga unique constraint
            if (!empty($ssoUserId)) {
                User::where('sso_user_id', $ssoUserId)
                    ->when($localUser, fn ($q) => $q->where('id', '!=', $localUser->id))
                    ->update(['sso_user_id' => null]);
            }

            if ($localUser) {
                $localUser->update([
                    'sso_user_id' => $ssoUserId,
                    'name' => $name,
                    'username' => $username,
                    'email' => $email,
                    'role' => $role,
                    'avatar_url' => $avatarUrl ?? $localUser->avatar_url,
                ]);
            } else {
                $localUser = User::create([
                    'sso_user_id' => $ssoUserId,
                    'name' => $name,
                    'username' => $username,
                    'email' => $email,
                    'role' => $role,
                    'password' => bcrypt(Str::random(32)),
                    'avatar_url' => $avatarUrl,
                ]);
            }

            // 4. Authenticate in SI-KEP Application and persist SSO token in session
            session([
                'sso_access_token' => $accessToken,
                'sso_user_id' => $ssoUser['id'],
            ]);

            Auth::login($localUser, false);

            return redirect()->intended(route('dashboard'))
                ->with('success', "Selamat datang kembali, {$localUser->name}! Anda berhasil masuk melalui SSO KPKNL Palembang.");

        } catch (Exception $e) {
            Log::error('SSO Callback Exception: ' . $e->getMessage());
            return redirect()->route('login')->with('error', 'Terjadi kesalahan sistem saat otentikasi SSO: ' . $e->getMessage());
        }
    }

    /**
     * Logout and destroy session, returning to SSO Portal Dashboard
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $ssoDashboardUrl = $this->getSsoBaseUrl($request) . '/dashboard';
        return redirect($ssoDashboardUrl);
    }
}

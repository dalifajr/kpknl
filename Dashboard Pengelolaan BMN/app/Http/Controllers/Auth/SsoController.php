<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;
use PDO;

class SsoController extends Controller
{
    /**
     * Show dedicated Login Page
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    /**
     * Redirect to SSO KPKNL Palembang
     */
    public function redirect(Request $request)
    {
        $ssoBaseUrl = env('SSO_BASE_URL', 'http://localhost/sso-kpknl-palembang/public');
        $clientId = env('SSO_CLIENT_ID', 'client_dashboard_bmn');
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
     * Handle OAuth2 Callback from SSO & Validate Application Assignment
     */
    public function callback(Request $request)
    {
        $code = $request->input('code');
        $state = $request->input('state');

        if (!$code) {
            return redirect()->route('login')->with('error', 'Otorisasi SSO dibatalkan atau tidak menerima kode.');
        }

        try {
            $ssoBaseUrl = env('SSO_BASE_URL', 'http://localhost/sso-kpknl-palembang/public');
            $clientId = env('SSO_CLIENT_ID', 'client_dashboard_bmn');
            $clientSecret = env('SSO_CLIENT_SECRET', 'secret_bmn_kpknl_2026');
            $redirectUri = env('SSO_REDIRECT_URI', url('/auth/sso/callback'));

            // 1. Exchange Token
            $response = Http::asForm()->timeout(15)->post($ssoBaseUrl . '/oauth/token', [
                'grant_type' => 'authorization_code',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'redirect_uri' => $redirectUri,
                'code' => $code,
            ]);

            if (!$response->successful()) {
                Log::warning('Gagal exchange token SSO: ' . $response->body());
                return redirect()->route('login')->with('error', 'Gagal memverifikasi token ke server SSO KPKNL Palembang.');
            }

            $tokenData = $response->json();
            $accessToken = $tokenData['access_token'] ?? null;
            $userPayload = $tokenData['user'] ?? null;

            if (!$userPayload && $accessToken) {
                $userResponse = Http::withToken($accessToken)->get($ssoBaseUrl . '/api/user');
                if ($userResponse->successful()) {
                    $json = $userResponse->json();
                    $userPayload = $json['data'] ?? $json;
                }
            }

            if (!$userPayload) {
                return redirect()->route('login')->with('error', 'Gagal mengambil data profil dari server SSO.');
            }

            $ssoUserId = $userPayload['id'] ?? null;
            $email = $userPayload['email'] ?? ($userPayload['username'] ? $userPayload['username'].'@kpknl.go.id' : 'user@kpknl.go.id');
            $name = $userPayload['name'] ?? 'Pengguna SSO';
            $isSuperadmin = !empty($userPayload['is_superadmin']) && $userPayload['is_superadmin'] === true;

            // 2. CHECK APPLICATION ASSIGNMENT
            // Superadmin has universal access, otherwise verify in sso_kpknl_palembang database
            $isAssigned = $isSuperadmin;

            if (!$isAssigned && $ssoUserId) {
                try {
                    $pdo = new PDO('mysql:host=127.0.0.1;dbname=sso_kpknl_palembang', 'root', '');
                    $stmt = $pdo->prepare("
                        SELECT COUNT(*) FROM user_application ua 
                        JOIN applications a ON ua.application_id = a.id 
                        WHERE ua.user_id = :user_id 
                        AND (a.id = 7 OR a.client_id = :client_id OR a.slug LIKE '%bmn%')
                    ");
                    $stmt->execute([
                        'user_id' => $ssoUserId,
                        'client_id' => $clientId,
                    ]);
                    $isAssigned = $stmt->fetchColumn() > 0;
                } catch (Exception $dbEx) {
                    Log::warning('SSO DB direct check failed: ' . $dbEx->getMessage());
                    // Fallback to true if DB connection to SSO is inaccessible
                    $isAssigned = true;
                }
            }

            if (!$isAssigned) {
                Log::warning("User {$name} (ID: {$ssoUserId}) ditolak masuk: Belum di-assign ke aplikasi Dashboard BMN.");
                return redirect()->route('login')->with('error', "Akses Ditolak: Akun '{$name}' belum diberikan izin akses/assign ke aplikasi Executive Dashboard Pengelolaan BMN oleh Administrator SSO. Silakan hubungi Administrator KPKNL Palembang.");
            }

            // 3. User is authorized, determine role
            $role = $userPayload['primary_role'] ?? ($userPayload['role'] ?? 'pegawai');
            if ($isSuperadmin) {
                $role = 'superadmin';
            } elseif (!empty($userPayload['is_admin']) && $userPayload['is_admin'] === true) {
                $role = 'admin';
            }

            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'username' => $userPayload['username'] ?? Str::slug($name),
                    'role' => $role,
                    'sso_id' => $ssoUserId,
                    'avatar_url' => $userPayload['avatar_url'] ?? null,
                    'password' => bcrypt(Str::random(32)),
                ]
            );

            Auth::login($user, true);

            return redirect()->to(rtrim(url('/'), '/') . '/')->with('success', "Selamat datang, {$user->name}! Berhasil masuk via SSO KPKNL Palembang sebagai [{$user->getRoleLabel()}].");

        } catch (Exception $e) {
            Log::error('SSO Exception: ' . $e->getMessage());
            return redirect()->route('login')->with('error', 'Koneksi SSO terputus: ' . $e->getMessage());
        }
    }

    /**
     * Switch user role for testing / simulation
     */
    public function switchRole(Request $request, string $role)
    {
        $validRoles = ['superadmin', 'admin', 'pegawai', 'eksekutif'];
        if (!in_array($role, $validRoles)) {
            $role = 'pegawai';
        }

        $names = [
            'superadmin' => 'Mardanus',
            'admin' => 'Seksi PKN',
            'pegawai' => 'Budi Santoso',
            'eksekutif' => 'Kepala KPKNL Palembang',
        ];

        $emails = [
            'superadmin' => 'mardanus@kpknl.go.id',
            'admin' => 'seksipkn@kpknl.test',
            'pegawai' => 'budi@kpknl.go.id',
            'eksekutif' => 'kepala.kantor@kemenkeu.go.id',
        ];

        $user = User::updateOrCreate(
            ['email' => $emails[$role]],
            [
                'name' => $names[$role],
                'username' => Str::slug($role),
                'role' => $role,
                'password' => bcrypt('password123'),
            ]
        );

        Auth::login($user, true);

        return redirect()->route('dashboard')->with('success', "Beralih peran: Masuk sebagai {$user->name} [{$user->getRoleLabel()}].");
    }

    /**
     * Logout and return to SSO KPKNL Palembang Dashboard
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $ssoDashboardUrl = env('SSO_BASE_URL', 'http://localhost/sso-kpknl-palembang/public') . '/dashboard';
        return redirect($ssoDashboardUrl);
    }
}

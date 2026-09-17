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
     * Resolve SSO base URL adapting dynamically to current request host if client accesses via LAN IP.
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

        return $baseUrl;
    }

    /**
     * Redirect unauthenticated user directly to SSO KPKNL Palembang
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
        $clientId = env('SSO_CLIENT_ID', 'client_peminjaman_lelang');
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

        if (!$code) {
            return redirect()->route('sso.redirect')->with('error', 'Otorisasi SSO dibatalkan atau tidak menerima kode.');
        }

        try {
            $ssoBaseUrl = $this->getSsoBaseUrl($request);
            $clientId = env('SSO_CLIENT_ID', 'client_peminjaman_lelang');
            $clientSecret = env('SSO_CLIENT_SECRET', 'secret_peminjaman_kpknl_2026');
            $redirectUri = env('SSO_REDIRECT_URI', url('/auth/sso/callback'));

            // 1. Exchange authorization code for token
            $response = Http::asForm()->timeout(15)->post($ssoBaseUrl . '/oauth/token', [
                'grant_type' => 'authorization_code',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'redirect_uri' => $redirectUri,
                'code' => $code,
            ]);

            if (!$response->successful()) {
                Log::warning('Gagal exchange token SSO: ' . $response->body());
                return redirect()->route('sso.redirect')->with('error', 'Gagal memverifikasi token ke server SSO KPKNL Palembang.');
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
                return redirect()->route('sso.redirect')->with('error', 'Gagal mengambil data profil dari server SSO.');
            }

            $ssoUserId = $userPayload['id'] ?? null;
            $email = $userPayload['email'] ?? ($userPayload['username'] ? $userPayload['username'].'@kpknl.go.id' : 'user@kpknl.go.id');
            $name = $userPayload['name'] ?? 'Pengguna SSO';
            $isSuperadmin = !empty($userPayload['is_superadmin']) && $userPayload['is_superadmin'] === true;
            $isMaintenance = (!empty($userPayload['is_maintenance']) && $userPayload['is_maintenance'] === true)
                || (!empty($userPayload['primary_role']) && $userPayload['primary_role'] === 'maintenance')
                || (isset($userPayload['roles']) && in_array('maintenance', (array)$userPayload['roles']))
                || (($userPayload['username'] ?? '') === 'maintenance');

            // 2. Check application assignment in SSO DB
            $isAssigned = $isSuperadmin || $isMaintenance;
            $assignedRole = null;

            if ($ssoUserId) {
                try {
                    $pdo = new PDO("mysql:host=127.0.0.1;dbname=sso_kpknl_palembang", "root", "");
                    
                    // If not yet flagged as assigned, check user_role table in SSO DB
                    if (!$isAssigned) {
                        $stmtRole = $pdo->prepare("
                            SELECT r.name FROM roles r 
                            JOIN user_role ur ON ur.role_id = r.id 
                            WHERE ur.user_id = :user_id AND r.name IN ('superadmin', 'maintenance')
                            LIMIT 1
                        ");
                        $stmtRole->execute(['user_id' => $ssoUserId]);
                        if ($stmtRole->fetch()) {
                            $isAssigned = true;
                            $isMaintenance = true;
                        }
                    }

                    $stmt = $pdo->prepare("
                        SELECT ua.role FROM user_application ua 
                        JOIN applications a ON ua.application_id = a.id 
                        WHERE ua.user_id = :user_id 
                        AND (a.client_id = :client_id OR a.slug LIKE '%peminjam%' OR a.slug LIKE '%lelang%')
                        LIMIT 1
                    ");
                    $stmt->execute([
                        'user_id' => $ssoUserId,
                        'client_id' => $clientId,
                    ]);
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($row) {
                        $isAssigned = true;
                        $assignedRole = $row['role'];
                    }
                } catch (Exception $dbEx) {
                    Log::warning("SSO DB check warning: " . $dbEx->getMessage());
                    $isAssigned = true;
                }
            }

            // 3. Map application role per KPKNL business rules:
            // - Pegawai / User Biasa = peminjam (Peminjam Berkas)
            // - Admin (Pejabat Lelang) = pelelang (Pejabat Lelang)
            // - Superadmin & Maintenance = admin (Administrator Arsip)
            $roles = $userPayload['roles'] ?? [];
            $primaryRole = $userPayload['primary_role'] ?? ($roles[0] ?? 'user');
            $isSuperadmin = !empty($userPayload['is_superadmin']) || in_array('superadmin', $roles);
            $isMaintenance = !empty($userPayload['is_maintenance']) || in_array('maintenance', $roles) || ($userPayload['username'] ?? '') === 'maintenance';
            $isAdmin = !empty($userPayload['is_admin']) || in_array('admin', $roles) || $primaryRole === 'admin';

            if ($isSuperadmin || $isMaintenance) {
                $role = 'admin'; // Administrator Arsip
            } elseif ($isAdmin || ($assignedRole ?? null) === 'pelelang' || ($assignedRole ?? null) === 'pejabat_lelang' || ($userPayload['app_role'] ?? null) === 'pelelang') {
                $role = 'pelelang'; // Pejabat Lelang
            } else {
                $role = 'peminjam'; // Peminjam Berkas (Pegawai / User Biasa)
            }

            // 4. Update or create local user strictly synced with SSO profile
            $user = null;
            if ($ssoUserId) {
                $user = User::where('sso_id', $ssoUserId)->first();
            }
            if (!$user && !empty($email)) {
                $user = User::where('email', $email)->first();
            }

            if ($user) {
                $user->update([
                    'name' => $name,
                    'username' => $userPayload['username'] ?? $user->username,
                    'email' => $email,
                    'role' => $role,
                    'sso_id' => $ssoUserId,
                    'avatar_url' => $userPayload['avatar_url'] ?? $user->avatar_url,
                ]);
            } else {
                $user = User::create([
                    'name' => $name,
                    'username' => $userPayload['username'] ?? Str::slug($name),
                    'email' => $email,
                    'role' => $role,
                    'sso_id' => $ssoUserId,
                    'avatar_url' => $userPayload['avatar_url'] ?? null,
                    'password' => bcrypt(Str::random(32)),
                ]);
            }

            Auth::login($user, true);

            return redirect()->route('dashboard')->with('success', "Selamat datang, {$user->name}! Berhasil masuk via SSO sebagai [{$user->getRoleLabel()}].");

        } catch (Exception $e) {
            Log::error('SSO Exception: ' . $e->getMessage());
            return redirect()->route('sso.redirect')->with('error', 'Koneksi SSO terputus: ' . $e->getMessage());
        }
    }

    /**
     * Switch role tester for fast demonstration & evaluation
     */
    public function switchRole(Request $request, string $role)
    {
        $validRoles = ['admin', 'pelelang', 'peminjam'];
        if (!in_array($role, $validRoles)) {
            $role = 'peminjam';
        }

        $names = [
            'admin' => 'Mardanus (Admin Arsip)',
            'pelelang' => 'Budi Santoso (Pejabat Lelang)',
            'peminjam' => 'Dewi Sartika (Peminjam Berkas)',
        ];

        $emails = [
            'admin' => 'mardanus@kpknl.go.id',
            'pelelang' => 'budi@kpknl.go.id',
            'peminjam' => 'dewi@kpknl.go.id',
        ];

        $usernames = [
            'admin' => 'mardanus',
            'pelelang' => 'budi.santoso',
            'peminjam' => 'dewi.sartika',
        ];

        $user = User::updateOrCreate(
            ['email' => $emails[$role]],
            [
                'name' => $names[$role],
                'username' => $usernames[$role],
                'role' => $role,
                'password' => bcrypt('password123'),
            ]
        );

        Auth::login($user, true);

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => "Role aktif beralih ke: [{$user->getRoleLabel()}].",
                'user' => $user
            ]);
        }

        return redirect()->route('dashboard')->with('success', "Beralih peran aktif: Masuk sebagai {$user->name} [{$user->getRoleLabel()}].");
    }

    /**
     * Switch role tester via POST
     */
    public function switchRolePost(Request $request)
    {
        $role = $request->input('role', 'peminjam');
        return $this->switchRole($request, $role);
    }

    /**
     * Logout and return to SSO KPKNL Palembang Dashboard
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

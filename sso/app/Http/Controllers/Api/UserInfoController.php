<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OAuthService;
use Illuminate\Http\Request;

class UserInfoController extends Controller
{
    public function user(Request $request)
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json([
                'error' => 'unauthorized',
                'error_description' => 'Header Authorization Bearer token diperlukan.',
            ], 401);
        }

        $token = substr($authHeader, 7);
        $user = OAuthService::validateAccessToken($token);

        if (!$user) {
            return response()->json([
                'error' => 'invalid_token',
                'error_description' => 'Access token tidak valid atau telah kedaluwarsa.',
            ], 401);
        }

        $roles = $user->roles->pluck('name')->toArray();
        $primaryRole = $roles[0] ?? 'user';

        // Retrieve application-specific role (e.g. admin, peminjam, pelelang for peminjaman-lelang)
        $tokenRecord = \App\Models\OAuthToken::where('access_token', $token)->first();
        $appRole = null;
        if ($tokenRecord && $tokenRecord->application_id) {
            $userApp = \Illuminate\Support\Facades\DB::table('user_application')
                ->where('user_id', $user->id)
                ->where('application_id', $tokenRecord->application_id)
                ->first();
            if ($userApp && !empty($userApp->role)) {
                $appRole = $userApp->role;
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'status' => $user->status,
                'roles' => $roles,
                'primary_role' => $primaryRole,
                'app_role' => $appRole ?? ($user->isSuperadmin() || $user->isMaintenance() ? 'admin' : ($user->isAdmin() ? 'pelelang' : 'peminjam')),
                'is_superadmin' => $user->isSuperadmin(),
                'is_admin' => $user->isAdmin(),
                'is_maintenance' => $user->isMaintenance(),
                'avatar_url' => asset($user->avatar ?: 'images/default-avatar.png'),
            ],
        ]);
    }
}

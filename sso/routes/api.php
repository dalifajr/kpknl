<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserInfoController;
use App\Models\SsoNotification;
use App\Models\User;
use Illuminate\Http\Request;

Route::get('/user', [UserInfoController::class, 'user']);
Route::match(['GET', 'POST'], '/sso/verify-session', [UserInfoController::class, 'verifySession']);

Route::post('/hub/notifications', function (Request $request) {
    // 1. Authenticate caller (via client_secret or active OAuth Bearer token)
    $authenticatedApp = null;
    $clientId = $request->header('X-Client-Id') ?: $request->input('client_id');
    $clientSecret = $request->header('X-Client-Secret') ?: $request->input('client_secret');

    if ($clientId && $clientSecret) {
        $authenticatedApp = \App\Models\Application::where('client_id', $clientId)
            ->where('client_secret', $clientSecret)
            ->where('status', 'active')
            ->first();
    }

    if (!$authenticatedApp) {
        $authHeader = $request->header('Authorization');
        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            $token = substr($authHeader, 7);
            $tokenRecord = \App\Models\OAuthToken::where('access_token', $token)
                ->where('revoked', false)
                ->where('expires_at', '>', now())
                ->first();
            if ($tokenRecord && $tokenRecord->application) {
                $authenticatedApp = $tokenRecord->application;
            }
        }
    }

    if (!$authenticatedApp) {
        return response()->json([
            'error' => 'unauthorized',
            'message' => 'Autentikasi aplikasi diperlukan (sertakan X-Client-Id & X-Client-Secret atau valid Bearer token).',
        ], 401);
    }

    if (!$request->sso_id && !$request->email && !$request->user_id) {
        return response()->json(['error' => 'invalid_payload', 'message' => 'Parameter user penerima tidak ditemukan.'], 400);
    }

    $user = User::where('id', $request->user_id)
        ->orWhere('id', $request->sso_id)
        ->orWhere('email', $request->email)
        ->first();

    if (!$user) {
        return response()->json(['error' => 'user_not_found', 'message' => 'User penerima tidak ditemukan di database SSO.'], 404);
    }

    $title = strip_tags($request->title ?? "Notifikasi dari {$authenticatedApp->name}");
    $message = strip_tags($request->description ?? $request->message ?? 'Ada pembaruan data penting.');
    $link = $request->link;
    if ($link && (!preg_match('/^(https?:\/\/|\/)/i', $link) || preg_match('/javascript:/i', $link))) {
        $link = null;
    }

    SsoNotification::create([
        'user_id' => $user->id,
        'title' => $title,
        'message' => $message,
        'type' => in_array($request->type, ['login', 'app', 'task', 'info']) ? $request->type : 'app',
        'icon' => $request->icon ?? 'fa-solid fa-bell text-primary',
        'link' => $link,
        'app_name' => $authenticatedApp->name,
    ]);

    return response()->json(['status' => 'success']);
});

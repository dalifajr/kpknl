<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserInfoController;
use App\Models\SsoNotification;
use App\Models\User;
use Illuminate\Http\Request;

Route::get('/user', [UserInfoController::class, 'user']);

Route::post('/hub/notifications', function (Request $request) {
    if (!$request->sso_id && !$request->email && !$request->user_id) {
        return response()->json(['error' => 'invalid_payload'], 400);
    }

    $user = User::where('id', $request->user_id)
        ->orWhere('id', $request->sso_id)
        ->orWhere('email', $request->email)
        ->first();

    if (!$user) {
        return response()->json(['error' => 'user_not_found'], 404);
    }

    SsoNotification::create([
        'user_id' => $user->id,
        'title' => $request->title ?? 'Notifikasi Aplikasi Terintegrasi',
        'message' => $request->description ?? $request->message ?? 'Ada pembaruan dari aplikasi terintegrasi.',
        'type' => $request->type ?? 'app',
        'icon' => $request->icon ?? 'fa-solid fa-bell text-primary',
        'link' => $request->link,
        'app_name' => $request->app_name ?? 'Monitoring & Pelaporan',
    ]);

    return response()->json(['status' => 'success']);
});

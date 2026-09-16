<?php

namespace App\Http\Controllers\OAuth;

use App\Http\Controllers\Controller;
use App\Services\OAuthService;
use Illuminate\Http\Request;

class TokenController extends Controller
{
    public function token(Request $request)
    {
        $code = $request->input('code');
        $clientId = $request->input('client_id');
        $clientSecret = $request->input('client_secret');

        if (!$code || !$clientId || !$clientSecret) {
            return response()->json([
                'error' => 'invalid_request',
                'error_description' => 'Parameter code, client_id, dan client_secret wajib diisi.',
            ], 400);
        }

        $tokens = OAuthService::exchangeCodeForToken($code, $clientId, $clientSecret);

        if (!$tokens) {
            return response()->json([
                'error' => 'invalid_grant',
                'error_description' => 'Authorization code tidak valid, kedaluwarsa, atau kredensial aplikasi salah.',
            ], 400);
        }

        return response()->json($tokens);
    }
}

<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\LoginSession;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LoginSessionController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $sessions = $user->loginSessions()->latest()->paginate(10);
        $currentSessionId = session()->getId();

        return view('user.login-sessions.index', compact('sessions', 'currentSessionId'));
    }

    public function destroy(LoginSession $session)
    {
        if ($session->user_id !== auth()->id() && !auth()->user()->isSuperadmin()) {
            abort(403, 'Akses ditolak.');
        }

        // 1. Mark session in login_sessions table as inactive
        $session->update([
            'is_active' => false,
            'logout_at' => now(),
        ]);

        // 2. Delete actual Laravel web session payload from sessions table if exists
        if (Schema::hasTable('sessions')) {
            DB::table('sessions')->where('id', $session->session_id)->delete();
        }

        ActivityLogService::log(
            'session_terminated',
            "Sesi login pada IP {$session->ip_address} ({$session->browser} - {$session->platform}) dihentikan.",
            auth()->id()
        );

        return back()->with('success', 'Sesi login berhasil diakhiri.');
    }
}


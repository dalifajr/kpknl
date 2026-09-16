<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Application;
use App\Models\LoginSession;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->isSuperadmin() || $user->isMaintenance()) {
            $totalUsers = User::count();
            $totalApps = Application::count();
            $activeSessions = LoginSession::where('is_active', true)->count();
            $recentLogs = ActivityLog::with(['user', 'application'])->latest()->take(10)->get();
            $applications = Application::where('status', 'active')->get();

            return view('dashboard.superadmin', compact('totalUsers', 'totalApps', 'activeSessions', 'recentLogs', 'applications'));
        }

        if ($user->isAdmin()) {
            $assignedApps = $user->applications()->where('status', 'active')->get();
            $assignedAppIds = $assignedApps->pluck('id')->toArray();

            // Users assigned to these apps
            $appUsersCount = User::whereHas('applications', function ($q) use ($assignedAppIds) {
                $q->whereIn('applications.id', $assignedAppIds);
            })->count();

            // Recent activity logs of users on these apps
            $recentLogs = ActivityLog::with(['user', 'application'])
                ->whereIn('application_id', $assignedAppIds)
                ->latest()
                ->take(10)
                ->get();

            return view('dashboard.admin', compact('assignedApps', 'appUsersCount', 'recentLogs'));
        }

        // Standard User
        $assignedApps = $user->applications()->where('status', 'active')->get();
        $recentSessions = $user->loginSessions()->latest()->take(5)->get();

        return view('dashboard.user', compact('assignedApps', 'recentSessions'));
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Application;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = auth()->user();
        $query = ActivityLog::with(['user', 'application']);

        if ($currentUser->isSuperadmin()) {
            // Superadmin can filter by any user or application
            if ($userId = $request->input('user_id')) {
                $query->where('user_id', $userId);
            }
            if ($appId = $request->input('application_id')) {
                $query->where('application_id', $appId);
            }
            $availableApps = Application::all();
            $availableUsers = User::all();
        } elseif ($currentUser->isAdmin()) {
            // Admin can see logs of users on applications assigned to this admin
            $assignedAppIds = $currentUser->applications->pluck('id')->toArray();

            $query->where(function ($q) use ($currentUser, $assignedAppIds) {
                // Own activity OR activity on assigned apps
                $q->where('user_id', $currentUser->id)
                  ->orWhereIn('application_id', $assignedAppIds);
            });

            if ($appId = $request->input('application_id')) {
                if (in_array($appId, $assignedAppIds)) {
                    $query->where('application_id', $appId);
                }
            }

            $availableApps = $currentUser->applications;
            $availableUsers = User::whereHas('applications', function ($q) use ($assignedAppIds) {
                $q->whereIn('applications.id', $assignedAppIds);
            })->get();
        } else {
            // Standard User sees own logs
            $query->where('user_id', $currentUser->id);
            $availableApps = $currentUser->applications;
            $availableUsers = collect([$currentUser]);
        }

        if ($action = $request->input('action')) {
            $query->where('action', 'like', "%{$action}%");
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $logs = $query->latest()->paginate(15)->withQueryString();

        return view('admin.logs.index', compact('logs', 'availableApps', 'availableUsers'));
    }
}

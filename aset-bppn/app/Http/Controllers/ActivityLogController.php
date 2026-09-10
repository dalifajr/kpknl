<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use App\Models\User;
use App\Models\Asset;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        // Restrict to superadmin and admin roles
        if (!in_array(auth()->user()->role, ['superadmin', 'admin'])) {
            abort(403, 'Akses tidak diizinkan. Hanya Admin dan Superadmin yang dapat mengakses Log Aktifitas.');
        }

        $query = Activity::with(['causer'])->latest();

        // Search Keyword (Description, Subject ID, Properties, Causer Name)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('properties', 'like', "%{$search}%")
                  ->orWhere('subject_id', 'like', "%{$search}%")
                  ->orWhereHasMorph('causer', [User::class], function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by Event (created, updated, deleted)
        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        // Filter by Causer (User ID)
        if ($request->filled('user_id')) {
            $query->where('causer_type', User::class)
                  ->where('causer_id', $request->user_id);
        }

        // Filter by Date Range
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $activities = $query->paginate(15)->withQueryString();

        // Pre-fetch related assets including soft-deleted ones for fast lookup
        $assetIds = $activities->pluck('subject_id')->unique()->filter()->values();
        $assetsLookup = Asset::withTrashed()->whereIn('id', $assetIds)->get()->keyBy('id');

        $users = User::orderBy('name')->get();

        return view('activity_logs.index', compact('activities', 'assetsLookup', 'users'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\SsoNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        
        $query = SsoNotification::where(function ($q) use ($userId) {
            $q->where('user_id', $userId)
              ->orWhereNull('user_id');
        });

        // Filter by Date Range
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Keyword Search (Title, Message, App Name)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%")
                  ->orWhere('app_name', 'like', "%{$search}%");
            });
        }

        // Filter by Type (login, app, task, info)
        if ($request->filled('action_type')) {
            $query->where('type', $request->action_type);
        }

        $notifications = $query->latest()->paginate(15)->withQueryString();

        // Mark all notifications for this user as read when opening notification center
        SsoNotification::where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('notifications.index', compact('notifications'));
    }

    public function readAndRedirect($id)
    {
        $userId = Auth::id();
        $notif = SsoNotification::where('id', $id)
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhereNull('user_id');
            })->first();

        if ($notif) {
            $notif->update(['read_at' => now()]);
            if ($notif->link) {
                return redirect($notif->link);
            }
        }

        return redirect()->route('notifications.index');
    }

    public function markAllRead()
    {
        $userId = Auth::id();
        SsoNotification::where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back()->with('success', 'Seluruh notifikasi personal berhasil ditandai sebagai telah dibaca.');
    }
}

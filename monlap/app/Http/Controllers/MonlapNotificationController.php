<?php

namespace App\Http\Controllers;

use App\Models\MonlapNotification;
use Illuminate\Http\Request;

class MonlapNotificationController extends Controller
{
    public function index()
    {
        $notifications = MonlapNotification::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(15);
            
        return view('notifications.index', compact('notifications'));
    }

    public function read(MonlapNotification $notification)
    {
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }

        $notification->update(['is_read' => true]);

        if ($notification->link) {
            return redirect($notification->link);
        }

        return redirect()->back();
    }

    public function markAllRead()
    {
        MonlapNotification::where('user_id', auth()->id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return redirect()->back()->with('success', 'Semua notifikasi telah ditandai sudah dibaca.');
    }
}

<?php

namespace App\Services;

use App\Models\MonlapNotification;
use App\Models\User;
use Illuminate\Support\Facades\Http;

class NotificationService
{
    /**
     * Send notification to both Monlap internal hub and SSO Notification Hub.
     */
    public function send(User $user, $title, $message, $link = null, $type = 'app')
    {
        // 1. Save to Monlap internal Notification Hub
        MonlapNotification::create([
            'user_id' => $user->id,
            'title' => $title,
            'message' => $message,
            'link' => $link,
            'is_read' => false,
        ]);

        // 2. Send to SSO Notification Hub
        $ssoUrl = config('services.sso.url');
        if ($ssoUrl && $user->sso_id) {
            try {
                Http::post($ssoUrl . '/api/hub/notifications', [
                    'sso_id' => $user->sso_id,
                    'title' => $title,
                    'message' => $message,
                    'link' => $link,
                    'type' => $type,
                    'app_name' => 'Monitoring & Pelaporan'
                ]);
            } catch (\Exception $e) {
                // Log silently if SSO is down
                \Log::error('Failed sending notification to SSO: ' . $e->getMessage());
            }
        }
    }
}

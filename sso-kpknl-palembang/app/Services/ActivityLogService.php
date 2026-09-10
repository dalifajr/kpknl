<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogService
{
    public static function log(
        string $action,
        string $description,
        ?int $userId = null,
        ?int $applicationId = null,
        ?array $properties = null,
        ?Request $request = null
    ): ActivityLog {
        $req = $request ?: request();
        $user = auth()->user();

        return ActivityLog::create([
            'user_id' => $userId ?: ($user ? $user->id : null),
            'application_id' => $applicationId,
            'action' => $action,
            'description' => $description,
            'ip_address' => $req ? $req->ip() : null,
            'user_agent' => $req ? substr($req->header('User-Agent', ''), 0, 500) : null,
            'properties' => $properties,
        ]);
    }
}

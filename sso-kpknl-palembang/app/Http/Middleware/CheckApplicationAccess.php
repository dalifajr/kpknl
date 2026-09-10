<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Application;

class CheckApplicationAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        $appId = $request->route('application') ?: $request->input('application_id');

        if ($appId) {
            $application = $appId instanceof Application ? $appId : Application::find($appId);

            if ($application && !$user->hasApplicationAccess($application)) {
                return redirect()->route('dashboard')->with('error', 'Aplikasi tersebut tidak di-assign ke akun Anda.');
            }
        }

        return $next($request);
    }
}

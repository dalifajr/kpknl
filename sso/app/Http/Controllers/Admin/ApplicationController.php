<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ApplicationController extends Controller
{
    public function index(Request $request)
    {
        $query = Application::withCount('users');

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $stats = [
            'total' => Application::count(),
            'active' => Application::where('status', 'active')->count(),
            'inactive' => Application::where('status', 'inactive')->count(),
            'total_users' => User::count(),
        ];

        $applications = $query->latest()->paginate(9)->withQueryString();

        return view('admin.applications.index', compact('applications', 'stats'));
    }

    public function create()
    {
        if (!auth()->user()->isMaintenance()) {
            abort(403, 'Hanya tim Maintenance yang berwenang menambah aplikasi baru.');
        }

        return view('admin.applications.create');
    }

    public function store(Request $request)
    {
        if (!auth()->user()->isMaintenance()) {
            abort(403, 'Hanya tim Maintenance yang berwenang menambah aplikasi baru.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:100|unique:applications,slug|alpha_dash',
            'description' => 'nullable|string',
            'url' => 'required|url|max:255',
            'redirect_uri' => 'required|url|max:255',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'status' => 'required|in:active,inactive',
        ]);

        $iconPath = 'box';
        if ($request->hasFile('icon')) {
            $file = $request->file('icon');
            $path = $file->getRealPath() ?: $file->getPathname();
            $filename = 'applications/' . $file->hashName();
            \Illuminate\Support\Facades\Storage::disk('public')->put($filename, file_get_contents($path));
            $iconPath = $filename;
        }

        $app = Application::create([
            'name' => $request->name,
            'slug' => strtolower($request->slug),
            'description' => $request->description,
            'url' => $request->url,
            'redirect_uri' => $request->redirect_uri,
            'icon' => $iconPath,
            'client_id' => 'client_' . Str::random(16),
            'client_secret' => Str::random(32),
            'status' => $request->status,
        ]);

        ActivityLogService::log(
            'application_created',
            "Superadmin mendaftarkan aplikasi baru: '{$app->name}'",
            null,
            $app->id
        );

        return redirect()->route('admin.applications.index')->with('success', "Aplikasi '{$app->name}' berhasil didaftarkan.");
    }

    public function show(Application $application)
    {
        $application->load(['users.roles']);
        $assignedUsers = $application->users;
        $unassignedUsers = User::whereNotIn('id', $assignedUsers->pluck('id'))->where('status', 'active')->get();

        return view('admin.applications.show', compact('application', 'assignedUsers', 'unassignedUsers'));
    }

    public function edit(Application $application)
    {
        return view('admin.applications.edit', compact('application'));
    }

    public function update(Request $request, Application $application)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => ['required', 'string', 'max:100', 'alpha_dash', Rule::unique('applications')->ignore($application->id)],
            'description' => 'nullable|string',
            'url' => 'required|url|max:255',
            'redirect_uri' => 'required|url|max:255',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'status' => 'required|in:active,inactive',
        ]);

        $iconPath = $application->icon;
        if ($request->hasFile('icon')) {
            if ($application->icon && !in_array($application->icon, ['box', 'desktop', 'window-maximize']) && \Illuminate\Support\Facades\Storage::disk('public')->exists($application->icon)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($application->icon);
            }
            $file = $request->file('icon');
            $path = $file->getRealPath() ?: $file->getPathname();
            $filename = 'applications/' . $file->hashName();
            \Illuminate\Support\Facades\Storage::disk('public')->put($filename, file_get_contents($path));
            $iconPath = $filename;
        }

        $application->update([
            'name' => $request->name,
            'slug' => strtolower($request->slug),
            'description' => $request->description,
            'url' => $request->url,
            'redirect_uri' => $request->redirect_uri,
            'icon' => $iconPath ?: 'box',
            'status' => $request->status,
        ]);

        ActivityLogService::log(
            'application_updated',
            "Superadmin mengupdate aplikasi: '{$application->name}'",
            null,
            $application->id
        );

        return redirect()->route('admin.applications.index')->with('success', "Aplikasi '{$application->name}' berhasil diperbarui.");
    }

    public function regenerateSecret(Application $application)
    {
        $newSecret = Str::random(32);
        $application->update(['client_secret' => $newSecret]);

        ActivityLogService::log(
            'application_secret_regenerated',
            "Superadmin mereset client secret untuk aplikasi: '{$application->name}'",
            null,
            $application->id
        );

        return back()->with('success', "Client secret untuk aplikasi '{$application->name}' telah diperbarui.");
    }

    public function assignUsers(Request $request, Application $application)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'role' => 'nullable|string',
        ]);

        $role = $request->input('role', 'peminjam');
        if (str_contains($application->slug, 'lelang') || str_contains($application->slug, 'peminjam')) {
            if (!in_array($role, ['admin', 'peminjam', 'pelelang'])) {
                $role = 'peminjam';
            }
        }

        $syncData = [];
        foreach ($request->user_ids as $userId) {
            $syncData[$userId] = [
                'assigned_by' => auth()->id(),
                'role' => $role,
            ];
        }

        $application->users()->syncWithoutDetaching($syncData);

        ActivityLogService::log(
            'application_users_assigned',
            "Superadmin mengassign " . count($request->user_ids) . " user ke aplikasi '{$application->name}' dengan role [{$role}]",
            null,
            $application->id
        );

        return back()->with('success', 'User berhasil di-assign ke aplikasi dengan role [' . $role . '].');
    }

    public function updateUserRole(Request $request, Application $application, User $user)
    {
        $role = $request->input('role');
        if (str_contains($application->slug, 'lelang') || str_contains($application->slug, 'peminjam')) {
            $request->validate([
                'role' => 'required|in:admin,peminjam,pelelang',
            ]);
        }

        $application->users()->updateExistingPivot($user->id, [
            'role' => $role,
        ]);

        ActivityLogService::log(
            'application_user_role_updated',
            "Superadmin mengubah role user '{$user->name}' pada aplikasi '{$application->name}' menjadi '{$role}'",
            $user->id,
            $application->id
        );

        return back()->with('success', "Peran user '{$user->name}' pada aplikasi berhasil diperbarui menjadi [{$role}].");
    }

    public function revokeUser(Application $application, User $user)
    {
        $application->users()->detach($user->id);

        ActivityLogService::log(
            'application_user_revoked',
            "Akses user '{$user->name}' ke aplikasi '{$application->name}' telah dicabut",
            $user->id,
            $application->id
        );

        return back()->with('success', "Akses user '{$user->name}' telah dicabut dari aplikasi.");
    }

    public function destroy(Application $application)
    {
        $name = $application->name;
        $application->delete();

        ActivityLogService::log(
            'application_deleted',
            "Superadmin menghapus aplikasi: '{$name}'"
        );

        return redirect()->route('admin.applications.index')->with('success', "Aplikasi '{$name}' berhasil dihapus.");
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Role;
use App\Models\User;
use App\Rules\StrongPassword;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['roles', 'applications']);

        // Poin 1: Sembunyikan user dengan role maintenance dari superadmin
        if (auth()->user()->isSuperadmin()) {
            $query->whereDoesntHave('roles', function ($q) {
                $q->where('name', 'maintenance');
            });
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role = $request->input('role')) {
            $query->whereHas('roles', function ($q) use ($role) {
                $q->where('name', $role);
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $users = $query->latest()->paginate(10)->withQueryString();
        $roles = auth()->user()->isSuperadmin() 
            ? Role::where('name', '!=', 'maintenance')->get() 
            : Role::all();
        $applications = Application::where('status', 'active')->get();

        return view('admin.users.index', compact('users', 'roles', 'applications'));
    }

    public function create()
    {
        $roles = auth()->user()->isSuperadmin() 
            ? Role::where('name', '!=', 'maintenance')->get() 
            : Role::all();
        $applications = Application::where('status', 'active')->get();
        return view('admin.users.create', compact('roles', 'applications'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users,username|alpha_dash',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => ['required', 'string', new StrongPassword()],
            'role_id' => 'required|exists:roles,id',
            'status' => 'required|in:active,inactive,suspended',
            'applications' => 'nullable|array',
            'applications.*' => 'exists:applications,id',
        ], [
            'username.unique' => 'Username sudah digunakan oleh akun lain.',
            'username.alpha_dash' => 'Username hanya boleh berisi huruf, angka, strip (-), dan garis bawah (_).',
            'email.unique' => 'Email sudah terdaftar di sistem.',
        ]);

        if (auth()->user()->isSuperadmin()) {
            $maintenanceRole = Role::where('name', 'maintenance')->first();
            if ($maintenanceRole && (int) $request->role_id === (int) $maintenanceRole->id) {
                return back()->withInput()->with('error', 'Superadmin tidak diizinkan membuat akun dengan role Maintenance.');
            }
        }

        $user = User::create([
            'name' => $request->name,
            'username' => strtolower($request->username),
            'email' => strtolower($request->email),
            'password' => Hash::make($request->password),
            'status' => $request->status,
            'created_by' => auth()->id(),
        ]);

        // Attach Role
        $user->roles()->attach($request->role_id);

        // Assign Applications if selected
        if ($request->has('applications')) {
            $syncData = [];
            foreach ($request->applications as $appId) {
                $syncData[$appId] = ['assigned_by' => auth()->id()];
            }
            $user->applications()->sync($syncData);
        }

        $actor = auth()->user()->isSuperadmin() ? 'Superadmin' : (auth()->user()->isMaintenance() ? 'Maintenance' : 'Admin');
        ActivityLogService::log(
            'user_created',
            "{$actor} membuat user baru: '{$user->name}' ({$user->username})",
            $user->id
        );

        return redirect()->route('admin.users.index')->with('success', "User '{$user->name}' berhasil dibuat.");
    }

    public function edit(User $user)
    {
        if (auth()->user()->isSuperadmin() && $user->isMaintenance()) {
            abort(403, 'Anda tidak memiliki izin untuk mengedit akun Maintenance.');
        }

        $roles = auth()->user()->isSuperadmin() 
            ? Role::where('name', '!=', 'maintenance')->get() 
            : Role::all();
        $applications = Application::where('status', 'active')->get();
        $userAppIds = $user->applications->pluck('id')->toArray();
        $userRoleId = $user->roles->first()?->id;

        return view('admin.users.edit', compact('user', 'roles', 'applications', 'userAppIds', 'userRoleId'));
    }

    public function update(Request $request, User $user)
    {
        if (auth()->user()->isSuperadmin() && $user->isMaintenance()) {
            abort(403, 'Anda tidak memiliki izin untuk mengubah akun Maintenance.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('users')->ignore($user->id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', new StrongPassword()],
            'role_id' => 'required|exists:roles,id',
            'status' => 'required|in:active,inactive,suspended',
            'applications' => 'nullable|array',
            'applications.*' => 'exists:applications,id',
        ]);

        if (auth()->user()->isSuperadmin()) {
            $maintenanceRole = Role::where('name', 'maintenance')->first();
            if ($maintenanceRole && (int) $request->role_id === (int) $maintenanceRole->id) {
                return back()->withInput()->with('error', 'Superadmin tidak diizinkan menetapkan role Maintenance.');
            }
        }

        if ($user->id === auth()->id()) {
            $currentRoleId = $user->roles->first()?->id;
            if ($currentRoleId && (int) $request->role_id !== (int) $currentRoleId) {
                return back()->withInput()->with('error', 'Anda tidak dapat mengubah role akun Anda sendiri.');
            }
        }

        $updateData = [
            'name' => $request->name,
            'username' => strtolower($request->username),
            'email' => strtolower($request->email),
            'status' => $request->status,
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        // Sync Role (unless self)
        if ($user->id !== auth()->id()) {
            $user->roles()->sync([$request->role_id]);
        }

        // Sync Applications
        $syncData = [];
        if ($request->has('applications')) {
            foreach ($request->applications as $appId) {
                $syncData[$appId] = ['assigned_by' => auth()->id()];
            }
        }
        $user->applications()->sync($syncData);

        $actor = auth()->user()->isSuperadmin() ? 'Superadmin' : (auth()->user()->isMaintenance() ? 'Maintenance' : 'Admin');
        ActivityLogService::log(
            'user_updated',
            "{$actor} mengupdate data user: '{$user->name}' ({$user->username})",
            $user->id
        );

        return redirect()->route('admin.users.index')->with('success', "Data user '{$user->name}' berhasil diperbarui.");
    }

    public function destroy(User $user)
    {
        if (auth()->user()->isSuperadmin() && $user->isMaintenance()) {
            abort(403, 'Anda tidak memiliki izin untuk menghapus akun Maintenance.');
        }

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $name = $user->name;
        $username = $user->username;

        $user->delete();

        $actor = auth()->user()->isSuperadmin() ? 'Superadmin' : (auth()->user()->isMaintenance() ? 'Maintenance' : 'Admin');
        ActivityLogService::log(
            'user_deleted',
            "{$actor} menghapus user: '{$name}' ({$username})"
        );

        return redirect()->route('admin.users.index')->with('success', "User '{$name}' berhasil dihapus.");
    }
}

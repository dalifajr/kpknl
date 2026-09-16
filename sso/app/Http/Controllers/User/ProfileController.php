<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Rules\StrongPassword;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show()
    {
        $user = auth()->user()->load(['roles', 'applications', 'loginSessions']);
        return view('user.profile.show', compact('user'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
        ]);

        $user->update([
            'name' => $request->name,
            'email' => strtolower($request->email),
        ]);

        ActivityLogService::log(
            'profile_updated',
            "User {$user->name} memperbarui informasi profilnya",
            $user->id
        );

        return back()->with('success', 'Informasi profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'current_password' => 'required|string',
            'password' => ['required', 'string', 'confirmed', new StrongPassword()],
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini yang Anda masukkan salah.']);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        ActivityLogService::log(
            'password_changed',
            "User {$user->name} memperbarui password akunnya",
            $user->id
        );

        return back()->with('success', 'Password akun Anda berhasil diperbarui.');
    }
}

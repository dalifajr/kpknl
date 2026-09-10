<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SettingController extends Controller
{
    public function editLoginInfo()
    {
        $badge = Setting::get('login_info_badge', 'INFO LAYANAN & KEAMANAN');
        $title = Setting::get('login_info_title', 'Selamat Datang di Portal Single Sign-On (SSO)');
        $content = Setting::get('login_info_content', 'Satu akun resmi untuk mengautentikasi dan mengakses seluruh aplikasi internal Kekayaan Negara & Lelang Palembang secara aman, efisien, dan terintegrasi.');
        $status = Setting::get('login_info_status', 'active');

        return view('admin.settings.login_info', compact('badge', 'title', 'content', 'status'));
    }

    public function updateLoginInfo(Request $request)
    {
        $request->validate([
            'login_info_badge' => 'required|string|max:100',
            'login_info_title' => 'required|string|max:255',
            'login_info_content' => 'required|string|max:1000',
            'login_info_status' => 'required|in:active,inactive',
        ]);

        Setting::set('login_info_badge', $request->login_info_badge);
        Setting::set('login_info_title', $request->login_info_title);
        Setting::set('login_info_content', $request->login_info_content);
        Setting::set('login_info_status', $request->login_info_status);

        ActivityLogService::log(
            'setting_updated',
            "Superadmin mengupdate Card Informasi pada Halaman Login SSO"
        );

        return back()->with('success', 'Card Informasi Halaman Login berhasil diperbarui.');
    }

    public function editMaintenance()
    {
        $status = Setting::get('maintenance_mode', 'inactive');
        $message = Setting::get('maintenance_message', 'Sistem Single Sign-On (SSO) KPKNL Palembang sedang dalam pemeliharaan berkala.');

        return view('admin.settings.maintenance', compact('status', 'message'));
    }

    public function updateMaintenance(Request $request)
    {
        $request->validate([
            'maintenance_mode' => 'required|in:active,inactive',
            'maintenance_message' => 'required|string|max:1000',
            'sudo_password' => 'required|string',
        ], [
            'sudo_password.required' => 'Password verifikasi Superadmin wajib diisi.',
        ]);

        if (!Hash::check($request->sudo_password, auth()->user()->password)) {
            return back()->withInput()->with('error', 'Verifikasi password Superadmin gagal. Password yang Anda masukkan salah.');
        }

        Setting::set('maintenance_mode', $request->maintenance_mode);
        Setting::set('maintenance_message', $request->maintenance_message);

        $statusText = $request->maintenance_mode === 'active' ? 'AKTIF (Sistem dalam pemeliharaan)' : 'NON-AKTIF (Sistem berjalan normal)';

        ActivityLogService::log(
            'maintenance_toggled',
            "Superadmin mengupdate status Modus Pemeliharaan (Maintenance) menjadi: {$statusText}"
        );

        return back()->with('success', "Modus Pemeliharaan (Maintenance) berhasil diperbarui: {$statusText}.");
    }
}




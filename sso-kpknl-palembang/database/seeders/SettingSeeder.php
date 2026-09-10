<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        Setting::set('login_info_badge', 'INFO LAYANAN & KEAMANAN');
        Setting::set('login_info_title', 'Selamat Datang di Portal Single Sign-On (SSO)');
        Setting::set('login_info_content', 'Satu akun resmi untuk mengautentikasi dan mengakses seluruh aplikasi internal Kekayaan Negara & Lelang Palembang secara aman, efisien, dan terintegrasi.');
        Setting::set('login_info_status', 'active');
    }
}

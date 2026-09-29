<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Application;
use Illuminate\Support\Facades\Hash;

class UserPersonaSeeder extends Seeder
{
    /**
     * Run the database seeds for specialized user personas and application assignments.
     */
    public function run(): void
    {
        // 1. Ambil Referensi Roles
        $superadminRole = Role::firstOrCreate(['name' => 'superadmin'], ['display_name' => 'Super Administrator']);
        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Administrator']);
        $userRole = Role::firstOrCreate(['name' => 'user'], ['display_name' => 'Pengguna']);

        // 2. Ambil Referensi Applications
        $appPeminjaman = Application::where('slug', 'peminjaman-lelang')->first();
        $appSikep = Application::where('slug', 'simpatik')->first();
        $appMonlap = Application::where('slug', 'monitoring')->first();
        $allApps = Application::all();

        // Admin creator ID fallback (mardanus / superadmin)
        $superadminUser = User::where('username', 'mardanus')->orWhere('username', 'superadmin')->first();
        $assignedById = $superadminUser ? $superadminUser->id : 1;

        $defaultPassword = Hash::make('Password123!@#');

        // Helper upsert with soft-delete safety
        $upsertUser = function (string $username, array $attributes) {
            $user = User::withTrashed()->where('username', $username)->first();
            if ($user) {
                if ($user->trashed()) {
                    $user->restore();
                }
                $user->update($attributes);
            } else {
                $user = User::create(array_merge(['username' => $username], $attributes));
            }
            return $user;
        };

        // ==========================================
        // PERSONA 1: User Peminjaman (Petugas Peminjaman Arsip)
        // Akses: peminjaman (role: admin), si-kep (role: user)
        // ==========================================
        $userPeminjaman = $upsertUser('petugas_peminjaman', [
            'name' => 'Petugas Peminjaman Risalah Lelang',
            'email' => 'petugas.peminjaman@kpknl.go.id',
            'password' => $defaultPassword,
            'status' => 'active',
            'created_by' => $assignedById,
        ]);
        $userPeminjaman->roles()->sync([$userRole->id]);

        $peminjamanAppSync = [];
        if ($appPeminjaman) {
            $peminjamanAppSync[$appPeminjaman->id] = ['role' => 'admin', 'assigned_by' => $assignedById];
        }
        if ($appSikep) {
            $peminjamanAppSync[$appSikep->id] = ['role' => 'user', 'assigned_by' => $assignedById];
        }
        $userPeminjaman->applications()->sync($peminjamanAppSync);

        // ==========================================
        // PERSONA 2: User Sekretaris (Sekretaris Pimpinan)
        // Akses: peminjaman (role: peminjam), si-kep (role: user), monlap (role: admin)
        // ==========================================
        $userSekretaris = $upsertUser('sekretaris', [
            'name' => 'Sekretaris Pimpinan KPKNL',
            'email' => 'sekretaris@kpknl.go.id',
            'password' => $defaultPassword,
            'status' => 'active',
            'created_by' => $assignedById,
        ]);
        $userSekretaris->roles()->sync([$adminRole->id]);

        $sekretarisAppSync = [];
        if ($appPeminjaman) {
            $sekretarisAppSync[$appPeminjaman->id] = ['role' => 'peminjam', 'assigned_by' => $assignedById];
        }
        if ($appSikep) {
            $sekretarisAppSync[$appSikep->id] = ['role' => 'user', 'assigned_by' => $assignedById];
        }
        if ($appMonlap) {
            $sekretarisAppSync[$appMonlap->id] = ['role' => 'admin', 'assigned_by' => $assignedById];
        }
        $userSekretaris->applications()->sync($sekretarisAppSync);

        // ==========================================
        // PERSONA 3: User PIC Eselon IV (Koordinator & Tiap Seksi/Subbag)
        // Akses: peminjaman (role: peminjam), si-kep (role: user), monlap (role: user/pic)
        // ==========================================
        $picSections = [
            [
                'username' => 'pic_eselon4',
                'name' => 'Koordinator PIC Eselon IV',
                'email' => 'pic.eselon4@kpknl.go.id',
            ],
            [
                'username' => 'pic_subbag_umum',
                'name' => 'PIC Subbagian Umum',
                'email' => 'pic.umum@kpknl.go.id',
            ],
            [
                'username' => 'pic_seksi_pkn',
                'name' => 'PIC Seksi Pengelolaan Kekayaan Negara',
                'email' => 'pic.pkn@kpknl.go.id',
            ],
            [
                'username' => 'pic_seksi_lelang',
                'name' => 'PIC Seksi Pelayanan Lelang',
                'email' => 'pic.lelang@kpknl.go.id',
            ],
            [
                'username' => 'pic_seksi_pn',
                'name' => 'PIC Seksi Piutang Negara',
                'email' => 'pic.pn@kpknl.go.id',
            ],
            [
                'username' => 'pic_seksi_hi',
                'name' => 'PIC Seksi Hukum dan Informasi',
                'email' => 'pic.hi@kpknl.go.id',
            ],
            [
                'username' => 'pic_seksi_ki',
                'name' => 'PIC Seksi Kepatuhan Internal',
                'email' => 'pic.ki@kpknl.go.id',
            ],
        ];

        foreach ($picSections as $sec) {
            $picUser = $upsertUser($sec['username'], [
                'name' => $sec['name'],
                'email' => $sec['email'],
                'password' => $defaultPassword,
                'status' => 'active',
                'created_by' => $assignedById,
            ]);
            $picUser->roles()->sync([$userRole->id]);

            $picAppSync = [];
            if ($appPeminjaman) {
                $picAppSync[$appPeminjaman->id] = ['role' => 'peminjam', 'assigned_by' => $assignedById];
            }
            if ($appSikep) {
                $picAppSync[$appSikep->id] = ['role' => 'user', 'assigned_by' => $assignedById];
            }
            if ($appMonlap) {
                $picAppSync[$appMonlap->id] = ['role' => 'user', 'assigned_by' => $assignedById];
            }
            $picUser->applications()->sync($picAppSync);
        }

        // ==========================================
        // PERSONA 4: User Kepala Kantor
        // Akses: Semua aplikasi dengan role superadmin
        // ==========================================
        $userKepalaKantor = $upsertUser('kepala_kantor', [
            'name' => 'Kepala KPKNL Palembang',
            'email' => 'kepala.kantor@kpknl.go.id',
            'password' => $defaultPassword,
            'status' => 'active',
            'created_by' => $assignedById,
        ]);
        $userKepalaKantor->roles()->sync([$superadminRole->id]);

        $superadminAppSync = [];
        foreach ($allApps as $app) {
            $superadminAppSync[$app->id] = ['role' => 'superadmin', 'assigned_by' => $assignedById];
        }
        $userKepalaKantor->applications()->sync($superadminAppSync);

        $this->command->info('UserPersonaSeeder berhasil dijalankan untuk 4 personas: Petugas Peminjaman, Sekretaris, PIC Eselon IV, dan Kepala Kantor.');
    }
}

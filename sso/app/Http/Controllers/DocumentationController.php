<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DocumentationController extends Controller
{
    /**
     * Display the documentation page.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $primaryRole = $this->resolvePrimaryRole($user);

        // Hanya tampilkan panduan khusus untuk role yang sedang login
        $activeRole = $primaryRole;
        $applications = Application::where('status', 'active')->orderBy('name')->get();
        $docSections = $this->getDocumentationData();

        return view('documentation.index', compact(
            'user',
            'primaryRole',
            'activeRole',
            'applications',
            'docSections'
        ));
    }

    /**
     * Generate and download the documentation as a PDF file.
     */
    public function downloadPdf(Request $request)
    {
        $user = auth()->user();
        $primaryRole = $this->resolvePrimaryRole($user);

        // Unduhan PDF khusus dan terikat pada role yang sedang login
        $activeRole = $primaryRole;
        $applications = Application::where('status', 'active')->orderBy('name')->get();
        $docSections = $this->getDocumentationData();

        // Render HTML for PDF
        $html = view('documentation.pdf', compact(
            'user',
            'primaryRole',
            'activeRole',
            'applications',
            'docSections'
        ))->render();

        // Setup Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Helvetica');
        $options->set('dpi', 120);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $roleSlug = match ($activeRole) {
            'user' => 'Pegawai_User',
            'admin' => 'Administrator',
            'maintenance' => 'Tim_Maintenance',
            'superadmin' => 'Superadmin',
            default => 'Lengkap_Semua_Role'
        };

        $filename = "Panduan_SSO_KPKNL_Palembang_{$roleSlug}_" . date('Ymd_His') . ".pdf";

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * Determine the user's primary role.
     */
    private function resolvePrimaryRole($user): string
    {
        if ($user->isSuperadmin()) {
            return 'superadmin';
        }
        if ($user->isMaintenance()) {
            return 'maintenance';
        }
        if ($user->isAdmin()) {
            return 'admin';
        }
        return 'user';
    }

    /**
     * Structured documentation data detailing all SSO features.
     */
    private function getDocumentationData(): array
    {
        return [
            'overview' => [
                'title' => 'Arsitektur & Konsep Ekosistem SSO',
                'description' => 'Gambaran umum sistem Single Sign-On (SSO) KPKNL Palembang yang mengintegrasikan seluruh aplikasi perkantoran dalam satu pintu otentikasi terpercaya.',
                'features' => [
                    [
                        'name' => 'Portal Terpadu & App Launcher',
                        'icon' => 'fa-solid fa-shapes',
                        'summary' => 'Satu pintu gerbang (portal) untuk seluruh pegawai mengakses aplikasi kedinasan seperti SI-KEP, MonLap, Peminjaman Berkas Lelang, Dashboard BMN, dan Aset BPPN.',
                        'details' => 'Setelah berhasil login pada SSO, pengguna disajikan katalog aplikasi dengan indikator status real-time (aktif/pemeliharaan). Pengguna cukup mengklik aplikasi untuk masuk secara instan tanpa perlu memasukkan username atau kata sandi ulang.',
                    ],
                    [
                        'name' => 'Protokol Otentikasi Standar Industri OAuth 2.0',
                        'icon' => 'fa-solid fa-key',
                        'summary' => 'Menggunakan Authorization Code Grant dengan pertukaran Access Token Bearer yang aman dan terenkripsi.',
                        'details' => 'Setiap aplikasi klien terdaftar memiliki Client ID dan Client Secret unik. Ketika pengguna meluncurkan aplikasi, SSO menerbitkan Authorization Code sekali pakai yang kemudian ditukarkan oleh backend aplikasi klien dengan Access Token yang aman.',
                    ],
                    [
                        'name' => 'Single Sign-Out Terkoordinasi',
                        'icon' => 'fa-solid fa-right-from-bracket',
                        'summary' => 'Mengakhiri sesi di seluruh aplikasi klien secara otomatis saat pengguna melakukan logout dari portal SSO.',
                        'details' => 'Fitur Single Sign-Out memastikan tidak ada sesi tertinggal di peramban (browser) yang dapat disalahgunakan oleh pihak tidak berwenang. Endpoint verifikasi sesi (/api/sso/verify-session) secara berkala memeriksa keabsahan sesi aktif.',
                    ],
                    [
                        'name' => 'Pusat Notifikasi Lintas Aplikasi (Notification Hub)',
                        'icon' => 'fa-solid fa-bell',
                        'summary' => 'Agregasi notifikasi tugas dan informasi penting dari seluruh aplikasi ke dalam satu dashboard notifikasi.',
                        'details' => 'Aplikasi klien dapat mengirimkan notifikasi tugas atau informasi kedinasan ke portal SSO melalui API terproteksi. Pengguna dapat langsung mengklik notifikasi untuk menuju berkas atau menu yang memerlukan tindak lanjut.',
                    ],
                ],
            ],
            'user' => [
                'role_name' => 'Pegawai / Staf Pengguna (User)',
                'badge' => 'Pegawai',
                'color' => '#0284c7',
                'summary' => 'Panduan penggunaan sehari-hari bagi seluruh pegawai KPKNL Palembang untuk mengakses aplikasi, mengelola profil, memantau keamanan akun, dan mengelola sesi.',
                'guides' => [
                    [
                        'title' => '1. Masuk ke Portal (Login) & Masalah Sandi',
                        'icon' => 'fa-solid fa-arrow-right-to-bracket',
                        'steps' => [
                            'Akses alamat portal SSO KPKNL Palembang melalui peramban web modern (Google Chrome, Microsoft Edge, atau Mozilla Firefox).',
                            'Masukkan Username (atau NIP/Email terdaftar) dan Kata Sandi.',
                            'Perhatikan informasi pengumuman pada kartu login apabila sistem sedang dalam periode pemeliharaan tertentu.',
                            'Jika mengalami kendala gagal login atau lupa sandi, hubungi Administrator Kepegawaian (Admin) atau Tim Maintenance.',
                        ],
                        'notes' => 'Pastikan URL berawalan protokol HTTPS atau IP resmi jaringan KPKNL Palembang.',
                    ],
                    [
                        'title' => '2. Meluncurkan Aplikasi Kedinasan (App Launcher)',
                        'icon' => 'fa-solid fa-rocket',
                        'steps' => [
                            'Setelah login, halaman Dashboard Portal menampilkan daftar aplikasi yang menjadi hak akses Anda.',
                            'Setiap kartu aplikasi menampilkan logo, nama aplikasi, deskripsi singkat, dan badge role Anda pada aplikasi tersebut.',
                            'Klik tombol "Buka Aplikasi" untuk langsung diarahkan dan otomatis login ke aplikasi target.',
                            'Jika aplikasi menampilkan badge "Pemeliharaan", tombol akses akan dinonaktifkan sementara hingga perbaikan teknis selesai.',
                        ],
                        'notes' => 'Aplikasi yang muncul pada dashboard Anda disesuaikan dengan penugasan kerja yang telah di-assign oleh Admin/Maintenance.',
                    ],
                    [
                        'title' => '3. Pengaturan Profil & Keamanan Sandi Kuat',
                        'icon' => 'fa-solid fa-shield-halved',
                        'steps' => [
                            'Klik menu "Pengaturan Profil" pada sidebar kiri atau avatar di sudut kanan atas.',
                            'Anda dapat memperbarui data nama lengkap, email kedinasan, dan mengunggah foto profil resmi.',
                            'Untuk mengganti kata sandi, isi Kata Sandi Saat Ini, Kata Sandi Baru, dan Konfirmasi Kata Sandi.',
                            'Sistem menerapkan standar keamanan tinggi: Sandi minimal 8 karakter, wajib memuat kombinasi huruf besar (A-Z), huruf kecil (a-z), angka (0-9), dan simbol khusus (@#$%^&*).',
                        ],
                        'notes' => 'Hindari menggunakan kata sandi yang mudah ditebak seperti tanggal lahir atau NIP.',
                    ],
                    [
                        'title' => '4. Manajemen Multi-Sesi & Perangkat Aktif',
                        'icon' => 'fa-solid fa-laptop-file',
                        'steps' => [
                            'Buka menu "Sesi & Perangkat" (/login-sessions) pada sidebar.',
                            'Sistem menampilkan daftar seluruh perangkat/komputer yang sedang login menggunakan akun Anda, lengkap dengan alamat IP, jenis browser, sistem operasi, dan waktu aktivitas terakhir.',
                            'Perangkat yang sedang Anda gunakan saat ini ditandai dengan badge "Perangkat Ini".',
                            'Jika terdapat perangkat yang mencurigakan atau Anda lupa logout di komputer kantor lain, klik tombol "Cabut Sesi" atau "Logout Dari Semua Perangkat Lain".',
                        ],
                        'notes' => 'Langkah ini secara instan mengakhiri sesi di perangkat tersebut demi melindungi akun Anda.',
                    ],
                    [
                        'title' => '5. Pusat Notifikasi Terpadu (Notification Center)',
                        'icon' => 'fa-regular fa-bell',
                        'steps' => [
                            'Ikon lonceng pada bilah atas (topbar) menampilkan jumlah notifikasi yang belum dibaca (unread badge).',
                            'Klik ikon lonceng untuk melihat ringkasan pesan masuk terbaru.',
                            'Klik "Lihat Semua Notifikasi" untuk membuka Pusat Notifikasi lengkap (/notifications).',
                            'Gunakan filter status (Semua / Belum Dibaca / Sudah Dibaca) dan kategori (Aplikasi, Tugas, Info).',
                            'Klik item notifikasi untuk otomatis membuka tautan pekerjaan terkait di aplikasi asal.',
                        ],
                        'notes' => 'Tersedia tombol "Tandai Semua Telah Dibaca" untuk membersihkan badge notifikasi sekaligus.',
                    ],
                ],
            ],
            'admin' => [
                'role_name' => 'Administrator Kepegawaian & Pengguna (Admin)',
                'badge' => 'Administrator',
                'color' => '#4f46e5',
                'summary' => 'Panduan pengelolaan data akun pegawai KPKNL Palembang, pembuatan akun individu, serta pemanfaatan fitur impor massal pegawai berbasis Excel.',
                'guides' => [
                    [
                        'title' => '1. Pengelolaan Akun Pengguna (Kelola User)',
                        'icon' => 'fa-solid fa-users-gear',
                        'steps' => [
                            'Buka menu "Kelola User" (/admin/users) pada sidebar.',
                            'Tabel menampilkan daftar seluruh user lengkap dengan informasi Nama, Username, Email, Role Global, Aplikasi Terhubung, dan Status.',
                            'Gunakan kolom pencarian (search) dan filter berdasarkan Role atau Status (Active, Inactive, Suspended).',
                            'Untuk membuat user baru secara manual, klik tombol "+ Tambah User" di sudut kanan atas.',
                            'Isi identitas lengkap pegawai, pilih Role Global akun, tentukan status, dan klik tombol "Pilih / Edit Aplikasi" untuk mengaitkan aplikasi awal.',
                        ],
                        'notes' => 'Admin memiliki wewenang mengelola akun Pegawai dan Admin, namun tidak diizinkan mengubah akun Maintenance demi stabilitas teknis.',
                    ],
                    [
                        'title' => '2. Pengubahan Status & Reset Sandi Pengguna',
                        'icon' => 'fa-solid fa-user-pen',
                        'steps' => [
                            'Pada baris user yang dituju, klik tombol "Edit".',
                            'Jika seorang pegawai mutasi keluar atau cuti panjang, ubah status akun menjadi "Inactive" atau "Suspended". User tidak akan dapat login ke portal maupun aplikasi terintegrasi.',
                            'Jika pegawai lupa sandi, admin dapat mengisi kolom "Kata Sandi Baru" dengan password sementara yang memenuhi syarat kompleksitas.',
                            'Pegawai dapat diarahkan untuk segera mengganti kata sandi setelah berhasil login kembali.',
                        ],
                        'notes' => 'Setiap aksi perubahan data user tercatat secara otomatis pada Activity Log audit trail.',
                    ],
                    [
                        'title' => '3. Sistem Impor Massal Pegawai via Excel (User Import System)',
                        'icon' => 'fa-solid fa-file-excel',
                        'steps' => [
                            'Buka menu "Kelola User" lalu klik tombol "Impor Pegawai".',
                            'Langkah 1: Klik "Unduh Template Excel" untuk memperoleh format file standar (.xlsx / .csv). Jangan mengubah nama header kolom.',
                            'Langkah 2: Isi data pegawai (Nama, NIP/Username, Email, Sandi default, Status).',
                            'Langkah 3: Unggah berkas yang telah diisi. Sistem akan memvalidasi struktur data dan menampilkan visual progress bar.',
                            'Langkah 4 (Duplicate Conflict Resolver): Apabila sistem mendeteksi ada username atau email yang sudah terdaftar, sistem akan memberikan pilihan cerdas: Lewati (Skip) data duplikat atau Perbarui (Update) data pegawai yang ada.',
                            'Klik "Konfirmasi & Selesaikan Impor" untuk memproses data hingga selesai 100%.',
                        ],
                        'notes' => 'Fitur ini mempercepat registrasi seluruh staf kantor tanpa perlu input satu per satu.',
                    ],
                ],
            ],
            'maintenance' => [
                'role_name' => 'Tim Pengembang & Pemeliharaan Sistem (Maintenance)',
                'badge' => 'Maintenance',
                'color' => '#d97706',
                'summary' => 'Panduan komprehensif bagi tim teknis untuk mengelola kredensial OAuth aplikasi, konfigurasi hak akses & role per-aplikasi, lifecycle orchestrator, CI/CD, backup, dan rollback.',
                'guides' => [
                    [
                        'title' => '1. Manajemen Aplikasi Terintegrasi & Kredensial OAuth2',
                        'icon' => 'fa-solid fa-cubes',
                        'steps' => [
                            'Buka menu "Aplikasi Terintegrasi" (/admin/applications) pada sidebar.',
                            'Untuk mendaftarkan aplikasi baru, klik "+ Daftarkan Aplikasi". Isi Nama, Slug unik, Deskripsi, URL Utama, OAuth Redirect URI, dan unggah Ikon resmi.',
                            'Sistem secara otomatis men-generate Client ID dan Client Secret yang aman dan terenkripsi.',
                            'Buka halaman "Detail Aplikasi" untuk melihat kredensial. Terdapat tombol Salin Cepat dan Sembunyikan/Tampilkan Secret.',
                            'Apabila terjadi potensi kebocoran kredensial, gunakan tombol "Reset Client Secret" untuk membuat kunci rahasia baru.',
                        ],
                        'notes' => 'Setelah reset secret, pastikan berkas konfigurasi .env pada aplikasi klien tujuan langsung diperbarui.',
                    ],
                    [
                        'title' => '2. Konfigurasi Otorisasi Role Per-Aplikasi (Fitur Unggulan)',
                        'icon' => 'fa-solid fa-user-shield',
                        'steps' => [
                            'Buka halaman Detail Aplikasi pada menu "Aplikasi Terintegrasi".',
                            'Metode Dropdown Langsung: Pada tabel "User Terdaftar di Aplikasi Ini", ubah pilihan pada kolom "Role di Aplikasi Ini". Perubahan langsung tersimpan tanpa perlu refresh halaman.',
                            'Pilihan role menyesuaikan modul aplikasi: Pada aplikasi Lelang tersedia role Peminjam, Pelelang, Admin. Pada aplikasi umum tersedia User/Pegawai, Operator, Admin, atau Bawaan Akun.',
                            'Metode Quick Assign: Di panel kanan, pilih Role tujuan, pilih beberapa user sekaligus dengan menahan tombol Ctrl/Cmd, lalu klik "ASSIGN USER TERPILIH".',
                            'Metode Edit User: Melalui menu Kelola User -> Edit User -> "Pilih / Edit Aplikasi" via popup SweetAlert2 dinamis.',
                            'Pencabutan Akses: Klik tombol "Cabut" pada user tertentu untuk mencabut hak aksesnya dari aplikasi terkait.',
                        ],
                        'notes' => 'Endpoint /api/user akan secara otomatis meneruskan parameter app_role spesifik aplikasi saat login.',
                    ],
                    [
                        'title' => '3. Centralized Maintenance & Lifecycle Orchestrator',
                        'icon' => 'fa-solid fa-code-branch',
                        'steps' => [
                            'Akses menu "Maintenance Orchestrator" (/admin/maintenance-orchestrator).',
                            'Dashboard menampilkan status kesehatan (Health Check), branch Git aktif, dan hash commit terakhir dari setiap aplikasi terintegrasi.',
                            'Klik "App Console" pada aplikasi yang diinginkan untuk membuka panel kontrol khusus aplikasi.',
                            'Fitur Cek Git (Fetch Git): Klik tombol "Cek Pembaruan Git" untuk menarik daftar commit terbaru dari remote repository tanpa membuka terminal.',
                            'Fitur Zero-Downtime Deploy: Klik "Deploy Sekarang" untuk menjalankan prosedur deployment terotomasi (git pull, composer install, artisan migrate, clear cache).',
                            'Fitur Instant Rollback & Backup: Sebelum deploy, sistem dapat membuat snapshot cadangan. Jika terjadi bug setelah deploy, klik "Rollback" untuk mengembalikan kode ke commit stabil sebelumnya.',
                            'Deployment Log: Seluruh log konsol hasil deployment tersimpan dan dapat diinspeksi kapan saja.',
                        ],
                        'notes' => 'Orchestrator mengurangi risiko downtime dan menyederhanakan siklus pembaruan perangkat lunak.',
                    ],
                    [
                        'title' => '4. Pusat Pengaturan Sistem (Unified Settings Hub)',
                        'icon' => 'fa-solid fa-gear',
                        'steps' => [
                            'Buka menu "Pengaturan Sistem" (/admin/settings).',
                            'Pemeriksaan Update SSO: Cek status versi portal SSO terhadap repositori Git utama dan jalankan update terpadu.',
                            'Login Info Card: Sesuaikan banner pesan pengumuman pada halaman muka login SSO (contoh: informasi jam layanan, kontak helpdesk, atau pemberitahuan maintenance).',
                            'Mode Pemeliharaan (Maintenance Mode): Aktifkan mode pemeliharaan portal jika akan dilakukan migrasi besar-besaran dengan pesan khusus untuk pengguna.',
                        ],
                        'notes' => 'Seluruh perubahan pengaturan langsung diterapkan secara instan ke seluruh sistem.',
                    ],
                    [
                        'title' => '5. Cadangan & Pemulihan Database SSO (Backup & Restore)',
                        'icon' => 'fa-solid fa-database',
                        'steps' => [
                            'Buka menu "Pengaturan Sistem" -> tab "Backup & Restore" (/admin/backup).',
                            'Klik tombol "Buat Backup Baru" untuk membuat snapshot database SSO secara instan.',
                            'Daftar file backup dilengkapi informasi ukuran file dan tanggal pembuatan.',
                            'Unduh berkas .sql ke penyimpanan aman lokal sebagai arsip rutin.',
                            'Untuk memulihkan data, gunakan tombol "Restore" pada berkas yang ditargetkan.',
                        ],
                        'notes' => 'Lakukan backup berkala sebelum melakukan perubahan skema migrasi database besar.',
                    ],
                ],
            ],
            'superadmin' => [
                'role_name' => 'Pimpinan & Pengawas Tertinggi (Superadmin)',
                'badge' => 'Superadmin',
                'color' => '#dc2626',
                'summary' => 'Panduan pengawasan integritas operasional, audit trail seluruh aktivitas sistem, dan tata kelola akun tingkat tinggi.',
                'guides' => [
                    [
                        'title' => '1. Pengawasan Menyeluruh Ekosistem SSO',
                        'icon' => 'fa-solid fa-gauge-high',
                        'steps' => [
                            'Superadmin memiliki hak akses menyeluruh untuk memantau ringkasan statistik: total user terdaftar, user aktif, aplikasi online, dan volume notifikasi.',
                            'Memastikan seluruh aplikasi kedinasan berjalan dengan lancar dan hak akses setiap pegawai sesuai dengan tugas fungsi kedinasan.',
                        ],
                        'notes' => 'Gunakan dashboard utama untuk melihat gambaran cepat kesehatan ekosistem TI kantor.',
                    ],
                    [
                        'title' => '2. Jejak Audit & Log Aktivitas Global (Audit Trail)',
                        'icon' => 'fa-solid fa-clock-rotate-left',
                        'steps' => [
                            'Buka menu "Log Aktivitas" (/activity-logs) pada sidebar.',
                            'Sistem merekam setiap kejadian krusial: Autentikasi user (login/logout), pendaftaran aplikasi baru, perubahan role user per-aplikasi, regenerate secret, eksekusi deployment, hingga perubahan data profil.',
                            'Setiap entri memuat User Pelaksana, Jenis Aktivitas, Deskripsi Rinci, Alamat IP, dan Waktu Eksekusi.',
                            'Gunakan kolom pencarian dan filter untuk menelusuri aktivitas tertentu saat melakukan investigasi keamanan.',
                        ],
                        'notes' => 'Log aktivitas disimpan permanen dan tidak dapat dihapus sembarangan demi kepatuhan tata kelola TI.',
                    ],
                    [
                        'title' => '3. Kebijakan Keamanan Akun & Proteksi Peran Khusus',
                        'icon' => 'fa-solid fa-lock',
                        'steps' => [
                            'Superadmin dapat mengelola akun pengguna dan administrator.',
                            'Sistem menerapkan proteksi peran: Akun dengan role Maintenance diproteksi secara khusus dari penghapusan atau penurunan hak akses tidak sengaja untuk menjamin ketersediaan akses pemulihan sistem darurat.',
                            'Pastikan pergantian kata sandi berkala dilakukan oleh seluruh jajaran pengguna.',
                        ],
                        'notes' => 'Prinsip least privilege dan separation of duties diterapkan secara ketat dalam arsitektur SSO KPKNL Palembang.',
                    ],
                ],
            ],
        ];
    }
}

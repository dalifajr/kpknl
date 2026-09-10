# LAPORAN LENGKAP MIGRASI PHP NATIVE KE LARAVEL
## Sistem Pengelolaan Risalah Lelang & Peminjaman - KPKNL

Dokumen ini mencatat seluruh hasil audit teknis, implementasi arsitektur, pengujian, dan standardisasi keamanan dari migrasi aplikasi procedural **PHP Native** ke framework **Laravel 11.x**.

---

### 1. Informasi Lingkungan & Framework
- **Framework Target**: Laravel 11.56.1
- **PHP Version**: PHP 8.2.12 (CLI / FPM)
- **Composer Version**: 2.8.9
- **Database Engine**: MySQL / MariaDB (Database: `kpknl`)
- **Frontend / Rendering**: Server-rendered Blade Layouts + Custom CSS + Chart.js
- **Node / NPM**: Node.js v24.12.0 / NPM 11.6.2

---

### 2. Konektivitas & Struktur Database
Aplikasi Laravel berhasil terhubung dan sepenuhnya kompatibel dengan database existing `kpknl` (>10.500 rows) tanpa modifikasi destruktif pada data aktif:

| Nama Tabel | Total Baris Aktual | Model Eloquent | Konfigurasi Timestamp | Primary Key |
|---|---|---|---|---|
| `users` | 19 | `App\Models\User` | Custom (`created_at`) | `id` (AI, Int) |
| `risalah_pending` | 8 | `App\Models\RisalahPending` | `$timestamps = false` | `id` (AI, Int) |
| `risalah_minuta` | 8.529 | `App\Models\RisalahMinuta` | `$timestamps = false` | `id` (AI, Int) |
| `risalah_tap` | 1.340 | `App\Models\RisalahTap` | `$timestamps = false` | `id` (AI, Int) |
| `risalah_batal` | 614 | `App\Models\RisalahBatal` | `$timestamps = false` | `id` (AI, Int) |
| `risalah_revisi` | 1 | `App\Models\RisalahRevisi` | `$timestamps = false` | `id` (AI, Int) |
| `peminjaman` | 67 | `App\Models\Peminjaman` | `$timestamps = false` | `id` (AI, Int) |

---

### 3. Daftar Model Eloquent
- [`App\Models\User`](file:///e:/PROGRAM%20MAGANG%20%283%29/PROGRAM%20MAGANG/laravel/app/Models/User.php): Otentikasi dengan helper role (`isAdmin()`, `isPelelang()`, `isPeminjam()`).
- [`App\Models\RisalahPending`](file:///e:/PROGRAM%20MAGANG%20%283%29/PROGRAM%20MAGANG/laravel/app/Models/RisalahPending.php): Menampung pengajuan risalah baru sebelum divalidasi.
- [`App\Models\RisalahMinuta`](file:///e:/PROGRAM%20MAGANG%20%283%29/PROGRAM%20MAGANG/laravel/app/Models/RisalahMinuta.php): Register risalah minuta resmi.
- [`App\Models\RisalahTap`](file:///e:/PROGRAM%20MAGANG%20%283%29/PROGRAM%20MAGANG/laravel/app/Models/RisalahTap.php): Register risalah lelang tidak ada penawaran (TAP).
- [`App\Models\RisalahBatal`](file:///e:/PROGRAM%20MAGANG%20%283%29/PROGRAM%20MAGANG/laravel/app/Models/RisalahBatal.php): Register risalah lelang batal.
- [`App\Models\RisalahRevisi`](file:///e:/PROGRAM%20MAGANG%20%283%29/PROGRAM%20MAGANG/laravel/app/Models/RisalahRevisi.php): Menampung risalah yang dikembalikan untuk perbaikan oleh Admin.
- [`App\Models\Peminjaman`](file:///e:/PROGRAM%20MAGANG%20%283%29/PROGRAM%20MAGANG/laravel/app/Models/Peminjaman.php): Riwayat & status peminjaman dokumen fisik risalah.

---

### 4. Daftar Controller & Service Layer

#### Controller Layer
- [`App\Http\Controllers\Auth\AuthController`](file:///e:/PROGRAM%20MAGANG%20%283%29/PROGRAM%20MAGANG/laravel/app/Http/Controllers/Auth/AuthController.php): Otentikasi login fleksibel (username ATAU email), registrasi dengan whitelist role, dan secure logout.
- [`App\Http\Controllers\DashboardController`](file:///e:/PROGRAM%20MAGANG%20%283%29/PROGRAM%20MAGANG/laravel/app/Http/Controllers/DashboardController.php): Dashboard statistik terdedikasi per peran (Admin, Pelelang, Peminjam).
- [`App\Http\Controllers\RisalahController`](file:///e:/PROGRAM%20MAGANG%20%283%29/PROGRAM%20MAGANG/laravel/app/Http/Controllers/RisalahController.php): Manajemen risalah Minuta/TAP/Batal, filter tanggal & parameter, create, edit, riwayat pelelang.
- [`App\Http\Controllers\ValidasiRisalahController`](file:///e:/PROGRAM%20MAGANG%20%283%29/PROGRAM%20MAGANG/laravel/app/Http/Controllers/ValidasiRisalahController.php): Antrian validasi Admin, approval penetapan Box/Lemari, pengembalian revisi, dan pengajuan ulang oleh Pelelang.
- [`App\Http\Controllers\PeminjamanController`](file:///e:/PROGRAM%20MAGANG%20%283%29/PROGRAM%20MAGANG/laravel/app/Http/Controllers/PeminjamanController.php): Katalog peminjaman multi-item, alur approval, konfirmasi terima, pengajuan pengembalian, dan verifikasi fisik pengembalian.
- [`App\Http\Controllers\GrafikController`](file:///e:/PROGRAM%20MAGANG%20%283%29/PROGRAM%20MAGANG/laravel/app/Http/Controllers/GrafikController.php): Menyajikan data agregat untuk 6 grafik statistik Chart.js.
- [`App\Http\Controllers\ExportController`](file:///e:/PROGRAM%20MAGANG%20%283%29/PROGRAM%20MAGANG/laravel/app/Http/Controllers/ExportController.php): Export data dalam format CSV/Excel yang aman dan sinkron dengan filter aktif.

#### Service Layer
- [`App\Services\RisalahValidationService`](file:///e:/PROGRAM%20MAGANG%20%283%29/PROGRAM%20MAGANG/laravel/app/Services/RisalahValidationService.php): Mengatur pemindahan data atomic antar tabel (`risalah_pending` -> `minuta/tap/batal`, `risalah_pending` -> `risalah_revisi`, `risalah_revisi` -> `risalah_pending`) menggunakan `DB::transaction()` dan row locking.
- [`App\Services\PeminjamanService`](file:///e:/PROGRAM%20MAGANG%20%283%29/PROGRAM%20MAGANG/laravel/app/Services/PeminjamanService.php): Menjamin concurrency safety saat multi-user meminjam risalah dengan `lockForUpdate()`, serta memulihkan status risalah menjadi `tersedia` saat pengembalian tervalidasi.
- [`App\Services\RisalahExportService`](file:///e:/PROGRAM%20MAGANG%20%283%29/PROGRAM%20MAGANG/laravel/app/Services/RisalahExportService.php): Generator CSV chunked dengan UTF-8 BOM untuk efisiensi memori.

---

### 5. Middleware & Proteksi Rute
- [`App\Http\Middleware\RoleMiddleware`](file:///e:/PROGRAM%20MAGANG%20%283%29/PROGRAM%20MAGANG/laravel/app/Http/Middleware/RoleMiddleware.php): Didaftarkan sebagai alias `role` pada `bootstrap/app.php`. Memastikan user dengan role unauthorized mendapatkan HTTP 403 Forbidden secara server-side.

---

### 6. Perbaikan Bug & Celah Keamanan PHP Native
1. **Pencegahan SQL Injection**: Seluruh query procedural yang sebelumnya menggabungkan string mentah telah diganti dengan Eloquent ORM & parameterized Query Builder.
2. **Penghapusan Mutasi Data via GET**: Semua aksi mutasi (approve validasi, revisi, approve peminjaman, konfirmasi terima, pengembalian) dipindahkan ke HTTP `POST` atau `PUT` dengan proteksi `@csrf`.
3. **Pemberantasan Inconsistent Redirects**: Redirect typo pada `cek_login.php` telah disatukan melalui Role-based redirection pattern di `AuthController`.
4. **Pencegahan Race Condition**: Peminjaman simultan pada berkas yang sama dilindungi oleh pessimistic locking (`lockForUpdate()`) di dalam database transaction.
5. **Enforcement Identitas Pelelang & Peminjam**: Field `nama_pelelang` dan `nama_peminjam` kini diambil langsung dari `Auth::user()->username` di server dan tidak lagi mempercayai input tersembunyi (hidden input) dari browser.
6. **Session Fixation Prevention**: Session ID diregenerasi saat login (`$request->session()->regenerate()`) dan token dibersihkan saat logout (`$request->session()->invalidate()`).

---

### 7. Hasil Pengujian Otomatis (Testing Suite)
Pengujian dieksekusi secara terisolasi menggunakan SQLite in-memory (`:memory:`) untuk menjamin zero-risk terhadap data MySQL produksi:

```text
PASS  Tests\Unit\ExampleTest
  ✓ that true is true

PASS  Tests\Feature\AuthTest
  ✓ user can login with username
  ✓ user can login with email
  ✓ user cannot login with invalid password
  ✓ user can register and is redirected to dashboard
  ✓ user can logout

PASS  Tests\Feature\ExampleTest
  ✓ the application redirects guest to login

PASS  Tests\Feature\RoleAuthorizationTest
  ✓ admin can access admin dashboard
  ✓ pelelang cannot access admin dashboard
  ✓ peminjam cannot access admin dashboard
  ✓ peminjam cannot access pelelang routes

PASS  Tests\Feature\RisalahTest
  ✓ pelelang can create risalah and it enters pending
  ✓ user can view risalah minuta listing

PASS  Tests\Feature\RisalahValidationTest
  ✓ admin can validate and approve pending minuta
  ✓ admin can validate and approve pending tap

PASS  Tests\Feature\RisalahRevisionTest
  ✓ admin can request revision and pelelang can resubmit

PASS  Tests\Feature\PeminjamanTest
  ✓ peminjam can borrow multiple available risalah

PASS  Tests\Feature\ReturnPeminjamanTest
  ✓ full borrow confirmation and return cycle

Tests:    18 passed (65 assertions)
Duration: 1.11s
```

---

### 8. Panduan Menjalankan Aplikasi

#### Skenario A: Menggunakan Database MySQL `kpknl` Existing
1. Buka terminal di folder project Laravel:
   ```bash
   cd laravel
   ```
2. Pastikan file `.env` telah mengarah ke database `kpknl`:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=kpknl
   DB_USERNAME=root
   DB_PASSWORD=
   DB_COLLATION=utf8mb4_unicode_ci
   ```
3. Jalankan development server:
   ```bash
   php artisan serve
   ```
4. Akses aplikasi melalui browser di: `http://127.0.0.1:8000`.

#### Skenario B: Setup Database Kosong Baru dari Nol
1. Buat database baru di MySQL (misal: `kpknl_baru`).
2. Sesuaikan `DB_DATABASE=kpknl_baru` pada file `.env`.
3. Jalankan migration dan seeder:
   ```bash
   php artisan migrate
   php artisan db:seed
   ```
   *Akun bawaan development:*
   - **Admin**: `username`: `admin` | `password`: `admin123`
   - **Pelelang**: `username`: `pelelang1` | `password`: `pelelang123`
   - **Peminjam**: `username`: `peminjam1` | `password`: `peminjam123`

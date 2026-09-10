# Dokumen Audit Migrasi PHP Native ke Laravel (KPKNL)

## A. Daftar Halaman Lama & Pemetaan ke Laravel

| File PHP Native Lama | Peran / Fungsi | Route / Controller Laravel |
|---|---|---|
| `index.html` / `login.php` / `cek_login.php` | Halaman login & otentikasi user | `GET /login` & `POST /login` -> `AuthController@showLogin`, `AuthController@login` |
| `register.html` / `register_admin.html` / `register_pelelang.html` / `register.php` | Pendaftaran akun baru | `GET /register` & `POST /register` -> `AuthController@showRegister`, `AuthController@register` |
| `logout.php` | Mengakhiri session user | `POST /logout` -> `AuthController@logout` |
| `admin_dashboard.php` | Dashboard utama Administrator | `GET /admin/dashboard` -> `DashboardController@admin` |
| `pelelang_dashboard.php` | Dashboard utama Pejabat Lelang | `GET /pelelang/dashboard` -> `DashboardController@pelelang` |
| `peminjam_dashboard.php` | Dashboard utama Peminjam | `GET /peminjam/dashboard` -> `DashboardController@peminjam` |
| `tambah_risalah.php` / `proses_tambah.php` | Form & proses pengajuan risalah oleh Pelelang | `GET /pelelang/risalah/create` & `POST /pelelang/risalah` -> `RisalahController@create`, `RisalahController@store` |
| `menu_risalah.php` | Menu navigasi jenis risalah (Minuta, TAP, Batal) | Terintegrasi ke layout navbar & dashboard |
| `cek_data_risalah.php` | Tinjau daftar, filter, pencarian risalah Minuta | `GET /risalah/minuta` -> `RisalahController@minuta` |
| `cek_data_tap.php` | Tinjau daftar, filter, pencarian risalah TAP | `GET /risalah/tap` -> `RisalahController@tap` |
| `cek_data_batal.php` | Tinjau daftar, filter, pencarian risalah Batal | `GET /risalah/batal` -> `RisalahController@batal` |
| `edit_risalah.php` / `edit_risalah_tap.php` / `edit_risalah_batal.php` | Form edit & pembaruan data risalah | `GET /risalah/{type}/{id}/edit` & `PUT /risalah/{type}/{id}` -> `RisalahController@edit`, `RisalahController@update` |
| `riwayat_risalah.php` | Riwayat risalah yang diajukan oleh pelelang yang login | `GET /pelelang/riwayat` -> `RisalahController@history` |
| `pelelang_pending.php` | Daftar risalah berstatus pending milik pelelang | `GET /pelelang/pending` -> `RisalahController@pending` |
| `validasi.php` / `form_validasi.php` / `proses_validasi.php` | Antrian verifikasi risalah oleh Admin & penetapan Box/Lemari | `GET /admin/validasi` & `POST /admin/validasi/{id}/approve` -> `ValidasiRisalahController@index`, `ValidasiRisalahController@approve` |
| `form_revisi.php` / `proses_revisi.php` | Pengembalian risalah oleh Admin dengan rincian catatan revisi | `POST /admin/validasi/{id}/revisi` -> `ValidasiRisalahController@requestRevision` |
| `cek_revisi.php` / `kirim_kembali.php` / `hapus_revisi.php` | Tinjau & pengiriman ulang data revisi oleh Pelelang | `GET /pelelang/revisi` & `POST /pelelang/revisi/{id}/resubmit` -> `ValidasiRisalahController@pelelangRevisi`, `ValidasiRisalahController@resubmitRevision` |
| `pilih_pinjam.php` / `pinjam_risalah.php` / `pinjam_risalah_tap.php` / `pinjam_risalah_batal.php` | Katalog risalah berstatus tersedia untuk dipinjam | `GET /peminjam/katalog` -> `PeminjamanController@katalog` |
| `pinjam_peminjam.php` / `alasan_peminjaman.php` / `proses_pinjam.php` | Pemilihan multi-item risalah & pengajuan form peminjaman | `POST /peminjam/pinjam` -> `PeminjamanController@store` |
| `cek_pinjaman.php` / `peminjam.php` | Daftar riwayat & status peminjaman milik peminjam | `GET /peminjam/pinjaman` -> `PeminjamanController@index` |
| `proses_validasi_pinjam.php` | Persetujuan peminjaman oleh Admin | `POST /admin/peminjaman/{id}/approve` -> `PeminjamanController@adminApprove` |
| `proses_terima.php` | Konfirmasi fisik penerimaan risalah oleh Peminjam | `POST /peminjam/pinjaman/{id}/terima` -> `PeminjamanController@konfirmasiTerima` |
| `proses_kembalikan.php` | Pengajuan pengembalian risalah oleh Peminjam | `POST /peminjam/pinjaman/{id}/kembalikan` -> `PeminjamanController@ajukanKembali` |
| `validasi_kembali.php` / `admin_kembalikan.php` / `proses_validasi_kembali.php` | Verifikasi pengembalian fisik oleh Admin & pemulihan status | `GET /admin/pengembalian` & `POST /admin/pengembalian/{id}/approve` -> `PeminjamanController@adminApproveReturn` |
| `cek_grafik.php` / `grafik_*.php` | Visualisasi statistik bulanan, tahunan, pelelang, peminjam | `GET /grafik/*` -> `GrafikController@*` |

---

## B. Audit Struktur Database Aktual (`kpknl`)

Database MySQL: `kpknl` (Total > 10.500 rows)

1. **`users`** (19 rows)
   - `id` (int(11), PK, AI)
   - `username` (varchar(50), UNIQUE)
   - `email` (varchar(100), UNIQUE)
   - `password` (varchar(255))
   - `role` (enum('admin','pelelang','peminjam'))
   - `created_at` (timestamp, default current_timestamp)

2. **`risalah_pending`** (8 rows)
   - `id` (int(11), PK, AI)
   - `no_risalah` (varchar(50))
   - `jenis` (enum('minuta','tap','batal'))
   - `tgl_risalah` (date)
   - `tgl_validasi` (date, nullable)
   - `nama_pelelang` (varchar(100))
   - `pemohon_lelang` (varchar(100))
   - `link_erisalah` (text, nullable)
   - `box` (varchar(50), nullable)
   - `lemari` (varchar(50), nullable)
   - `keterangan` (text, nullable)
   - `catatan` (text, nullable)
   - `status` (enum('belum_validasi','validasi'), default 'belum_validasi')

3. **`risalah_minuta`** (8.529 rows)
   - `id` (int(11), PK, AI)
   - `no_risalah` (varchar(50))
   - `tgl_risalah` (date)
   - `tgl_validasi` (date, nullable)
   - `nama_pelelang` (varchar(100))
   - `pemohon_lelang` (varchar(100))
   - `link_erisalah` (text, nullable)
   - `box` (varchar(50), nullable)
   - `lemari` (varchar(50), nullable)
   - `status` (enum('tersedia','sedang_dipinjam'), default 'tersedia')

4. **`risalah_tap`** (1.340 rows)
   - `id` (int(11), PK, AI)
   - `no_risalah` (varchar(50))
   - `tgl_risalah` (date)
   - `tgl_validasi` (date, nullable)
   - `nama_pelelang` (varchar(100))
   - `pemohon_lelang` (varchar(100))
   - `link_erisalah` (text, nullable)
   - `box` (varchar(50), nullable)
   - `lemari` (varchar(50), nullable)
   - `status` (enum('tersedia','sedang_dipinjam'), default 'tersedia')

5. **`risalah_batal`** (614 rows)
   - `id` (int(11), PK, AI)
   - `no_risalah` (varchar(50))
   - `tgl_risalah` (date)
   - `tgl_validasi` (date, nullable)
   - `nama_pelelang` (varchar(100))
   - `pemohon_lelang` (varchar(100))
   - `link_erisalah` (text, nullable)
   - `box` (varchar(50), nullable)
   - `lemari` (varchar(50), nullable)
   - `status` (enum('tersedia','sedang_dipinjam'), default 'tersedia')

6. **`risalah_revisi`** (1 row)
   - `id` (int(11), PK, AI)
   - `no_risalah` (varchar(50))
   - `jenis` (enum('minuta','tap','batal'))
   - `tgl_risalah` (date)
   - `tgl_revisi` (date)
   - `nama_pelelang` (varchar(100))
   - `pemohon_lelang` (varchar(100))
   - `catatan` (text, nullable)
   - `status` (enum('revisi','dikirim'), default 'revisi')
   - `catatan_no` (text, nullable)
   - `catatan_jenis` (text, nullable)
   - `catatan_tgl` (text, nullable)
   - `catatan_pelelang` (text, nullable)
   - `catatan_pemohon` (text, nullable)

7. **`peminjaman`** (67 rows)
   - `id` (int(11), PK, AI)
   - `nama_peminjam` (varchar(100))
   - `no_risalah` (varchar(50))
   - `tgl_risalah` (date)
   - `nama_pelelang` (varchar(100))
   - `pemohon_lelang` (varchar(150))
   - `box` (varchar(10), nullable)
   - `lemari` (varchar(10), nullable)
   - `tgl_peminjaman` (date)
   - `tgl_pengembalian` (date, nullable)
   - `status` (varchar(50))
   - `alasan_peminjaman` (text, nullable)

*Catatan*: Tabel `peminjaman_tap` tidak ada di database aktual; semua peminjaman tercatat di tabel `peminjaman`.

---

## C. Bug & Celah Keamanan PHP Native yang Ditemukan

1. **SQL Injection Vulnerabilities**: Berbagai query pada PHP native menggunakan interpolasi string langsung (contoh: `SELECT * FROM users WHERE username = '$username'`).
   - *Solusi Laravel*: Menggunakan parameter binding via Eloquent ORM & Query Builder.
2. **State Mutation via HTTP GET**: Beberapa aksi seperti `proses_validasi_pinjam.php?id=...`, `hapus_revisi.php?id=...`, dan `admin_kembalikan.php?id=...` mengubah status database melalui request GET yang rentan eksploitasi CSRF / accidental execution.
   - *Solusi Laravel*: Seluruh aksi mutasi data dipindahkan ke method `POST`/`PUT`/`PATCH` dengan proteksi `@csrf`.
3. **Inconsistent Redirects**: File `cek_login.php` mengarahkan ke file dengan nama yang salah (`dashboard_admin.php` vs `admin_dashboard.php`).
   - *Solusi Laravel*: Named routes terpusat dan role-based redirect handler.
4. **Session Fixation & Hardcoded Credentials**: Kredensial MySQL berada di `db.php`.
   - *Solusi Laravel*: Migrasi ke `.env`, regenerasi session id saat login & invalidasi session saat logout.
5. **Race Condition pada Peminjaman**: Peminjaman multi-user tanpa database locking memungkinkan dua peminjam meminjam risalah yang sama secara simultan.
   - *Solusi Laravel*: Menggunakan `DB::transaction()` dan pessimistic row locking `lockForUpdate()`.

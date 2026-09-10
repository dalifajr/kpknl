# Master Prompt Migrasi PHP Native ke Laravel

> File ini berisi instruksi lengkap untuk Codex agar memigrasikan project PHP Native **PROGRAM MAGANG** ke Laravel secara bertahap, aman, dan tetap mempertahankan business flow serta data database existing.

---

buat folder baru untuk khusus projet laravel di dalam folder program magang dan baca prompt ini, Saya ingin Anda bertindak sebagai **Senior Laravel Developer / Software Migration Engineer**.

Tugas Anda adalah **MIGRASI PENUH project PHP Native yang sedang terbuka di workspace ini menjadi aplikasi Laravel yang rapi, aman, terstruktur, dan tetap mempertahankan seluruh fungsi aplikasi lama.**

JANGAN hanya memberikan tutorial atau contoh kode.

Saya ingin Anda:

* membaca project PHP native yang ada,
* menganalisis seluruh file,
* membuat project Laravel,
* membuat konfigurasi database,
* membuat Model,
* Migration,
* Controller,
* Form Request,
* Middleware,
* Route,
* Service jika diperlukan,
* Blade View,
* authentication,
* authorization role,
* filter,
* export,
* grafik,
* transaksi database,
* testing,
* dan memastikan aplikasi dapat dijalankan.

Kerjakan langsung file-file project.

# =========================================================

# 1. INFORMASI PROJECT LAMA

# =========================================================

Project lama menggunakan:

* PHP Native / procedural PHP
* MySQL
* mysqli
* Session PHP native
* HTML
* CSS
* JavaScript
* Chart.js
* Export data ke format Excel sederhana
* Database bernama:

kpknl

Konfigurasi database lama ditemukan di `db.php`:

DB_HOST=localhost
DB_DATABASE=kpknl
DB_USERNAME=root
DB_PASSWORD=

Jangan hard-code kredensial tersebut ke source Laravel.

Gunakan `.env`.

Project memiliki sekitar 60 file dan file PHP penting antara lain:

* index.html
* login.php
* cek_login.php
* register.php
* register.html
* register_admin.html
* register_pelelang.html

Dashboard:

* admin_dashboard.php
* pelelang_dashboard.php
* peminjam_dashboard.php

Risalah:

* tambah_risalah.php
* proses_tambah.php
* menu_risalah.php
* cek_data_risalah.php
* cek_data_tap.php
* cek_data_batal.php
* edit_risalah.php
* edit_risalah_tap.php
* edit_risalah_batal.php
* riwayat_risalah.php

Validasi:

* validasi.php
* form_validasi.php
* proses_validasi.php
* form_revisi.php
* proses_revisi.php
* cek_revisi.php
* kirim_kembali.php
* validasi_kembali.php
* proses_validasi_kembali.php
* hapus_revisi.php

Peminjaman:

* pilih_pinjam.php
* pinjam_risalah.php
* pinjam_risalah_tap.php
* pinjam_risalah_batal.php
* pinjam_peminjam.php
* alasan_peminjaman.php
* proses_pinjam.php
* proses_pinjam_tap.php
* proses_pinjam_batal.php
* cek_pinjaman.php
* peminjam.php
* proses_validasi_pinjam.php
* proses_validasi_pinjam_tap.php
* proses_terima.php
* proses_kembalikan.php
* validasi_kembali.php
* admin_kembalikan.php

Grafik:

* cek_grafik.php
* grafik_bulanan.php
* grafik_tahunan.php
* grafik_bulanan_pelelangan.php
* grafik_tahunan_pelelangan.php
* grafik_bulanan_peminjaman.php
* grafik_tahunan_peminjaman.php
* grafik_pelelang.php
* grafik_peminjam.php

Asset:

* style_dashboard.css
* style_pinjam.css
* logo.png

# =========================================================

# 2. ATURAN PALING PENTING

# =========================================================

WAJIB ikuti aturan berikut.

1. Jangan menghapus source code PHP Native lama.

2. Jangan menjalankan:

php artisan migrate:fresh
php artisan db:wipe

atau operasi:

DROP DATABASE
DROP TABLE
TRUNCATE TABLE

terhadap database lama.

3. Database `kpknl` kemungkinan sudah memiliki data.

Anggap database tersebut sebagai DATA PENTING.

4. Sebelum membuat migration, audit struktur database sebenarnya.

Jika koneksi MySQL tersedia, gunakan query seperti:

SHOW TABLES;

DESCRIBE users;
DESCRIBE risalah_pending;
DESCRIBE risalah_minuta;
DESCRIBE risalah_tap;
DESCRIBE risalah_batal;
DESCRIBE risalah_revisi;
DESCRIBE peminjaman;
DESCRIBE peminjaman_tap;

dan jika diperlukan:

SHOW CREATE TABLE nama_tabel;

5. Jangan mengarang kolom database apabila bisa diketahui dari database atau source lama.

6. Jika database tidak dapat diakses, analisis semua:

* SELECT
* INSERT
* UPDATE
* DELETE
* form input

dalam project PHP lama untuk merekonstruksi schema.

7. Jangan langsung mengubah schema database existing.

Prioritas pertama adalah membuat Laravel dapat bekerja dengan database lama.

8. Jika membuat migration untuk instalasi baru, migration harus merepresentasikan database lama tetapi JANGAN dijalankan ke database produksi/existing sebelum dipastikan aman.

9. Jangan mengubah nama tabel lama sembarangan.

10. Jangan mengubah business flow aplikasi sebelum versi Laravel memiliki fungsi yang setara dengan PHP Native.

# =========================================================

# 3. TARGET FRAMEWORK

# =========================================================

Target utama:

Laravel 13.x

Tetapi terlebih dahulu jalankan:

php -v
composer --version
node -v
npm -v

Pastikan environment mendukung Laravel yang digunakan.

Jika Laravel 13 tidak kompatibel dengan environment lokal:

* jelaskan incompatibility pada laporan,
* jangan diam-diam melakukan downgrade,
* gunakan versi Laravel terbaru yang kompatibel hanya jika benar-benar diperlukan.

Gunakan:

* Laravel
* Blade
* Eloquent ORM / Query Builder
* MySQL
* Laravel Authentication berbasis session
* Middleware
* Form Request Validation
* Database Transaction
* CSRF Protection
* Chart.js

Tidak perlu React/Vue/Inertia kecuali benar-benar diperlukan.

Saya lebih memilih aplikasi server-rendered menggunakan Blade karena aplikasi lama juga server-rendered.

# =========================================================

# 4. AUDIT PROJECT TERLEBIH DAHULU

# =========================================================

Sebelum menulis banyak kode, analisis SELURUH project PHP Native.

Buat dokumen:

docs/MIGRATION_AUDIT.md

Isi minimal:

## A. Daftar halaman lama

File lama → fungsi → halaman Laravel pengganti.

## B. Database

Catat:

* tabel
* primary key
* semua kolom
* tipe data
* nullable
* default
* index
* unique constraint jika ada.

## C. Authentication

Identifikasi mekanisme:

* login
* logout
* register
* session
* role.

## D. Business flow

Dokumentasikan:

* pengajuan risalah
* validasi risalah
* revisi risalah
* pengembalian revisi
* peminjaman
* validasi peminjaman
* konfirmasi peminjam
* pengembalian
* validasi pengembalian
* grafik
* export.

## E. Bug/masalah existing

Contohnya sudah terlihat terdapat dua mekanisme login:

login.php
cek_login.php

dan redirect yang tidak konsisten.

`cek_login.php` mengarah ke:

dashboard_admin.php
dashboard_pelelang.php
dashboard_peminjam.php

padahal file aktual antara lain:

admin_dashboard.php
pelelang_dashboard.php
peminjam_dashboard.php

Satukan mekanisme tersebut dalam Laravel.

Cari masalah lain seperti:

* SQL Injection
* action perubahan data melalui GET
* duplikasi koneksi database
* query raw tidak aman
* duplikasi source code
* status dengan kapitalisasi tidak konsisten
* validasi kurang
* session authorization manual.

Dokumentasikan semuanya.

# =========================================================

# 5. DATABASE YANG SUDAH DIIDENTIFIKASI

# =========================================================

Dari source lama, tabel yang digunakan minimal:

1. users
2. risalah_pending
3. risalah_minuta
4. risalah_tap
5. risalah_batal
6. risalah_revisi
7. peminjaman
8. peminjaman_tap

JANGAN menghapus `peminjaman_tap` walaupun terlihat kemungkinan merupakan kode lama/duplikasi sebelum Anda memastikan apakah masih digunakan.

# =========================================================

# 6. KOLOM YANG TERLIHAT DARI SOURCE LAMA

# =========================================================

Gunakan bagian ini sebagai referensi, TETAPI tetap cocokkan dengan schema database sebenarnya.

## users

Terlihat menggunakan:

id
username
email
password
role

Role:

admin
pelelang
peminjam

## risalah_pending

Minimal terlihat menggunakan:

id
no_risalah
jenis
tgl_risalah
nama_pelelang
pemohon_lelang
status

Source lain kemungkinan menggunakan:

link_erisalah
box
lemari
keterangan
catatan

Audit schema sebenarnya.

Status yang terlihat:

belum_validasi

## risalah_minuta

Minimal:

id
no_risalah
tgl_risalah
nama_pelelang
pemohon_lelang
link_erisalah
box
lemari
tgl_validasi
status

## risalah_tap

Minimal:

id
no_risalah
tgl_risalah
nama_pelelang
pemohon_lelang
link_erisalah
box
lemari
tgl_validasi
status

## risalah_batal

Minimal:

id
no_risalah
tgl_risalah
nama_pelelang
pemohon_lelang
link_erisalah
tgl_validasi
status

Audit apakah box/lemari juga tersedia.

## risalah_revisi

Minimal:

id
no_risalah
jenis
tgl_risalah
nama_pelelang
pemohon_lelang
catatan_no
catatan_jenis
catatan_tgl
catatan_pelelang
catatan_pemohon
status
tgl_revisi

Status yang terlihat:

dikembalikan

## peminjaman

Minimal:

id
nama_peminjam
no_risalah
tgl_risalah
nama_pelelang
pemohon_lelang
box
lemari
alasan_peminjaman
tgl_peminjaman
tgl_pengembalian
status

Status yang ditemukan pada source lama:

Proses Peminjaman
Menunggu Konfirmasi Peminjam
Sedang Dipinjam
Proses Pengembalian
Sudah Dikembalikan

## Status risalah

Source lama menggunakan beberapa variasi:

tersedia
Tersedia
sedang_dipinjam
Sedang Dipinjam

JANGAN langsung mengganti data existing.

Buat strategi normalisasi secara aman.

Untuk tahap migrasi awal, aplikasi Laravel harus dapat membaca nilai lama tersebut.

Jika ingin membuat constants/Enum, sediakan compatibility mapping.

# =========================================================

# 7. KONFIGURASI .ENV

# =========================================================

Konfigurasi Laravel harus membaca database dari `.env`.

Contoh development:

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kpknl
DB_USERNAME=root
DB_PASSWORD=

Jangan commit `.env`.

Pastikan `.env.example` tidak memiliki password sensitif.

Set:

APP_NAME="Sistem Risalah KPKNL"
APP_ENV=local
APP_DEBUG=true

untuk local development.

Jangan gunakan APP_DEBUG=true pada production.

# =========================================================

# 8. MODEL YANG HARUS DIBUAT

# =========================================================

Buat minimal:

app/Models/User.php
app/Models/RisalahPending.php
app/Models/RisalahMinuta.php
app/Models/RisalahTap.php
app/Models/RisalahBatal.php
app/Models/RisalahRevisi.php
app/Models/Peminjaman.php

Jika `peminjaman_tap` masih benar-benar digunakan:

app/Models/PeminjamanTap.php

Pastikan masing-masing:

protected $table = 'nama_tabel';

Gunakan `$fillable` atau `$guarded` secara aman.

Jika tabel lama tidak memiliki:

created_at
updated_at

gunakan:

public $timestamps = false;

Jangan menganggap tabel memiliki timestamps sebelum memeriksa database.

Gunakan casts untuk:

* tanggal risalah
* tanggal validasi
* tanggal peminjaman
* tanggal pengembalian
* tanggal revisi

jika kompatibel dengan schema existing.

Jangan membuat foreign key yang dapat merusak database existing.

Perhatikan bahwa aplikasi lama banyak menyimpan:

nama_pelelang
nama_peminjam

sebagai STRING username, bukan foreign key.

Untuk tahap pertama pertahankan kompatibilitas ini.

Setelah aplikasi bekerja, boleh memberikan rekomendasi refactor menjadi `user_id`, tetapi JANGAN memaksakannya dalam migrasi pertama.

# =========================================================

# 9. AUTHENTICATION

# =========================================================

Ganti seluruh session PHP native menjadi Laravel Auth.

Buat:

app/Http/Controllers/Auth/AuthController.php

Method minimal:

showLogin()
login()
logout()
showRegister()
register()

Login harus dapat menggunakan:

USERNAME ATAU EMAIL

Sama seperti program lama.

Contoh behaviour:

input:
login = username/email
password = password

Cari user dengan:

username = input
ATAU
email = input

Kemudian verifikasi password menggunakan Laravel Hash/Auth.

Password lama dibuat menggunakan:

password_hash(..., PASSWORD_DEFAULT)

Pastikan kompatibel dengan password Laravel.

Jangan mengubah password existing tanpa kebutuhan.

Setelah login, redirect berdasarkan role:

admin:
→ admin.dashboard

pelelang:
→ pelelang.dashboard

peminjam:
→ peminjam.dashboard

Jika credential salah:
kembali ke login dengan validation error yang baik.

Gunakan session regeneration untuk mencegah session fixation.

Pada logout:

* Auth::logout()
* invalidate session
* regenerate CSRF token.

# =========================================================

# 10. REGISTER

# =========================================================

Migrasikan halaman:

register.html
register_admin.html
register_pelelang.html
register.php

Gunakan Laravel validation.

Minimal:

username:
required
string
unique users

email:
required
email
unique users

password:
required
confirmed
minimal panjang yang wajar

role:
harus masuk whitelist:

admin
pelelang
peminjam

JANGAN mempercayai nilai role mentah dari request.

CATATAN KEAMANAN:

Source lama memungkinkan role berasal dari form.

Audit apakah registrasi admin memang boleh publik.

Jika registrasi admin publik tidak diperlukan, buat desain lebih aman:

* public register hanya role yang diperbolehkan,
* pembuatan admin dilakukan admin existing.

Tetapi jangan mengubah behaviour aplikasi secara diam-diam.

Dokumentasikan keputusan.

# =========================================================

# 11. ROLE MIDDLEWARE

# =========================================================

Buat:

app/Http/Middleware/RoleMiddleware.php

Middleware harus mendukung role seperti:

role:admin
role:pelelang
role:peminjam

Jika role tidak sesuai:
abort(403)

atau redirect aman dengan pesan.

Daftarkan alias middleware menggunakan mekanisme Laravel versi yang digunakan.

Untuk Laravel modern gunakan konfigurasi middleware pada:

bootstrap/app.php

jika itu mekanisme yang sesuai dengan versi Laravel project.

Jangan menggunakan tutorial Laravel lama secara membabi buta.

# =========================================================

# 12. CONTROLLER

# =========================================================

Struktur minimal yang saya inginkan:

app/Http/Controllers/
├── Auth/
│   └── AuthController.php
├── DashboardController.php
├── RisalahController.php
├── ValidasiRisalahController.php
├── PeminjamanController.php
├── GrafikController.php
└── ExportController.php

Jika lebih rapi boleh pecah lagi menjadi controller khusus.

Jangan membuat satu controller berisi ribuan baris.

# =========================================================

# 13. DASHBOARD CONTROLLER

# =========================================================

Buat:

DashboardController

Method misalnya:

admin()
pelelang()
peminjam()

Admin dashboard harus mempertahankan data penting seperti jumlah:

risalah_pending

dan statistik lain yang sebelumnya digunakan.

Pelelang dashboard:
tampilkan menu/fungsi milik pelelang.

Peminjam dashboard:
tampilkan menu/fungsi milik peminjam.

# =========================================================

# 14. RISALAH CONTROLLER

# =========================================================

Migrasikan fungsi dari:

tambah_risalah.php
proses_tambah.php
cek_data_risalah.php
cek_data_tap.php
cek_data_batal.php
edit_risalah.php
edit_risalah_tap.php
edit_risalah_batal.php
riwayat_risalah.php

Buat desain controller yang bersih.

Minimal mendukung:

* melihat daftar Minuta
* melihat daftar TAP
* melihat daftar Batal
* filter
* pencarian
* filter tahun
* filter bulan
* filter status
* create risalah
* store risalah
* edit
* update
* history risalah pelelang
* export

Saat Pelelang menambahkan risalah:

simpan terlebih dahulu ke:

risalah_pending

dengan status:

belum_validasi

`nama_pelelang` harus berasal dari user login jika memungkinkan.

JANGAN mempercayai hidden input `nama_pelelang` dari browser.

Gunakan:

Auth::user()->username

# =========================================================

# 15. FORM REQUEST

# =========================================================

Buat Form Request agar controller tidak penuh validasi.

Minimal:

LoginRequest.php
RegisterRequest.php
StoreRisalahRequest.php
UpdateRisalahRequest.php
ValidateRisalahRequest.php
BorrowRisalahRequest.php
ReturnRisalahRequest.php

Tempat:

app/Http/Requests/

Gunakan validation rules sesuai field.

Untuk jenis risalah:

jenis harus whitelist:

minuta
tap
batal

case handling harus konsisten.

# =========================================================

# 16. ALUR VALIDASI RISALAH

# =========================================================

Business flow lama HARUS dipertahankan.

Alur:

PELELANG
↓
Tambah Risalah
↓
risalah_pending
↓
ADMIN VALIDASI

Jika jenis MINUTA:

risalah_pending
→ risalah_minuta

Jika jenis TAP:

risalah_pending
→ risalah_tap

Jika jenis BATAL:

risalah_pending
→ risalah_batal

Setelah transfer berhasil:
hapus record dari pending.

TETAPI proses transfer HARUS menggunakan:

DB::transaction()

Jangan lakukan insert kemudian delete tanpa transaction.

Gunakan row locking jika relevan.

Untuk Minuta:
box dan lemari wajib ketika validasi.

Untuk TAP:
box dan lemari wajib ketika validasi.

Untuk Batal:
ikuti behaviour database/source lama.

`link_erisalah` juga harus dipertahankan.

Buat:

ValidasiRisalahController

Method dapat berupa:

index()
show()
approve()
requestRevision()
revisionIndex()
showRevision()
resubmitRevision()

Nama method boleh berbeda jika desain lebih baik.

# =========================================================

# 17. REVISI RISALAH

# =========================================================

Jika Admin memilih REVISI:

data dari:

risalah_pending

dipindahkan ke:

risalah_revisi

Simpan catatan:

catatan_no
catatan_jenis
catatan_tgl
catatan_pelelang
catatan_pemohon

status:

dikembalikan

tanggal:

tgl_revisi

Setelah berhasil:
hapus dari pending.

Gunakan transaction.

Pelelang harus dapat melihat risalah revisinya sendiri.

Jangan menggunakan:

WHERE nama_pelelang = '$username'

dengan raw interpolation.

Gunakan Eloquent/Query Builder dan Auth user.

Pelelang dapat memperbaiki data.

Setelah dikirim kembali:

risalah_revisi
→ risalah_pending

Kemudian Admin dapat melakukan validasi ulang.

Semua transfer tersebut harus atomic menggunakan transaction.

# =========================================================

# 18. PEMINJAMAN RISALAH

# =========================================================

Migrasikan semua logic peminjaman.

User dengan role:

peminjam

dapat memilih risalah yang tersedia dari:

risalah_minuta
risalah_tap
risalah_batal

Peminjam dapat memilih lebih dari satu item.

Alasan peminjaman harus dipertahankan jika memang digunakan.

Ketika peminjaman dibuat:

1. Mulai DB transaction.

2. Lock risalah yang akan dipinjam.

Gunakan pola Eloquent/Query Builder setara:

lockForUpdate()

3. Pastikan status masih tersedia.

4. Ubah status risalah menjadi sedang dipinjam.

5. Insert ke tabel peminjaman.

Initial status existing:

Proses Peminjaman

6. Commit.

Jika salah satu proses gagal:
rollback semuanya.

Jangan sampai:
status risalah berubah tetapi record peminjaman gagal dibuat.

Source lama sudah mulai menggunakan transaction dan `FOR UPDATE`.

Pertahankan atau tingkatkan keamanan concurrency tersebut menggunakan Laravel.

# =========================================================

# 19. ALUR STATUS PEMINJAMAN

# =========================================================

Pertahankan flow:

Peminjam memilih risalah
↓
Proses Peminjaman
↓
Admin memvalidasi
↓
Menunggu Konfirmasi Peminjam
↓
Peminjam menerima
↓
Sedang Dipinjam
↓
Peminjam mengajukan pengembalian
↓
Proses Pengembalian
↓
Admin memvalidasi pengembalian
↓
Sudah Dikembalikan

Setelah pengembalian selesai:

status risalah sumber harus kembali menjadi:

Tersedia

Tetapi karena data lama menggunakan:

Tersedia
tersedia
sedang_dipinjam
Sedang Dipinjam

buat compatibility layer.

Jangan merusak data existing.

# =========================================================

# 20. IDENTIFIKASI JENIS RISALAH PADA PEMINJAMAN

# =========================================================

Source lama mencari `no_risalah` berurutan di:

risalah_batal
risalah_minuta
risalah_tap

untuk mengetahui tabel asal.

Ini bisa bermasalah jika `no_risalah` tidak unik lintas tabel.

Audit dulu.

Jangan langsung mengubah database.

Untuk versi Laravel awal:
pertahankan behaviour jika dibutuhkan.

Tetapi dokumentasikan solusi lebih baik, misalnya peminjaman memiliki:

risalah_type
risalah_id

atau konsep polymorphic relationship.

Implementasi perubahan schema tersebut hanya dilakukan jika:

* aman,
* tidak merusak data lama,
* dan memang diperlukan.

# =========================================================

# 21. SERVICE LAYER

# =========================================================

Logic transfer data jangan semuanya diletakkan di Controller.

Buat service jika membantu.

Saya menyarankan:

app/Services/RisalahValidationService.php
app/Services/PeminjamanService.php
app/Services/RisalahExportService.php

`RisalahValidationService` menangani:

* approve pending
* pindah ke Minuta/TAP/Batal
* request revisi
* resubmit revisi.

`PeminjamanService` menangani:

* borrow
* admin approve
* borrower confirm
* request return
* admin approve return.

Gunakan DB::transaction() pada business operation penting.

# =========================================================

# 22. ROUTES

# =========================================================

Gunakan:

routes/web.php

Buat route dengan nama yang jelas.

Contoh desain:

Guest:

GET  /login
POST /login
GET  /register
POST /register

Authenticated:

POST /logout

ADMIN:

/admin/dashboard
/admin/risalah/pending
/admin/risalah/{id}/validasi
/admin/peminjaman
/admin/peminjaman/{id}/approve
/admin/peminjaman/{id}/return/approve
/admin/grafik

PELELANG:

/pelelang/dashboard
/pelelang/risalah/create
/pelelang/risalah
/pelelang/revisi
/pelelang/riwayat

PEMINJAM:

/peminjam/dashboard
/peminjam/risalah
/peminjam/pinjaman
/peminjam/pinjaman/{id}/confirm
/peminjam/pinjaman/{id}/return

Nama route contoh:

login
register
logout

admin.dashboard
admin.risalah.pending
admin.risalah.validation.show
admin.risalah.approve
admin.risalah.revision
admin.peminjaman.index
admin.peminjaman.approve
admin.peminjaman.return.approve
admin.grafik.index

pelelang.dashboard
pelelang.risalah.create
pelelang.risalah.store
pelelang.risalah.history
pelelang.revision.index
pelelang.revision.resubmit

peminjam.dashboard
peminjam.risalah.index
peminjam.borrow.store
peminjam.borrow.index
peminjam.borrow.confirm
peminjam.borrow.return

Gunakan route model binding jika aman.

# =========================================================

# 23. JANGAN UBAH DATA DENGAN HTTP GET

# =========================================================

Source lama memiliki beberapa action seperti:

proses_validasi_pinjam.php?id=...
hapus_revisi.php?id=...
admin_kembalikan.php?id=...

yang melakukan perubahan data melalui GET.

Di Laravel JANGAN lakukan ini.

Gunakan:

POST
PATCH
PUT
DELETE

sesuai kebutuhan.

Semua form perubahan data wajib menggunakan:

@csrf

Untuk method:
@method('PATCH')
@method('DELETE')

jika diperlukan.

# =========================================================

# 24. BLADE VIEW

# =========================================================

Pindahkan seluruh HTML lama menjadi Blade.

Struktur yang saya sarankan:

resources/views/
├── layouts/
│   ├── app.blade.php
│   ├── admin.blade.php
│   ├── pelelang.blade.php
│   └── peminjam.blade.php
├── auth/
│   ├── login.blade.php
│   └── register.blade.php
├── admin/
│   └── dashboard.blade.php
├── pelelang/
│   └── dashboard.blade.php
├── peminjam/
│   └── dashboard.blade.php
├── risalah/
│   ├── minuta/
│   ├── tap/
│   ├── batal/
│   ├── pending/
│   └── revision/
├── peminjaman/
└── grafik/

Gunakan layout untuk menghindari copy-paste HTML.

Pindahkan:

* navbar
* menu
* header
* alert
* tombol kembali

menjadi reusable Blade component/layout jika masuk akal.

# =========================================================

# 25. TAMPILAN

# =========================================================

Jangan mengubah desain secara drastis.

Pertahankan:

* logo
* warna utama
* struktur menu
* tabel
* tombol
* dashboard

dari aplikasi PHP Native.

File:

logo.png
style_dashboard.css
style_pinjam.css

boleh dipindahkan ke:

public/images/
public/css/

atau resources jika menggunakan Vite.

Pastikan semua URL asset menggunakan helper Laravel seperti:

asset()

Jangan memakai relative path PHP lama.

# =========================================================

# 26. FLASH MESSAGE

# =========================================================

Ganti:

echo "<script>alert(...)"

dengan Laravel flash session.

Contoh:

return redirect()
->route(...)
->with('success', 'Risalah berhasil ditambahkan.');

Buat component Blade alert yang mendukung:

success
error
warning
info

Validation errors harus tampil dengan baik.

# =========================================================

# 27. FILTER DATA

# =========================================================

Fitur di:

cek_data_risalah.php
cek_data_tap.php
cek_data_batal.php

harus dipertahankan.

Filter termasuk jika tersedia:

* nomor risalah
* pemohon
* pelelang
* box
* lemari
* tahun
* bulan awal
* bulan akhir
* status.

Gunakan query builder/Eloquent secara conditional.

Contoh konsep:

$query->when($request->filled(...), ...)

Jangan concatenate SQL.

Tambahkan pagination.

Pertahankan query string saat pagination.

# =========================================================

# 28. EXPORT EXCEL

# =========================================================

Source lama melakukan export dengan:

Content-Type: application/vnd.ms-excel

dan mengirim HTML table sebagai `.xls`.

Untuk tahap migrasi:

buat export yang menghasilkan file yang benar dan aman.

Pilihan:

A. Gunakan CSV native Laravel/PHP jika tidak ingin dependency.

ATAU

B. gunakan package Laravel Excel jika kompatibel dan benar-benar diperlukan.

Jika menggunakan package tambahan:

* cek kompatibilitas Laravel,
* jangan install package usang.

Filter export harus mengikuti filter yang sedang aktif.

Contoh:
jika user filter tahun dan status,
hasil export harus sama dengan data yang terlihat.

# =========================================================

# 29. GRAFIK

# =========================================================

Pertahankan semua fungsi grafik lama:

* Grafik Bulanan Pelelangan
* Grafik Tahunan Pelelangan
* Grafik Bulanan Peminjaman
* Grafik Tahunan Peminjaman
* Grafik Pelelang
* Grafik Peminjam

Gunakan:

GrafikController

dan Chart.js.

Jangan menjalankan query database dari view.

Semua agregasi data dilakukan:

* Controller
  atau
* Service/query class.

Blade hanya menerima hasilnya.

Jika AJAX digunakan untuk update grafik:
buat endpoint Laravel yang aman dan return JSON.

# =========================================================

# 30. SECURITY

# =========================================================

Perbaiki security problem dari PHP Native.

WAJIB:

1. Tidak ada SQL seperti:

"SELECT * FROM users WHERE username='$username'"

2. Gunakan Eloquent/Query Builder.

3. Semua form memiliki CSRF.

4. Authorization berdasarkan role dilakukan server-side.

5. Jangan mempercayai hidden field seperti:
   nama_pelelang
   nama_peminjam
   role

tanpa validasi.

6. Data user login ambil dari:

Auth::user()

7. Escape output pada Blade menggunakan:

{{ }}

Hanya gunakan `{!! !!}` jika benar-benar aman.

8. Validation menggunakan Form Request.

9. Password menggunakan Hash/Auth Laravel.

10. Regenerate session sesudah login.

11. Invalidate session saat logout.

12. Action perubahan data tidak menggunakan GET.

13. Cegah mass assignment.

14. Gunakan database transaction pada proses multi-table.

15. Hindari dynamic table name dari request.

# =========================================================

# 31. BUSINESS STATUS

# =========================================================

Jangan menyebarkan string status di banyak file.

Buat central status definitions.

Jika versi PHP mendukung enum dengan baik, dapat menggunakan Enum.

Contoh konsep:

RisalahStatus:
AVAILABLE
BORROWED

LoanStatus:
PROCESSING
WAITING_BORROWER_CONFIRMATION
BORROWED
RETURN_PROCESSING
RETURNED

Tetapi karena database lama memiliki string existing, mapping ke database harus kompatibel.

Jangan otomatis mengubah semua data tanpa migration plan.

# =========================================================

# 32. DATABASE TRANSACTION

# =========================================================

WAJIB gunakan transaksi pada:

1. validasi pending → minuta/tap/batal
2. pending → revisi
3. revisi → pending
4. peminjaman risalah
5. validasi pengembalian
6. operasi lain yang mengubah lebih dari satu tabel.

Gunakan:

DB::transaction(function () {
...
});

Untuk peminjaman concurrency gunakan:

lockForUpdate()

agar dua peminjam tidak dapat meminjam risalah yang sama pada saat bersamaan.

# =========================================================

# 33. DATABASE MIGRATIONS

# =========================================================

Saya tetap ingin migration tersedia agar suatu saat database dapat dibuat dari nol.

Tetapi:

JANGAN jalankan migration tersebut ke DB lama jika akan menyebabkan conflict.

Lakukan:

1. Audit DB existing.
2. Dokumentasikan schema.
3. Buat migration yang merepresentasikan schema.
4. Uji migration menggunakan DATABASE TEST/KOSONG.
5. Jangan menghapus database existing.

Migration harus mencakup semua tabel aplikasi yang benar-benar digunakan.

Jika terdapat perbedaan antara source PHP dan database aktual:
database aktual menjadi sumber utama selama tidak terbukti sebagai bug.

Dokumentasikan perbedaan tersebut.

# =========================================================

# 34. SEEDER

# =========================================================

Buat seeder development jika dibutuhkan.

Misalnya:

AdminUserSeeder

Tetapi:

* jangan overwrite user existing,
* jangan menyimpan password produksi di source.

Seeder harus menggunakan Hash::make().

Boleh gunakan:

firstOrCreate()

agar tidak menggandakan user.

# =========================================================

# 35. TESTING

# =========================================================

Buat Feature Tests.

Minimal:

AuthTest
RoleAuthorizationTest
RisalahTest
RisalahValidationTest
RisalahRevisionTest
PeminjamanTest
ReturnPeminjamanTest

Test berikut:

## Authentication

* username login berhasil
* email login berhasil
* password salah ditolak
* logout bekerja.

## Authorization

* admin dapat akses admin
* pelelang tidak bisa akses admin
* peminjam tidak bisa akses admin
* admin tidak otomatis diberi halaman pelelang jika tidak perlu.

## Risalah

* pelelang membuat risalah
* masuk ke pending
* nama pelelang berasal dari authenticated user.

## Validasi

* admin approve minuta
* pending hilang setelah insert berhasil
* minuta tersimpan.

Lakukan juga TAP dan Batal.

## Revisi

* admin mengembalikan data
* masuk risalah_revisi
* pelelang dapat melihat revisinya
* pelelang kirim kembali
* data kembali ke pending.

## Peminjaman

* peminjam dapat pinjam risalah tersedia
* risalah tidak dapat dipinjam dua user bersamaan
* record peminjaman tercipta
* status berubah sesuai flow.

## Pengembalian

* request pengembalian
* admin approve
* peminjaman menjadi Sudah Dikembalikan
* risalah kembali tersedia.

Gunakan database testing TERPISAH.

Jangan menjalankan destructive test ke database `kpknl` utama.

# =========================================================

# 36. QUALITY CHECK

# =========================================================

Setelah coding jalankan minimal:

php artisan about

php artisan route:list

php artisan optimize:clear

php artisan test

Jika frontend assets menggunakan Vite:

npm install
npm run build

Cek error Laravel:

storage/logs/laravel.log

Pastikan tidak ada:

* syntax error
* missing route
* missing view
* namespace error
* undefined variable
* mass assignment exception.

# =========================================================

# 37. STRUKTUR TARGET

# =========================================================

Target struktur kurang lebih:

app/
├── Http/
│   ├── Controllers/
│   │   ├── Auth/
│   │   │   └── AuthController.php
│   │   ├── DashboardController.php
│   │   ├── RisalahController.php
│   │   ├── ValidasiRisalahController.php
│   │   ├── PeminjamanController.php
│   │   ├── GrafikController.php
│   │   └── ExportController.php
│   ├── Middleware/
│   │   └── RoleMiddleware.php
│   └── Requests/
│       ├── LoginRequest.php
│       ├── RegisterRequest.php
│       ├── StoreRisalahRequest.php
│       ├── UpdateRisalahRequest.php
│       ├── ValidateRisalahRequest.php
│       ├── BorrowRisalahRequest.php
│       └── ReturnRisalahRequest.php
├── Models/
│   ├── User.php
│   ├── RisalahPending.php
│   ├── RisalahMinuta.php
│   ├── RisalahTap.php
│   ├── RisalahBatal.php
│   ├── RisalahRevisi.php
│   └── Peminjaman.php
└── Services/
├── RisalahValidationService.php
├── PeminjamanService.php
└── RisalahExportService.php

database/
├── migrations/
├── seeders/
└── factories/

resources/
└── views/
├── layouts/
├── components/
├── auth/
├── admin/
├── pelelang/
├── peminjam/
├── risalah/
├── peminjaman/
└── grafik/

routes/
└── web.php

public/
├── css/
├── js/
└── images/

tests/
└── Feature/

docs/
├── MIGRATION_AUDIT.md
└── MIGRATION_REPORT.md

# =========================================================

# 38. URUTAN PENGERJAAN

# =========================================================

KERJAKAN BERURUTAN.

PHASE 0 — BACKUP / AUDIT

* jangan ubah project PHP lama
* analisis semua file
* analisis database
* buat MIGRATION_AUDIT.md.

PHASE 1 — LARAVEL SETUP

* cek PHP/Composer
* buat Laravel application
* `.env`
* app key
* koneksi database
* pastikan Laravel dapat membaca DB lama.

PHASE 2 — DATABASE

* audit schema
* Models
* casts
* fillable
* migration untuk fresh installation
* seeder development jika perlu.

PHASE 3 — AUTH

* login
* username/email
* register
* logout
* role middleware.

PHASE 4 — DASHBOARD

* admin
* pelelang
* peminjam.

PHASE 5 — RISALAH

* listing
* create
* store
* edit
* update
* filter
* history.

PHASE 6 — VALIDASI

* pending
* approve
* minuta
* tap
* batal
* revision
* resubmit.

PHASE 7 — PEMINJAMAN

* pilih risalah
* alasan
* transaction
* admin approve
* konfirmasi
* return
* admin return verification.

PHASE 8 — EXPORT & GRAFIK

* export
* semua Chart.js.

PHASE 9 — UI

* pindahkan CSS
* logo
* layouts
* alert
* responsive improvement ringan.

PHASE 10 — TESTING

* Feature Tests
* route:list
* artisan test
* build assets.

PHASE 11 — CLEANUP

* hapus duplikasi pada project Laravel saja
* jangan hapus PHP Native lama
* buat MIGRATION_REPORT.md.

# =========================================================

# 39. MIGRATION REPORT

# =========================================================

Setelah selesai buat:

docs/MIGRATION_REPORT.md

Isi:

1. Laravel version
2. PHP version
3. Database connection
4. Daftar tabel
5. Daftar Model
6. Daftar Controller
7. Daftar Service
8. Daftar Middleware
9. Daftar Routes
10. Daftar Blade views
11. Daftar test
12. Bug PHP Native yang diperbaiki
13. Perubahan security
14. Perbedaan behaviour jika ada
15. File legacy yang belum termigrasi jika ada
16. Langkah menjalankan aplikasi
17. Langkah setup database baru
18. Langkah menggunakan database existing.

# =========================================================

# 40. README

# =========================================================

Buat README.md yang jelas.

Minimal jelaskan:

Requirements

Installation

composer install

copy .env.example .env

php artisan key:generate

Konfigurasi MySQL

npm install
npm run build

php artisan serve

Jangan menyuruh user menjalankan `migrate:fresh` pada database existing.

Jelaskan dua skenario:

A. Menggunakan database `kpknl` existing.

B. Membuat database kosong baru menggunakan migrations.

# =========================================================

# 41. ACCEPTANCE CRITERIA

# =========================================================

Migrasi dianggap SELESAI hanya jika:

[ ] Laravel dapat boot tanpa error.

[ ] Database `kpknl` dapat terkoneksi.

[ ] User lama dapat login jika password compatible.

[ ] Login bisa username atau email.

[ ] Role admin bekerja.

[ ] Role pelelang bekerja.

[ ] Role peminjam bekerja.

[ ] Unauthorized role tidak dapat membuka halaman role lain.

[ ] Register bekerja.

[ ] Logout bekerja.

[ ] Pelelang dapat menambahkan risalah.

[ ] Risalah masuk pending.

[ ] Admin dapat melihat pending.

[ ] Admin dapat validasi Minuta.

[ ] Admin dapat validasi TAP.

[ ] Admin dapat validasi Batal.

[ ] Admin dapat mengembalikan risalah untuk revisi.

[ ] Pelelang dapat melihat revisi.

[ ] Pelelang dapat mengirim revisi kembali.

[ ] Listing Minuta bekerja.

[ ] Listing TAP bekerja.

[ ] Listing Batal bekerja.

[ ] Edit risalah bekerja.

[ ] Filter bekerja.

[ ] Riwayat pelelang bekerja.

[ ] Export bekerja.

[ ] Peminjam dapat memilih risalah.

[ ] Multiple selection bekerja.

[ ] Transaction peminjaman aman.

[ ] Risalah yang sedang dipinjam tidak dapat dipinjam ulang.

[ ] Admin dapat memvalidasi peminjaman.

[ ] Peminjam dapat konfirmasi.

[ ] Peminjam dapat request pengembalian.

[ ] Admin dapat menyelesaikan pengembalian.

[ ] Status risalah kembali tersedia.

[ ] Grafik bulanan pelelangan bekerja.

[ ] Grafik tahunan pelelangan bekerja.

[ ] Grafik bulanan peminjaman bekerja.

[ ] Grafik tahunan peminjaman bekerja.

[ ] Grafik pelelang bekerja.

[ ] Grafik peminjam bekerja.

[ ] CSRF aktif.

[ ] Tidak ada SQL interpolation raw yang berbahaya.

[ ] Tidak ada perubahan data melalui GET.

[ ] `php artisan route:list` berhasil.

[ ] `php artisan test` berhasil atau kegagalan terdokumentasi jelas.

# =========================================================

# 42. CARA ANDA BEKERJA

# =========================================================

Jangan hanya menjawab:

"Berikut langkah-langkahnya..."

Saya ingin Anda BENAR-BENAR MENGEDIT DAN MEMBUAT FILE.

Saat mengerjakan:

1. Audit dulu.
2. Implementasikan.
3. Jalankan command/testing.
4. Perbaiki error yang ditemukan.
5. Lanjut sampai aplikasi semaksimal mungkin selesai.

Tidak perlu meminta konfirmasi untuk setiap file.

Jika ada keputusan kecil, gunakan best practice Laravel.

Jika menemukan ambiguity:

* periksa source lama,
* periksa database,
* pilih behaviour yang paling mempertahankan aplikasi existing,
* dokumentasikan keputusan.

Jika ada bagian yang tidak dapat diselesaikan karena environment/database tidak tersedia:
jangan mengarang hasil.

Tetap buat kode yang memungkinkan, kemudian tuliskan secara jelas apa yang belum dapat diverifikasi.

# =========================================================

# 43. PRIORITAS UTAMA

# =========================================================

Urutan prioritas:

1. JANGAN HILANGKAN DATA.
2. JANGAN HAPUS PROJECT LAMA.
3. PERTAHANKAN BUSINESS FLOW.
4. PERBAIKI SECURITY.
5. PAKAI STRUKTUR LARAVEL YANG BENAR.
6. KURANGI DUPLIKASI.
7. PASTIKAN MUDAH DIKEMBANGKAN.
8. TEST SEMUA FITUR UTAMA.

Mulai sekarang dengan membaca seluruh project PHP Native yang tersedia di workspace.

Setelah audit, langsung mulai migrasi ke Laravel tanpa berhenti hanya pada penjelasan.

Target akhir saya adalah mendapatkan **project Laravel yang benar-benar dapat dijalankan**, bukan sekadar contoh atau tutorial.

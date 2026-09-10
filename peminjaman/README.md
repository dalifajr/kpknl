# Sistem Pengelolaan Risalah Lelang & Peminjaman (Laravel 11) - KPKNL

Aplikasi pengelolaan berkas risalah lelang (Minuta, TAP, Batal), verifikasi administrasi, peminjaman multi-item dengan pessimistic locking, serta visualisasi grafik statistik berbasis Laravel 11.

---

## 🚀 Persyaratan Sistem
- **PHP**: >= 8.2 (ekstensi `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `sqlite3` untuk test)
- **Database**: MySQL >= 5.7 atau MariaDB >= 10.3 (Database `kpknl`)
- **Composer**: >= 2.x
- **Web Server**: Apache / Nginx / PHP Built-in Server (`php artisan serve`)

---

## 📦 Panduan Instalasi & Menjalankan Aplikasi

### 1. Masuk ke Direktori Project Laravel
```bash
cd laravel
```

### 2. Salin Konfigurasi Environment
```bash
copy .env.example .env
```

### 3. Generate Application Key
```bash
php artisan key:generate
```

### 4. Konfigurasi Database
Buka file `.env` dan pastikan konfigurasi database sesuai dengan server MySQL lokal Anda:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kpknl
DB_USERNAME=root
DB_PASSWORD=
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci
```

---

## 🗄️ Opsi Database

### Skenario A: Menggunakan Database MySQL `kpknl` Existing (Rekomendasi)
> **PENTING:** Jangan jalankan `php artisan migrate:fresh` pada database existing agar data riwayat tidak terhapus.
1. Pastikan database `kpknl` sudah aktif di MySQL / XAMPP.
2. Langsung jalankan server (Langkah 5).

### Skenario B: Menggunakan Database Kosong Baru dari Nol
1. Buat database baru di MySQL (contoh: `kpknl_baru`).
2. Ubah `DB_DATABASE=kpknl_baru` di file `.env`.
3. Jalankan migration dan seeder bawaan:
   ```bash
   php artisan migrate
   php artisan db:seed
   ```
   *Akun bawaan development:*
   - **Admin**: `admin` / `admin123`
   - **Pelelang**: `pelelang1` / `pelelang123`
   - **Peminjam**: `peminjam1` / `peminjam123`

---

## 💻 Menjalankan Server Lokal

```bash
php artisan serve
```
Buka browser Anda dan akses: [http://127.0.0.1:8000](http://127.0.0.1:8000).

---

## 🧪 Menjalankan Automated Testing

Pengujian dilakukan menggunakan database SQLite `:memory:` terisolasi sehingga tidak mempengaruhi data pada MySQL lokal Anda:
```bash
php artisan test
```

---

## 📚 Dokumentasi Terkait
- [Dokumen Audit Migrasi](docs/MIGRATION_AUDIT.md)
- [Laporan Lengkap Migrasi](docs/MIGRATION_REPORT.md)

# WALKTHROUGH PERENCANAAN SISTEM PEMELIHARAAN TERPUSAT (SSO ORCHESTRATOR)
## PANDUAN IMPLEMENTASI OPERASIONAL UPDATING & DOWNGRADING MULTI-APLIKASI
**Portal Single Sign-On (SSO) &bull; KPKNL Palembang &bull; Kementerian Keuangan RI**

---

### 1. RINGKASAN DESAIN SISTEM

Modul **Central Application Maintenance Orchestrator** dirancang untuk diintegrasikan secara elegan ke dalam portal **Single Sign-On (SSO) KPKNL Palembang** pada menu Superadmin: `admin/maintenance-orchestrator`.

Modul ini memberikan kendali penuh kepada Superadmin untuk memantau status kesehatan, menjalankan pembaruan rilis (*updating*), maupun melakukan pemulihan darurat (*downgrading/rollback*) terhadap 4 aplikasi yang terhubung:
1. **Sistem Informasi Peminjaman Risalah Lelang** (`peminjaman`)
2. **Manajemen Aset Eks BPPN** (`aset-bppn`)
3. **Dashboard Eksekutif Pengelolaan BMN** (`Dashboard Pengelolaan BMN`)
4. **Sistem Monitoring TODO-LIST & Pelaporan** (`monlap`)

---

### 2. CETAK BIRU ANTARMUKA PENGGUNA (UI MOCKUP)

Berikut adalah struktur tata letak antarmuka yang dirancang pada portal SSO:

```
+───────────────────────────────────────────────────────────────────────────────────────────────────────────+
| [LOGO KPKNL]  PORTAL SSO KPKNL PALEMBANG                     [🔔 0]  [Superadmin Seksi HI ▼] [Logout]     |
+───────────────────────────────────────────────────────────────────────────────────────────────────────────+
| 📊 Dashboard    |  PUSAT KENDALI PEMELIHARAAN MULTI-APLIKASI (MAINTENANCE CONTROL PLANE)                 |
| 👥 Manajemen User|  Kendali terpusat pembaruan kode, migrasi database, dan pemulihan darurat aplikasi       |
| 🏢 Aplikasi Terdaftar                                                                                     |
| 🛠️ Maintenance  |  +-----------------------------------------------------------------------------------+  |
| 💾 Backup & Restore|  | MATRIKS STATUS APLIKASI (REAL-TIME HEALTH & VERSION MONITORING)                    |  |
| ⚙️ Pengaturan   |  +---------------------+-------------------+---------------------+-----------------+  |
|                 |  | 1. PEMINJAMAN LELANG | 2. ASET EKS BPPN  | 3. DASHBOARD BMN    | 4. MONLAP TODO  |  |
|                 |  | Versi: v2.1.0 (7fa9)| Versi: v1.4.2     | Versi: v2.0.1       | Versi: v1.2.0   |  |
|                 |  | Status: [● NORMAL]  | Status: [● NORMAL]| Status: [● NORMAL]  | Status: [● DOWN]|  |
|                 |  | Health: 42ms (OK)   | Health: 38ms (OK) | Health: 55ms (OK)   | Health: - (Down)|  |
|                 |  | [Deploy Update]     | [Deploy Update]   | [Deploy Update]     | [Deploy Update] |  |
|                 |  | [Rollback Downgrade]| [Rollback Downgrade]| [Rollback Downgrade]| [Rollback Downgrade]|
|                 |  | [Set Maintenance]   | [Set Maintenance] | [Set Maintenance]   | [Exit Mnt (UP)] |  |
|                 |  +---------------------+-------------------+---------------------+-----------------+  |
|                 |                                                                                         |
|                 |  +-----------------------------------------------------------------------------------+  |
|                 |  | KONSOL TERMINAL EKSEKUSI PIPELINE REAL-TIME (LIVE STREAMING TERMINAL OUTPUT)     |  |
|                 |  +-----------------------------------------------------------------------------------+  |
|                 |  | [16:30:02] [INFO] Memulai Preflight Check untuk aplikasi 'peminjaman'... [OK]     |  |
|                 |  | [16:30:03] [DB]   Membuat snapshot MySQL database 'kpknl' -> snapshot_20260910.sql |  |
|                 |  | [16:30:06] [DOWN] Mengaktifkan mode pemeliharaan via artisan down (Secret Generated)|  |
|                 |  | [16:30:07] [GIT]  Sinkronisasi commit 8b4c91a pada folder 'peminjaman/'... [OK]    |  |
|                 |  | [16:30:09] [COMP] Memperbarui dependensi vendor (composer install --no-dev)... [OK]  |  |
|                 |  | [16:30:12] [MIGR] Menjalankan php artisan migrate --force (0 pending migrations)   |  |
|                 |  | [16:30:13] [OPT]  Membersihkan & menyusun cache Laravel (optimize:clear)... [OK]   |  |
|                 |  | [16:30:14] [TEST] Internal Smoke Test GET http://localhost/peminjaman/public -> 200|  |
|                 |  | [16:30:15] [UP]   Menonaktifkan mode pemeliharaan via artisan up... SUKSES!        |  |
|                 |  +-----------------------------------------------------------------------------------+  |
+─────────────────┴─────────────────────────────────────────────────────────────────────────────────────────+
```

---

### 3. PANDUAN OPERASIONAL (STEP-BY-STEP OPERATOR PLAYBOOK)

#### 3.1 Prosedur Melakukan Pembaruan (Updating / Deploying)
1. **Langkah 1:** Superadmin login ke Portal SSO KPKNL Palembang dan membuka menu `admin/maintenance-orchestrator`.
2. **Langkah 2:** Pada kartu aplikasi target (misal: *Sistem Informasi Peminjaman Risalah Lelang*), klik tombol **[Deploy Update]**.
3. **Langkah 3:** Modal dialog akan muncul menampilkan:
   - Versi saat ini vs Daftar Git Tag / Commit rilis terbaru yang tersedia di repositori.
   - Ringkasan changelog dan daftar file yang berubah.
4. **Langkah 4:** Masukkan **Sudo Password Akun Superadmin** untuk otorisasi keamanan tingkat tinggi.
5. **Langkah 5:** Tekan tombol **[Eksekusi Pipeline Pembaruan]**.
6. **Langkah 6:** Layar terminal interaktif akan menampilkan proses berurutan:
   - Pembuatan file dump snapshot database MySQL secara otomatis.
   - Penutupan sementara aplikasi ke mode *maintenance* (`artisan down`).
   - Penarikan file kode baru yang terisolasi khusus folder aplikasi terkait.
   - Pengecekan paket Composer dan migrasi database (`migrate --force`).
   - Pembersihan dan pemanasan cache Laravel.
   - Pengujian asap (*smoke test*).
7. **Langkah 7:** Aplikasi otomatis aktif kembali (`artisan up`) dan versi baru tercatat resmi di database SSO.

#### 3.2 Prosedur Melakukan Pemulihan Darurat (Downgrading / Rollback)
1. **Langkah 1:** Jika pembaruan yang baru saja dirilis terdeteksi menimbulkan kendala operasional, Superadmin membuka menu `admin/maintenance-orchestrator`.
2. **Langkah 2:** Klik tombol **[Rollback Downgrade]** pada aplikasi terkait.
3. **Langkah 3:** Pilih versi rilis stabil sebelumnya dari riwayat rilis (misal dari `v2.2.0` mundur ke `v2.1.0`).
4. **Langkah 4:** Pilih opsi pemulihan basis data:
   - *Opsi 1 (Recommended):* Restore otomatis dari file snapshot SQL yang dibuat sesaat sebelum update tadi.
   - *Opsi 2:* Eksekusi `php artisan migrate:rollback` untuk memundurkan migrasi terakhir.
5. **Langkah 5:** Masukkan Sudo Password Superadmin dan konfirmasi tindakan darurat.
6. **Langkah 6:** Sistem mengeksekusi *downgrade protocol*, memulihkan database, mengembalikan kode ke commit stabil lama, membersihkan cache, dan menyalakan kembali aplikasi dalam hitungan detik.

---

### 4. RENCANA PENGUJIAN & MITIGASI RISIKO (ZERO DOWNTIME & ZERO DATA LOSS)

| Potensi Kegagalan | Dampak | Mekanisme Mitigasi Otomatis Orchestrator |
| :--- | :--- | :--- |
| **Koneksi Database Putus saat Migrasi** | Script PHP mati di tengah jalan | **Auto-Rollback Trigger**: Sistem langsung merestore file `.sql` snapshot dan mengembalikan commit lama. |
| **Tumbukan Monorepo di `d:\laragon\www`** | Aplikasi lain ikut berubah versi | **Path-Isolated Execution**: Git checkout hanya diarahkan ke path subdirektori (`git checkout <tag> -- peminjaman/`). |
| **User Mengakses saat Setengah Update** | User melihat halaman error 500 | **Artisan Down dengan Secret**: Aplikasi dikunci dengan pesan ramah, hanya pipeline SSO yang dapat mengakses via bypass key. |
| **Class Not Found pada Vendor** | Layanan lumpuh total | **Automatic Composer Runner**: Pipeline otomatis menjalankan `composer install --no-dev` sebelum aplikasi dihidupkan. |

---

### 5. BERKAS DELIVERABLES PERENCANAAN

1. **[ANALISIS_KRITIK_DAN_SARAN_MAINTENANCE_SSO.md](file:///d:/laragon/www/ANALISIS_KRITIK_DAN_SARAN_MAINTENANCE_SSO.md)** — Menjawab tuntas pertanyaan "Apakah command git cukup?", 7 jebakan fatal, analisis arsitektur, dan evaluasi matriks.
2. **[DOKUMENTASI_ARSITEKTUR_MAINTENANCE_SSO_DAN_APLIKASI.md](file:///d:/laragon/www/DOKUMENTASI_ARSITEKTUR_MAINTENANCE_SSO_DAN_APLIKASI.md)** — Analisis aktor, relasi, RACI, skema basis data ERD, Use Case, BPMN 2.0, dan seluruh spesifikasi sintaks siap pakai (Mermaid, PlantUML, Draw.io XML).
3. **[WALKTHROUGH_PERENCANAAN_MAINTENANCE_SSO.md](file:///d:/laragon/www/WALKTHROUGH_PERENCANAAN_MAINTENANCE_SSO.md)** — Cetak biru mockup antarmuka, SOP operator langkah demi langkah, dan rencana mitigasi risiko.
4. **Repositori Diagram Vektor SVG:**
   - [erd_maintenance_sso.svg](file:///d:/laragon/www/diagram_assets/erd_maintenance_sso.svg)
   - [usecase_maintenance_sso.svg](file:///d:/laragon/www/diagram_assets/usecase_maintenance_sso.svg)
   - [bpmn_maintenance_sso.svg](file:///d:/laragon/www/diagram_assets/bpmn_maintenance_sso.svg)

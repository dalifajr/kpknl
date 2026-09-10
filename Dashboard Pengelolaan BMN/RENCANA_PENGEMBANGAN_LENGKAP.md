# RENCANA PENGEMBANGAN APLIKASI DASHBOARD EKSEKUTIF PENGELOLAAN BMN
### KPKNL Palembang — Kantor Wilayah DJKN Sumatera Selatan, Jambi, dan Bangka Belitung

---

## 1. PENDAHULUAN & LATAR BELAKANG
Pengelolaan Barang Milik Negara (BMN) yang optimal merupakan salah satu pilar utama dalam akuntabilitas dan produktivitas kekayaan negara. Sesuai dengan **PMK Nomor 120 Tahun 2024** (serta regulasi turunan DJKN), aset tanah dan bangunan yang terindikasi tidak digunakan sesuai tugas dan fungsi (*idle*) wajib diidentifikasi, diklarifikasi, diteliti, dan ditindaklanjuti secara terstruktur.

Saat ini data pengelolaan BMN terindikasi idle dan eks BMN idle dikelola melalui Google Spreadsheet daring. Agar para pengambil keputusan (Kepala Kantor KPKNL Palembang, Kepala Seksi PKN, Kepala Kanwil DJKN SJB, serta tim pengelola BMN) dapat memantau capaian kinerja dan mengambil keputusan strategis secara cepat, akurat, dan komprehensif, dibutuhkan **Aplikasi Executive Dashboard Pengelolaan BMN Terindikasi Idle dan Eks BMN Idle**.

---

## 2. HASIL RISET MENDALAM TERHADAP PROYEK & SISTEM SERUPA
Berdasarkan riset mendalam terhadap *Executive Dashboard* DJKN Kemenkeu, sistem SIMAN (Sistem Informasi Manajemen Aset Negara), serta praktik terbaik (*best practices*) arsitektur *Executive Information System (EIS)*:

1. **Prinsip *Executive-First* (3 Detik Pemahaman)**:
   - Pimpinan eksekutif membutuhkan informasi tingkat tinggi (*high-level summary metrics*) yang dapat dipahami dalam 3-5 detik: Total Nilai Aset Berpotensi Idle, Persentase Capaian Klarifikasi, dan Jumlah Aset Eks Idle yang telah terselesaikan (PSP/Penghapusan/Pemanfaatan).
   - Menyediakan kemampuan *drill-down* dari level Kanwil -> KPKNL -> Kementerian/Lembaga -> Unit Eselon I -> Satker hingga ke level NUP/Fisik Aset.

2. **Mitigasi Masalah Teknis Formula Spreadsheet (`IMPORTRANGE`)**:
   - Kolom data pada spreadsheet induk yang menggunakan formula `=IMPORTRANGE()` dari berkas spreadsheet lain rentan menghasilkan nilai `#REF!`, `#ERROR!`, atau `#N/A` jika terjadi *session timeout*, izin akses (*permission denied*), atau jaringan tidak stabil.
   - Solusi *Best Practice*: Aplikasi backend bertindak sebagai *Smart Data Extraction & Pipeline Engine* yang membaca nilai hasil evaluasi komputasi (*computed cached values*) dan menyimpannya secara terstruktur ke dalam database relasional lokal (MySQL), sehingga dashboard tetap dapat diakses 100% cepat, interaktif, dan *offline-ready*.

3. **Integrasi Single Sign-On (SSO)**:
   - Dashboard eksekutif diintegrasikan langsung dengan **SSO KPKNL Palembang** (`sso-kpknl-palembang`) melalui protokol OAuth2 / Authorization Code Flow, menjamin keamanan autentikasi terpusat dan manajemen hak akses pengguna.

---

## 3. HASIL BRAINSTORMING, KRITIK, DAN SARAN PENGEMBANGAN

### A. Kritik Terhadap Pola Penggunaan Spreadsheet Manual
1. **Rentan Salah Tafsir & Human Error**: Spreadsheet dengan puluhan kolom sulit dibaca di perangkat mobile/tablet saat pimpinan sedang melakukan perjalanan dinas atau rapat pimpinan.
2. **Tidak Ada Jejak Riwayat (*Timeline & Audit Trail*)**: Perubahan status tindak lanjut aset dari *Penelitian* menjadi *Surat Pemberitahuan Bukan BMN Idle* atau *Penetapan BMN Idle* sulit dilacak historis perubahannya.
3. **Keterbatasan Visual Spasial**: Spreadsheet tidak dapat menampilkan lokasi sebaran geografis aset tanah dan bangunan secara visual di peta.

### B. Saran & Fitur Unggulan Tambahan (*Value-Added Features*)
1. **Multi-Tab Dashboard Eksekutif Sesuai Referensi Gambar Paparan PPT**:
   - **Tab 1: Dashboard BMN Eks BMN Idle** (Metrik Saldo Masuk 2013-2026 vs Saldo Per 2026, Horizontal Bar Usulan Pengelolaan, Donut Chart Tindak Lanjut per KPKNL).
   - **Tab 2: Dashboard BMN Terindikasi Idle** (Metrik Populasi 256 NUP, Status Respon Jawaban, Donut Komposisi Tahap Klarifikasi: Pemantauan, Penelusuran, Penelitian, Vertical Bar Sebaran per KPKNL).
   - **Tab 3: Matriks Capaian per KPKNL** (Tabel matriks sebaran capaian klarifikasi dan tahapan tindak lanjut per KPKNL se-Kanwil SJB dengan ringkasan target).
   - **Tab 4: Pemetaan BMN Terindikasi Idle Lingkup Kemenkeu** (Breakdown khusus Kemenkeu per Unit Eselon I: DJP, DJBC, DJPb, DJKN; Breakdown per jenis barang: Rumah Negara, Gedung Kantor, Tanah; serta Catatan Dinamis).
   - **Tab 5: Potensi Menjadi BMN Idle Lingkup Kemenkeu** (Metrik 19 NUP, 24% populasi, Nilai Perolehan Rp154,25 Miliar + Tabel Detail Aset Interaktif dengan pencarian dan filter cepat).
2. **Fitur Peta Interaktif Sebaran Aset (GIS Map View)**:
   - Menampilkan visualisasi peta spasial sebaran aset tanah/bangunan di wilayah Sumatera Selatan (Palembang, Prabumulih, Ogan Ilir, Banyuasin, OKU, Lahat, dll.) menggunakan Leaflet.js dengan pin status berwarna (Hijau = PSP/Pemantauan, Kuning = Penelusuran, Merah = Potensi Idle).
3. **Executive Presentation Mode & TV Wall Display**:
   - Mode layar penuh (*Full Screen Presentation Mode*) dengan auto-cycle tab / slideshow untuk display ruang rapat pimpinan atau lobby KPKNL Palembang.
4. **Ekspor 1-Klik Siap Paparan (PDF & PPTX Template DJKN)**:
   - Tombol ekspor instan seluruh grafik, tabel matriks, dan ringkasan eksekutif ke dalam format PDF/PowerPoint siap presentasi.
5. **Realtime Smart Sync & Status Monitor**:
   - Sinkronisasi otomatis setiap 1 jam via background job/scheduler Laravel.
   - Tombol manual *"Sinkronisasi Sekarang"* di header dashboard dengan animasi indikator progress, status koneksi Google Sheets, dan waktu update terakhir.
6. **Catatan Eksekutif Interaktif (*Executive Notes*)**:
   - Pimpinan/Admin dapat menambahkan catatan telaah kebijakan langsung pada dashboard yang tersimpan ke database lokal.

---

## 4. PERENCANAAN ARSITEKTUR TEKNIS & TEKNOLOGI

### A. Tech Stack
- **Backend Framework**: **Laravel 11 / 12** (PHP 8.2+ pada Laragon).
- **Frontend & UI/UX**: **Blade Components + Bootstrap 5.3 + Tabler Icons + Modern Glassmorphism Theme (Nuansa Biru Navy Kemenkeu/DJKN)**.
- **Visualisasi Grafik**: **Chart.js 4.x + ApexCharts** (Responsive, animated, export image, tooltip kaya data).
- **Tabel Data Interaktif**: **DataTables.net 2.x Responsive** dengan filter kolom, pencarian instan, dan export Excel/PDF.
- **Peta Spasial**: **Leaflet.js + OpenStreetMap**.
- **Database**: **MySQL (MariaDB di Laragon)** dengan skema tabel terindeks.
- **Autentikasi & SSO**: **OAuth2 Client terintegrasi dengan `sso-kpknl-palembang`**.

### B. Perancangan Skema Database Lokal (MySQL)

```
┌───────────────────────────────┐        ┌───────────────────────────────┐
│       bmn_potensi_idle        │        │         bmn_eks_idle          │
├───────────────────────────────┤        ├───────────────────────────────┤
│ id (PK, BigInt)               │        │ id (PK, BigInt)               │
│ no (Int)                      │        │ no (Int)                      │
│ hasil (Varchar/Int)           │        │ nama_kanwil (Varchar)         │
│ kanwil (Varchar)              │        │ nama_kpknl (Varchar)          │
│ kpknl (Varchar)               │        │ kode_barang (Varchar)         │
│ kode_satker (Varchar)         │        │ uraian_barang (Varchar)       │
│ kementerian_lembaga (Varchar) │        │ nup (Int)                     │
│ nama_satker (Varchar)         │        │ luas (Decimal)                │
│ kode_barang (Varchar)         │        │ nilai_perolehan (Decimal)     │
│ nup (Int)                     │        │ alamat (Text)                 │
│ kelompok_barang (Varchar)     │        │ jenis_tindak_lanjut (Varchar) │
│ nama_barang (Varchar)         │        │ jenis_pengelolaan (Varchar)   │
│ hasil_pengukuran_sbsk (Varchar│        │ no_surat (Varchar)            │
│ surat_klarifikasi (Varchar)   │        │ tanggal_surat (Date)          │
│ tanggal_klarifikasi (Date)    │        │ verifikasi_kanwil (Boolean)   │
│ surat_jawaban (Varchar)       │        │ link_bukti_dokumen (Varchar)  │
│ tanggal_jawaban (Date)        │        │ tipe (Varchar)                │
│ hasil_jawaban (Text)          │        │ bobot_nilai (Decimal)         │
│ tujuan_surat (Varchar)        │        │ nilai (Decimal)               │
│ validasi_kanwil (Boolean)     │        │ created_at / updated_at       │
│ status_klarifikasi (Varchar)  │        └───────────────────────────────┘
│ pemetaan_jawaban (Varchar)    │                        ▲
│ status_tindak_lanjut (Varchar)│                        │
│ hasil_penelitian (Varchar)    │        ┌───────────────┴───────────────┐
│ status_bmn_idle (Varchar)     │        │           sync_logs           │
│ klasterisasi (Varchar)        │        ├───────────────────────────────┤
│ is_kemenkeu (Boolean)         │        │ id (PK, BigInt)               │
│ eselon1_kemenkeu (Varchar)    │        │ source_url (Varchar)          │
│ created_at / updated_at       │        │ sheet_name (Varchar)          │
└───────────────────────────────┘        │ status (SUCCESS / FAILED)     │
                                         │ rows_synced (Int)             │
                                         │ duration_seconds (Float)      │
                                         │ error_message (Text, Nullable)│
                                         │ created_at                    │
                                         └───────────────────────────────┘
```

---

## 5. TAHAPAN IMPLEMENTASI & ROADMAP KERJA

| Fase | Durasi Estimasi | Kegiatan Utama |
| :--- | :---: | :--- |
| **Fase 1: Inisialisasi & Setup Arsitektur** | Hari 1 | Inisialisasi project Laravel di folder `d:\laragon\www\Dashboard Pengelolaan BMN`, konfigurasi database MySQL, migrasi skema tabel, dan instalasi dependencies. |
| **Fase 2: Smart Sync Pipeline & Data Parser** | Hari 2 | Pembuatan Service `GoogleSheetSyncService` yang mengunduh & mem-parsing sheet `potensi idle`, `eks bmn idle`, dan `Summary`, menangani formula `#REF!`/`IMPORTRANGE`, dan menyimpan ke database lokal. |
| **Fase 3: Pembuatan 5 Halaman Dashboard & Visualisasi Chart** | Hari 3-4 | Pembangunan antarmuka eksekutif (Blade + Bootstrap 5 + Chart.js) persis sesuai 5 gambar referensi paparan PPT DJKN KPKNL Palembang. |
| **Fase 4: Fitur Interaktif, DataTables, & GIS Mapping** | Hari 5 | Implementasi filter multi-kriteria (Kanwil, KPKNL, Klaster, Kemenkeu/Non-Kemenkeu), DataTables server-side, Leaflet.js peta sebaran aset, dan modul Executive Notes. |
| **Fase 5: Integrasi SSO & Otomasi Scheduler** | Hari 6 | Integrasi OAuth2 client dengan `sso-kpknl-palembang`, setup background scheduler sync 1 jam (`php artisan schedule:run`), dan tombol manual force sync. |
| **Fase 6: Pengujian, Optimasi, & Dokumentasi** | Hari 7 | Uji performa offline & online, verifikasi seluruh kalkulasi matriks angka, penyusunan buku panduan teknis dan user guide. |

---

## 6. KESIMPULAN
Dengan perencanaan matang dan komprehensif ini, Aplikasi **Dashboard Pengelolaan BMN KPKNL Palembang** akan menjadi instrumen pimpinan modern yang handal, cepat, informatif, dan terintegrasi dengan ekosistem digital KPKNL Palembang.

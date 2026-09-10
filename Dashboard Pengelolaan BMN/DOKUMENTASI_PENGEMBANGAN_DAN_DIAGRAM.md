# DOKUMENTASI SISTEM, ANALISIS, DAN DIAGRAM ARSITEKTUR
## Executive Dashboard Pengelolaan BMN Terindikasi Idle dan BMN Eks BMN Idle
**Kantor Pelayanan Kekayaan Negara dan Lelang (KPKNL) Palembang**  
*Kantor Wilayah DJKN Sumatera Selatan, Jambi, dan Bangka Belitung*

---

## DAFTAR ISI
1. [Ringkasan Eksekutif & Jawaban Seluruh Arahan Pengembangan Terbaru](#1-ringkasan-eksekutif--jawaban-seluruh-arahan-pengembangan-terbaru)
2. [Mekanisme Manajemen Tautan Spreadsheet Dinamis & Validasi Sistem](#2-mekanisme-manajemen-tautan-spreadsheet-dinamis--validasi-sistem)
3. [Analisis Mendalam: Aktor, Peran, dan Hak Akses Sistem](#3-analisis-mendalam-aktor-peran-dan-hak-akses-sistem)
4. [Analisis Faktual Metrik & Rekonsiliasi Data Spreadsheet](#4-analisis-faktual-metrik--rekonsiliasi-data-spreadsheet)
5. [Diagram Entity Relationship (ERD)](#5-diagram-entity-relationship-erd)
6. [Diagram Use Case](#6-diagram-use-case)
7. [Diagram Alur Kerja Proses Bisnis (BPMN 2.0)](#7-diagram-alur-kerja-proses-bisnis-bpmn-20)
8. [Petunjuk Penggunaan Sintaks pada Draw.io & PlantUML](#8-petunjuk-penggunaan-sintaks-pada-drawio--plantuml)

---

## 1. RINGKASAN EKSEKUTIF & JAWABAN SELURUH ARAHAN PENGEMBANGAN TERBARU

Berdasarkan lembar instruksi pengembangan pada berkas `rencana pengembangan.txt`, seluruh poin arahan telah selesai disempurnakan:

| No | Poin Arahan | Status | Realisasi & Solusi Teknis |
| :--- | :--- | :---: | :--- |
| **1** | **Sentralisasi Sumber Tautan Spreadsheet ke Menu Pengaturan** | **SELESAI** | Seluruh proses sinkronisasi background, tombol manual sync, serta tombol *"Buka Google Spreadsheet"* di seluruh menu tabel kini **100% mengacu secara dinamis** pada tautan yang disimpan di menu **Pengaturan Spreadsheet** (`app_settings`). Sistem tidak lagi mengandalkan hardcode URL. |
| **2** | **Validasi Ketat Tautan: Menampilkan Error `"link tidak ada"`** | **SELESAI** | Jika tautan spreadsheet kosong, belum diatur, atau dihapus pada menu pengaturan, sistem secara otomatis menolak eksekusi dan memunculkan notifikasi error eksplisit **`"link tidak ada"`** baik saat sinkronisasi API, tombol buka spreadsheet, maupun form submit. |
| **3** | **Pelebaran Kolom Tabel Sumber BMN Eks BMN Idle** | **SELESAI** | Kolom-kolom pada Tabel Sumber BMN Eks BMN Idle diperlebar secara proporsional. Kolom **Alamat Lengkap** (`min-width: 380px`, maks 550px) dan **Nomor Surat / Naskah** (`min-width: 320px`, maks 480px) dengan *word-break wrapping* rapi dan dual scrollbar horizontal. |
| **4** | **Pelebaran Kolom Tabel Sumber Potensi Idle** | **SELESAI** | Kolom **Nama Satker** (`min-width: 250px`), **Surat Klarifikasi** (`min-width: 280px`), dan **Nama Barang** (`min-width: 220px`) diperlebar agar teks panjang dapat dibaca secara leluasa tanpa terpotong. |
| **5** | **Penghapusan Fitur Simulasi Pengguna** | **SELESAI** | Menghapus seluruh tombol simulasi peran pada halaman login (`login.blade.php`), menghapus rute simulasi di `routes/web.php`, dan membersihkan dropdown simulasi di navbar topbar. Akses login murni melalui **SSO KPKNL Palembang**. |
| **6** | **Card Langkah Tindak Lanjut Terbagi Sub-Section** | **SELESAI** | Card ke-4 pada Tab Potensi Kemenkeu disusun menjadi 3 sub-section: **Pemantauan (14 NUP, batas: Akhir Semester I 2027)**, **Penelitian (3 NUP)**, dan **Penelusuran (2 NUP)**. |
| **7** | **Opsi Pilihan Jumlah Baris pada Setiap Tabel DataTables** | **SELESAI** | Seluruh tabel (`#tablePotensi19`, `#tableSourcePotensi`, `#tableSourceEks`, `#tableSyncLog`) kini memiliki kontrol dropdown: **`Tampilkan [10 | 25 | 50 | 100 | Semua] baris per halaman`**. |

---

## 2. MEKANISME MANAJEMEN TAUTAN SPREADSHEET DINAMIS & VALIDASI SISTEM

```
+-------------------------------------------------------------------------------------------------------------------------+
|                                    ARSITEKTUR SENTRALISASI & VALIDASI SPREADSHEET                                       |
+-------------------------------------------------------------------------------------------------------------------------+

 [ Menu Pengaturan Spreadsheet (Tab 9) ]
                   │
                   ▼ (POST /api/settings/spreadsheet)
       [ Validasi: Cek Tautan Ada/Tidak ] ──(Jika Kosong)──► Response: "link tidak ada" (HTTP 422)
                   │ (Jika Valid)
                   ▼
       [ Tabel Database: app_settings ]
         - spreadsheet_potensi_url
         - spreadsheet_eks_url
                   │
        ┌──────────┴──────────────────────────┐
        ▼                                     ▼
 [ GoogleSheetSyncService ]            [ Blade View Dashboard ]
  • Baca dari AppSetting                • Tombol "Buka Google Spreadsheet" (Tab 6 & 7)
  • Jika null: Lempar Exception:         • Jika kosong: SweetAlert ("Link Tidak Ada")
    "link tidak ada"                     • Jika ada: window.open(url, '_blank')
  • Konversi otomatis ke Export URL
  • Download live data & update DB
```

---

## 3. ANALISIS MENDALAM: AKTOR, PERAN, DAN HAK AKSES SISTEM

Sistem dirancang dengan pembagian peran yang terintegrasi langsung dengan database SSO KPKNL Palembang (`sso_kpknl_palembang`):

```
+-------------------------------------------------------------------------------------------------------------------------+
|                                              MATRIKS AKTOR & HAK AKSES SISTEM                                           |
+-------------------+-------------------------------------------------------------------+---------------------------------+
| Aktor             | Peran & Tanggung Jawab Utama                                      | Hak Akses Dashboard             |
+-------------------+-------------------------------------------------------------------+---------------------------------+
| Superadmin SSO    | Mengelola akun pengguna, penugasan aplikasi (assignment check),   | Hak Akses Penuh (All Tabs,      |
|                   | dan konfigurasi tautan Google Spreadsheet.                        | Pengaturan, Manual Sync, Export)|
| Admin Seksi PKN   | Monitoring harian BMN terindikasi idle & eks idle Palembang,      | Tab 1 s.d. Tab 8, Manual Sync,  |
|                   | menindaklanjuti klarifikasi satker, dan ekspor data.              | Ekspor DataTables Excel/PDF     |
| Pimpinan          | Mengambil keputusan strategis, meninjau capaian matriks se-SJB,   | Tab Eksekutif, Matriks Capaian, |
| (Kepala Kantor)   | dan memberikan catatan/arahan pada modul Catatan Eksekutif.       | Simpan Catatan Rekomendasi      |
| Pegawai / Satker  | Meninjau data terfilter satker dan membaca tindak lanjut BMN.     | Read-Only Data Dashboard        |
| System Scheduler  | Menjalankan sinkronisasi background otomatis secara berkala.     | Background Job Runner (Hourly)  |
+-------------------+-------------------------------------------------------------------+---------------------------------+
```

---

## 4. ANALISIS FAKTUAL METRIK & REKONSILIASI DATA SPREADSHEET

1. **Populasi BMN Terindikasi Idle (Palembang 256 NUP)**:
   - **Pemantauan**: 198 NUP (77,3%)
   - **Penelusuran**: 58 NUP (22,7%)
   - **Penelitian**: 0 NUP
   - **Total**: 256 NUP (100% konsisten dengan kartu KPI Populasi Tercatat).

2. **Aset Berpotensi Menjadi BMN Idle Lingkup Kemenkeu (19 NUP)**:
   - **Pemantauan**: **14 NUP** *(Batas waktu tindak lanjut: **Akhir Semester I 2027**)*
   - **Penelitian**: **3 NUP** *(Klarifikasi dokumen kepemilikan & SBSK)*
   - **Penelusuran**: **2 NUP** *(Pengecekan fisik dan pola pemanfaatan satker)*
   - **Nilai Perolehan Buku Aset**: **`Rp154.259.000.000`** (Rp 154,25 Miliar)
   - Rasio terhadap Populasi Kemenkeu: $\frac{19}{79} \times 100\% = \mathbf{24,05\%}$

3. **Nominal Perolehan Aset BMN Eks BMN Idle (Kolom H3:H18)**:
   - Dihitung dari 16 NUP aset eks idle KPKNL Palembang:
     $$\text{Total Nilai Perolehan} = \sum_{i=1}^{16} \text{Kolom } H_i = \mathbf{\text{Rp } 25.254.267.000}$$

---

## 5. DIAGRAM ENTITY RELATIONSHIP (ERD)

### Visual Render Gambar ERD:
![Entity Relationship Diagram (ERD)](file:///d:/laragon/www/Dashboard%20Pengelolaan%20BMN/public/images/diagrams/erd_diagram.jpg)

### A. Sintaks Mermaid
```mermaid
erDiagram
    USERS ||--o{ USER_APPLICATION : has
    APPLICATIONS ||--o{ USER_APPLICATION : assigned_to
    USERS ||--o{ OAUTH_TOKENS : owns
    APPLICATIONS ||--o{ OAUTH_TOKENS : authorizes
    APPLICATIONS ||--o{ SYNC_LOGS : records
    USERS ||--o{ EXECUTIVE_NOTES : writes
    APPLICATIONS ||--o{ APP_SETTINGS : configures

    USERS {
        bigint id PK
        string name
        string username
        string email
        string role "superadmin | admin | pegawai | eksekutif"
        bigint sso_id FK
        string avatar_url
        timestamp created_at
    }

    APPLICATIONS {
        bigint id PK
        string name
        string slug
        string client_id
        string client_secret
        string redirect_uri
        string status
        timestamp created_at
    }

    USER_APPLICATION {
        bigint id PK
        bigint user_id FK
        bigint application_id FK
        timestamp created_at
    }

    APP_SETTINGS {
        bigint id PK
        string setting_key "spreadsheet_potensi_url | spreadsheet_eks_url"
        text setting_value
        string setting_group
        text description
        timestamp updated_at
    }

    BMN_POTENSI_IDLE {
        bigint id PK
        int row_no
        string kpknl
        string kode_satker
        string kementerian_lembaga
        string nama_satker
        string kode_barang
        int nup
        string kelompok_barang
        string nama_barang
        string hasil_pengukuran_sbsk
        string status_tindak_lanjut
        string status_bmn_idle
        boolean is_kemenkeu
        string eselon1_kemenkeu
        timestamp created_at
    }

    BMN_EKS_IDLE {
        bigint id PK
        int row_no
        string nama_kpknl
        string kode_barang
        string uraian_barang
        int nup
        decimal luas
        decimal nilai_perolehan "Kolom H Worksheet Eks BMN Idle"
        text alamat
        string jenis_pengelolaan
        text no_surat
        string verifikasi_kanwil
        timestamp created_at
    }

    SYNC_LOGS {
        bigint id PK
        string source_type
        string status
        int potensi_synced
        int eks_idle_synced
        decimal duration_seconds
        string triggered_by
        timestamp created_at
    }

    EXECUTIVE_NOTES {
        bigint id PK
        string section_key
        text note_content
        string author_name
        timestamp updated_at
    }
```

### B. Sintaks PlantUML
```plantuml
@startuml
!theme plain
skinparam linetype ortho
skinparam packageStyle rectangle
skinparam roundcorner 8

entity "users" as users {
  * id : BIGINT (PK)
  --
  name : VARCHAR
  username : VARCHAR
  email : VARCHAR
  role : VARCHAR
  sso_id : BIGINT
  avatar_url : VARCHAR
}

entity "applications" as apps {
  * id : BIGINT (PK)
  --
  name : VARCHAR
  slug : VARCHAR
  client_id : VARCHAR
  client_secret : VARCHAR
}

entity "user_application" as user_app {
  * id : BIGINT (PK)
  --
  user_id : BIGINT (FK)
  application_id : BIGINT (FK)
}

entity "app_settings" as settings {
  * id : BIGINT (PK)
  --
  setting_key : VARCHAR
  setting_value : TEXT
  setting_group : VARCHAR
  description : TEXT
}

entity "bmn_potensi_idle" as potensi {
  * id : BIGINT (PK)
  --
  kpknl : VARCHAR
  kode_satker : VARCHAR
  nama_satker : VARCHAR
  kode_barang : VARCHAR
  nup : INT
  status_tindak_lanjut : VARCHAR
  status_bmn_idle : VARCHAR
  is_kemenkeu : BOOLEAN
}

entity "bmn_eks_idle" as eks {
  * id : BIGINT (PK)
  --
  nama_kpknl : VARCHAR
  kode_barang : VARCHAR
  nup : INT
  luas : DECIMAL
  nilai_perolehan : DECIMAL (Kolom H)
  alamat : TEXT
  jenis_pengelolaan : VARCHAR
  no_surat : TEXT
}

entity "sync_logs" as logs {
  * id : BIGINT (PK)
  --
  status : VARCHAR
  potensi_synced : INT
  eks_idle_synced : INT
  duration_seconds : DECIMAL
}

users ||--o{ user_app : "assigned"
apps ||--o{ user_app : "grants"
apps ||--o{ settings : "configures"
@enduml
```

---

## 6. DIAGRAM USE CASE

### Visual Render Gambar Use Case:
![Use Case Diagram](file:///d:/laragon/www/Dashboard%20Pengelolaan%20BMN/public/images/diagrams/usecase_diagram.jpg)

### A. Sintaks Mermaid
```mermaid
graph LR
    subgraph Aktor
        SA["Superadmin / Admin Seksi PKN"]
        KK["Kepala Kantor (Eksekutif)"]
        SCH["System Scheduler (1 Jam)"]
    end

    subgraph "Executive Dashboard Pengelolaan BMN"
        UC1(["Autentikasi SSO & Validasi Assignment"])
        UC2(["Melihat Dashboard BMN Eks Idle (Palembang)"])
        UC3(["Melihat Dashboard Terindikasi Idle (256 NUP)"])
        UC4(["Filter & Analisis Matriks Capaian KPKNL"])
        UC5(["Menganalisis Pemetaan Kemenkeu (Palembang)"])
        UC6(["Melihat Rincian 19 Aset Potensi Idle"])
        UC7(["Eksplorasi Tabel Sumber dengan Dual Scrollbar"])
        UC8(["Membuka Google Spreadsheet Online"])
        UC9(["Mengatur Tautan Spreadsheet Dinamis"])
        UC10(["Menyimpan Catatan Rekomendasi Pimpinan"])
        UC11(["Trigger Manual Sync Spreadsheet"])
        UC12(["Auto-Sync Background Google Sheet"])
        UC13(["Validasi & Error Handling 'Link Tidak Ada'"])
        UC14(["Mengatur Jumlah Baris Tampil (10/25/50/100/All)"])
        UC15(["Ekspor Laporan (Excel/PDF/Print)"])
    end

    SA --> UC1
    SA --> UC2
    SA --> UC3
    SA --> UC4
    SA --> UC6
    SA --> UC7
    SA --> UC8
    SA --> UC9
    SA --> UC11
    SA --> UC14
    SA --> UC15

    KK --> UC1
    KK --> UC2
    KK --> UC3
    KK --> UC4
    KK --> UC5
    KK --> UC6
    KK --> UC8
    KK --> UC10
    KK --> UC14
    KK --> UC15

    SCH --> UC12

    UC11 -.->|include| UC12
    UC11 -.->|validate| UC13
    UC9 -.->|validate| UC13
    UC8 -.->|validate| UC13
```

---

## 7. DIAGRAM ALUR KERJA PROSES BISNIS (BPMN 2.0)

### Visual Render Gambar BPMN:
![BPMN Workflow Diagram](file:///d:/laragon/www/Dashboard%20Pengelolaan%20BMN/public/images/diagrams/bpmn_diagram.jpg)

### A. Sintaks Mermaid
```mermaid
sequenceDiagram
    autonumber
    participant USR as Pengguna / Admin
    participant SETT as Modul Pengaturan Spreadsheet
    participant GS as Google Spreadsheet Online
    participant DB as Database MySQL (app_settings)
    participant UI as Executive Dashboard UI

    Note over USR,UI: ALUR SINKRONISASI DINAMIS & VALIDASI LINK TIDAK ADA

    opt Mengatur Tautan Spreadsheet
        USR->>UI: Buka Menu "Pengaturan Spreadsheet"
        USR->>SETT: Masukkan URL & Klik "Simpan Pengaturan"
        alt Input Link Kosong
            SETT-->>USR: Error Alert: "link tidak ada" (HTTP 422)
        else Input Link Valid
            SETT->>DB: Simpan ke tabel app_settings
            SETT-->>USR: Notifikasi: "Tautan Spreadsheet Berhasil Disimpan"
        end
    end

    opt Membuka Google Spreadsheet
        USR->>UI: Klik Tombol "Buka Google Spreadsheet" di Header Tabel
        UI->>DB: Periksa URL aktif dari app_settings
        alt URL Kosong / Belum Diatur
            UI-->>USR: SweetAlert: "Link Tidak Ada. Silakan atur pada menu Pengaturan."
        else URL Tersedia
            UI->>GS: Buka Lembar Kerja Online di Tab Baru
        end
    end

    opt Sinkronisasi Data Realtime
        USR->>UI: Klik Tombol "Sinkronisasi"
        UI->>DB: Ambil URL aktif dari app_settings
        alt URL Tidak Ada di Database
            UI-->>USR: SweetAlert Error: "link tidak ada"
        else URL Tersedia
            UI->>GS: Unduh Live Data XLSX via Export URL
            GS-->>UI: Return Dataset Binary XLSX
            UI->>DB: Update bmn_potensi_idle, bmn_eks_idle, & sync_logs
            UI-->>USR: SweetAlert: "Sinkronisasi Berhasil!"
        end
    end
```

---

## 8. PETUNJUK PENGGUNAAN SINTAKS PADA DRAW.IO & PLANTUML

1. **Menggunakan di Draw.io (Web / Desktop)**:
   - Buka [Draw.io](https://app.diagrams.net/).
   - Klik menu **`Arrange`** -> **`Insert`** -> **`Advanced`** -> Pilih **`PlantUML`** atau **`Mermaid`**.
   - Tempelkan (*paste*) blok kode sintaks PlantUML atau Mermaid yang ada di bab 5, 6, atau 7 di atas.
   - Klik **`Insert`**, diagram akan langsung terkonversi menjadi objek visual interaktif.

2. **Menggunakan di PlantText / PlantUML Server**:
   - Buka [PlantText.com](https://www.planttext.com/) atau server PlantUML lokal.
   - Tempelkan sintaks `@startuml ... @enduml`.
   - Unduh gambar beresolusi tinggi.

---
*Dokumen ini tersimpan permanen di direktori proyek:*  
`d:\laragon\www\Dashboard Pengelolaan BMN\DOKUMENTASI_PENGEMBANGAN_DAN_DIAGRAM.md`

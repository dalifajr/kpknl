<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Buku Panduan Pengguna Sistem SSO KPKNL Palembang</title>
    <style>
        @page {
            margin: 1.8cm 1.5cm 2cm 1.5cm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #1e293b;
            line-height: 1.5;
            font-size: 10pt;
            margin: 0;
            padding: 0;
        }
        /* Header & Footer */
        .kop-header {
            border-bottom: 2.5px solid #0b3b60;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .kop-table {
            width: 100%;
            border-collapse: collapse;
        }
        .kop-logo {
            width: 70px;
            vertical-align: middle;
            text-align: center;
        }
        .kop-text {
            text-align: center;
            vertical-align: middle;
        }
        .kop-text h4 {
            margin: 0;
            font-size: 11pt;
            color: #0f172a;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .kop-text h3 {
            margin: 2px 0;
            font-size: 12pt;
            color: #0b3b60;
            font-weight: 800;
            text-transform: uppercase;
        }
        .kop-text p {
            margin: 2px 0 0 0;
            font-size: 8pt;
            color: #64748b;
        }
        .doc-title-box {
            background-color: #f1f5f9;
            border-left: 4px solid #0b3b60;
            padding: 14px 16px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .doc-title-box h1 {
            margin: 0 0 4px 0;
            font-size: 14pt;
            color: #0b3b60;
            text-transform: uppercase;
            font-weight: 800;
        }
        .doc-title-box p {
            margin: 0;
            font-size: 9pt;
            color: #475569;
        }
        /* Metadata Table */
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
        }
        .meta-table td {
            padding: 6px 12px;
            font-size: 8.5pt;
            border-bottom: 1px solid #f1f5f9;
        }
        .meta-label {
            font-weight: bold;
            color: #475569;
            width: 25%;
            background-color: #f8fafc;
        }
        .meta-val {
            color: #0f172a;
            font-weight: 600;
        }
        /* Sections */
        .section-header {
            background-color: #0b3b60;
            color: #ffffff;
            padding: 8px 12px;
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            border-radius: 3px;
            margin-top: 24px;
            margin-bottom: 14px;
        }
        .section-header.user { background-color: #0284c7; }
        .section-header.admin { background-color: #4f46e5; }
        .section-header.maintenance { background-color: #d97706; }
        .section-header.superadmin { background-color: #dc2626; }

        .feature-box {
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 10px 14px;
            margin-bottom: 12px;
            background-color: #fafafa;
        }
        .feature-title {
            font-weight: bold;
            color: #0b3b60;
            font-size: 9.5pt;
            margin-bottom: 4px;
        }
        .feature-summary {
            font-size: 8.5pt;
            color: #334155;
            font-weight: 600;
            margin-bottom: 4px;
        }
        .feature-details {
            font-size: 8.5pt;
            color: #64748b;
            line-height: 1.45;
        }

        /* Steps */
        .step-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .step-num-cell {
            width: 28px;
            vertical-align: top;
            padding: 4px 0;
        }
        .step-num {
            display: inline-block;
            width: 20px;
            height: 20px;
            line-height: 20px;
            background-color: #e0f2fe;
            color: #0369a1;
            font-weight: bold;
            font-size: 8pt;
            text-align: center;
            border-radius: 10px;
        }
        .step-text-cell {
            vertical-align: top;
            padding: 4px 6px;
            font-size: 8.5pt;
            color: #1e293b;
        }
        .guide-box {
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 12px;
            margin-bottom: 14px;
            background-color: #ffffff;
            page-break-inside: avoid;
        }
        .guide-box h4 {
            margin: 0 0 8px 0;
            font-size: 10pt;
            color: #0f172a;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 6px;
        }
        .note-box {
            background-color: #fefce8;
            border-left: 3px solid #eab308;
            padding: 6px 10px;
            font-size: 8pt;
            color: #854d0e;
            margin-top: 8px;
            border-radius: 0 3px 3px 0;
        }
        /* Apps Table */
        .apps-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 8pt;
        }
        .apps-table th {
            background-color: #0b3b60;
            color: #ffffff;
            padding: 6px 8px;
            text-align: left;
            font-weight: bold;
        }
        .apps-table td {
            padding: 6px 8px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
        }
        .apps-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 7.5pt;
            font-weight: bold;
            border-radius: 3px;
        }
        .badge-success { background-color: #dcfce7; color: #15803d; }
        .badge-primary { background-color: #e0f2fe; color: #0369a1; }
        .badge-warning { background-color: #fef3c7; color: #92400e; }

        .page-break {
            page-break-after: always;
        }
        .footer-note {
            text-align: center;
            font-size: 7.5pt;
            color: #94a3b8;
            margin-top: 30px;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
        }
    </style>
</head>
<body>

    <!-- KOP RESMI DOKUMEN -->
    <div class="kop-header">
        <table class="kop-table">
            <tr>
                <td class="kop-text">
                    <h4>Kementerian Keuangan Republik Indonesia</h4>
                    <h3>Direktorat Jenderal Kekayaan Negara</h3>
                    <h4>Kantor Pelayanan Kekayaan Negara dan Lelang Palembang</h4>
                    <p>Gedung Keuangan Negara, Jl. Kapten A. Rivai No.4, Palembang, Sumatera Selatan 30129</p>
                </td>
            </tr>
        </table>
    </div>

    <!-- JUDUL DOKUMEN -->
    <div class="doc-title-box">
        <h1>Buku Panduan Pengguna Sistem SSO</h1>
        <p>Pedoman Pengoperasian Portal Single Sign-On & Ekosistem Aplikasi Terintegrasi</p>
    </div>

    <!-- METADATA DOKUMEN -->
    <table class="meta-table">
        <tr>
            <td class="meta-label">Versi Dokumen</td>
            <td class="meta-val">1.0.0 (Rilis Resmi 2026)</td>
            <td class="meta-label">Tanggal Terbit</td>
            <td class="meta-val">{{ date('d F Y, H:i') }} WIB</td>
        </tr>
        <tr>
            <td class="meta-label">Cakupan Panduan</td>
            <td class="meta-val">
                @if($activeRole === 'all')
                    Semua Role (Pegawai, Admin, Maintenance, Superadmin)
                @else
                    Khusus Role {{ strtoupper($activeRole) }}
                @endif
            </td>
            <td class="meta-label">Dicetak Oleh</td>
            <td class="meta-val">{{ $user->name }} ({{ $user->username }})</td>
        </tr>
    </table>

    <!-- RINGKASAN ARSITEKTUR SSO -->
    <div class="section-header">1. Gambaran Umum & Arsitektur Ekosistem SSO</div>
    <p style="font-size: 8.5pt; color: #475569; margin-bottom: 12px;">
        Sistem Single Sign-On (SSO) KPKNL Palembang dirancang sebagai gerbang otentikasi terpusat (Identity Provider / IdP) berbasis standar OAuth 2.0. Sistem ini memungkinkan seluruh pegawai dan pengelola mengakses berbagai aplikasi kedinasan hanya dengan satu set kredensial aman.
    </p>

    @foreach($docSections['overview']['features'] as $feat)
        <div class="feature-box">
            <div class="feature-title">{{ $feat['name'] }}</div>
            <div class="feature-summary">{{ $feat['summary'] }}</div>
            <div class="feature-details">{{ $feat['details'] }}</div>
        </div>
    @endforeach

    <!-- PANDUAN ROLE PEGAWAI / USER -->
    @if($activeRole === 'all' || $activeRole === 'user')
        <div class="page-break"></div>
        <div class="section-header user">
            2. Panduan Penggunaan: {{ $docSections['user']['role_name'] }}
        </div>
        <p style="font-size: 8.5pt; color: #475569; margin-bottom: 14px;">
            {{ $docSections['user']['summary'] }}
        </p>

        @foreach($docSections['user']['guides'] as $guide)
            <div class="guide-box">
                <h4>{{ $guide['title'] }}</h4>
                <table class="step-table">
                    @foreach($guide['steps'] as $idx => $step)
                        <tr>
                            <td class="step-num-cell">
                                <span class="step-num">{{ $idx + 1 }}</span>
                            </td>
                            <td class="step-text-cell">{{ $step }}</td>
                        </tr>
                    @endforeach
                </table>
                @if(!empty($guide['notes']))
                    <div class="note-box">
                        <strong>Catatan Penting:</strong> {{ $guide['notes'] }}
                    </div>
                @endif
            </div>
        @endforeach
    @endif

    <!-- PANDUAN ROLE ADMINISTRATOR -->
    @if($activeRole === 'all' || $activeRole === 'admin')
        @if($activeRole === 'all') <div class="page-break"></div> @endif
        <div class="section-header admin">
            3. Panduan Pengelolaan: {{ $docSections['admin']['role_name'] }}
        </div>
        <p style="font-size: 8.5pt; color: #475569; margin-bottom: 14px;">
            {{ $docSections['admin']['summary'] }}
        </p>

        @foreach($docSections['admin']['guides'] as $guide)
            <div class="guide-box">
                <h4>{{ $guide['title'] }}</h4>
                <table class="step-table">
                    @foreach($guide['steps'] as $idx => $step)
                        <tr>
                            <td class="step-num-cell">
                                <span class="step-num" style="background-color: #e0e7ff; color: #4338ca;">{{ $idx + 1 }}</span>
                            </td>
                            <td class="step-text-cell">{{ $step }}</td>
                        </tr>
                    @endforeach
                </table>
                @if(!empty($guide['notes']))
                    <div class="note-box" style="background-color: #f5f3ff; border-left-color: #4f46e5; color: #3730a3;">
                        <strong>Tips Admin:</strong> {{ $guide['notes'] }}
                    </div>
                @endif
            </div>
        @endforeach
    @endif

    <!-- PANDUAN ROLE TIM MAINTENANCE (FITUR UNGGULAN) -->
    @if($activeRole === 'all' || $activeRole === 'maintenance')
        @if($activeRole === 'all') <div class="page-break"></div> @endif
        <div class="section-header maintenance">
            4. Panduan Teknis: {{ $docSections['maintenance']['role_name'] }}
        </div>
        <p style="font-size: 8.5pt; color: #475569; margin-bottom: 14px;">
            {{ $docSections['maintenance']['summary'] }}
        </p>

        @foreach($docSections['maintenance']['guides'] as $guide)
            <div class="guide-box">
                <h4>{{ $guide['title'] }}</h4>
                <table class="step-table">
                    @foreach($guide['steps'] as $idx => $step)
                        <tr>
                            <td class="step-num-cell">
                                <span class="step-num" style="background-color: #fef3c7; color: #92400e;">{{ $idx + 1 }}</span>
                            </td>
                            <td class="step-text-cell">{{ $step }}</td>
                        </tr>
                    @endforeach
                </table>
                @if(!empty($guide['notes']))
                    <div class="note-box" style="background-color: #fffbeb; border-left-color: #d97706; color: #92400e;">
                        <strong>Perhatian Teknis:</strong> {{ $guide['notes'] }}
                    </div>
                @endif
            </div>
        @endforeach
    @endif

    <!-- PANDUAN ROLE SUPERADMIN -->
    @if($activeRole === 'all' || $activeRole === 'superadmin')
        @if($activeRole === 'all') <div class="page-break"></div> @endif
        <div class="section-header superadmin">
            5. Panduan Tata Kelola: {{ $docSections['superadmin']['role_name'] }}
        </div>
        <p style="font-size: 8.5pt; color: #475569; margin-bottom: 14px;">
            {{ $docSections['superadmin']['summary'] }}
        </p>

        @foreach($docSections['superadmin']['guides'] as $guide)
            <div class="guide-box">
                <h4>{{ $guide['title'] }}</h4>
                <table class="step-table">
                    @foreach($guide['steps'] as $idx => $step)
                        <tr>
                            <td class="step-num-cell">
                                <span class="step-num" style="background-color: #fee2e2; color: #991b1b;">{{ $idx + 1 }}</span>
                            </td>
                            <td class="step-text-cell">{{ $step }}</td>
                        </tr>
                    @endforeach
                </table>
                @if(!empty($guide['notes']))
                    <div class="note-box" style="background-color: #fef2f2; border-left-color: #dc2626; color: #991b1b;">
                        <strong>Kepatuhan Tata Kelola:</strong> {{ $guide['notes'] }}
                    </div>
                @endif
            </div>
        @endforeach
    @endif

    <!-- KATALOG APLIKASI EKOSISTEM -->
    <div class="section-header">Katalog Aplikasi Ekosistem SSO Terdaftar</div>
    <p style="font-size: 8.5pt; color: #475569; margin-bottom: 8px;">
        Berikut adalah daftar aplikasi resmi yang saat ini telah terhubung ke Identity Provider SSO KPKNL Palembang:
    </p>

    <table class="apps-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 25%;">Nama Aplikasi</th>
                <th style="width: 15%;">Slug / ID</th>
                <th style="width: 35%;">Alamat URL & Keterangan</th>
                <th style="width: 20%;">Cakupan Role</th>
            </tr>
        </thead>
        <tbody>
            @forelse($applications as $i => $app)
                <tr>
                    <td style="text-align: center;">{{ $i + 1 }}</td>
                    <td><strong>{{ $app->name }}</strong></td>
                    <td><code>{{ $app->slug }}</code></td>
                    <td>
                        <span style="color: #0284c7;">{{ $app->url }}</span><br>
                        <span style="color: #64748b; font-size: 7.5pt;">{{ $app->description ?: '-' }}</span>
                    </td>
                    <td>
                        @if(str_contains($app->slug, 'lelang') || str_contains($app->slug, 'peminjam'))
                            <span class="badge badge-warning">Peminjam / Pelelang / Admin</span>
                        @else
                            <span class="badge badge-primary">User / Operator / Admin</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: #94a3b8;">Belum ada aplikasi yang terdaftar.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer-note">
        Dokumen ini diterbitkan secara otomatis oleh Sistem Single Sign-On (SSO) KPKNL Palembang &bull; Hak Cipta &copy; {{ date('Y') }} KPKNL Palembang.
    </div>

</body>
</html>

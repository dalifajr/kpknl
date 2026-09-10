@extends('layouts.app')

@section('title', 'Executive Dashboard — Pengelolaan BMN KPKNL Palembang')

@section('content')

<!-- ========================================================================= -->
<!-- 1. TAB 1: BMN EKS BMN IDLE (KPKNL PALEMBANG)                             -->
<!-- ========================================================================= -->
<div class="dashboard-tab-pane show active" id="tab-eks-idle">
    <!-- Top Header Banner Title -->
    <div class="d-flex justify-content-between align-items-center mb-4 text-white">
        <div class="col-12">
            <h2 class="mb-1 text-white fw-bold">Executive Dashboard — BMN eks BMN Idle</h2>
            <p class="mb-0 text-white-50">Posisi per {{ date('d F Y') }} &bull; Terfilter: <strong>KPKNL Palembang</strong></p>
        </div>
    </div>

    <!-- Summary KPI Cards Row -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card interactive-card shadow-sm border-0 h-100 border-start border-4 border-primary" onclick="openDrilldown('eks', 'all', 'Seluruh BMN Eks Idle (KPKNL Palembang)', '16 Aset Tercatat')" title="Klik untuk melihat rincian 16 aset eks idle">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem;">SALDO MASUK (2013-2026)</small>
                        <span class="interactive-badge-hint"><i class="fas fa-arrow-up-right-from-square"></i></span>
                    </div>
                    <div class="d-flex align-items-center mt-1">
                        <h3 class="fw-bold text-dark mb-0">{{ $eksSaldoMasuk }} <span class="fs-6 text-muted fw-normal">NUP</span></h3>
                        <i class="fas fa-landmark ms-auto text-primary opacity-50 fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card interactive-card shadow-sm border-0 h-100 border-start border-4 border-info" onclick="openDrilldown('eks', 'dokumen_pengelolaan', 'BMN Eks Idle - Saldo Per 2026 (Dokumen Pengelolaan BMN)', '{{ $eksSaldoBarang }} Aset')" title="Klik untuk melihat rincian {{ $eksSaldoBarang }} aset saldo per 2026 (dokumen pengelolaan BMN)">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem;">SALDO PER 2026</small>
                        <span class="interactive-badge-hint"><i class="fas fa-arrow-up-right-from-square"></i></span>
                    </div>
                    <div class="d-flex align-items-center mt-1">
                        <h3 class="fw-bold text-dark mb-0">{{ $eksSaldoBarang }} <span class="fs-6 text-muted fw-normal">NUP</span></h3>
                        <i class="fas fa-chart-pie ms-auto text-info opacity-50 fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card interactive-card shadow-sm border-0 h-100 border-start border-4 border-success" onclick="openDrilldown('eks', 'usulan_psp', 'BMN Eks Idle - Usulan Penggunaan PSP (Dokumen Usulan Pengelolaan BMN)', '{{ $eksPengelolaanCounts['PSP'] }} Aset')" title="Klik untuk melihat rincian {{ $eksPengelolaanCounts['PSP'] }} aset usulan PSP">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem;">USULAN PENGGUNAAN (PSP)</small>
                        <span class="interactive-badge-hint"><i class="fas fa-arrow-up-right-from-square"></i></span>
                    </div>
                    <div class="d-flex align-items-center mt-1">
                        <h3 class="fw-bold text-success mb-0">{{ $eksPengelolaanCounts['PSP'] }} <span class="fs-6 text-muted fw-normal">NUP</span></h3>
                        <i class="fas fa-hand-holding-dollar ms-auto text-success opacity-50 fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card interactive-card shadow-sm border-0 h-100 border-start border-4 border-warning" onclick="openDrilldown('eks', 'penghapusan', 'BMN Eks Idle - Usulan Penghapusan / Hibah', '10 Aset')" title="Klik untuk melihat rincian 10 aset penghapusan/hibah">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem;">USULAN PENGHAPUSAN/HIBAH</small>
                        <span class="interactive-badge-hint"><i class="fas fa-arrow-up-right-from-square"></i></span>
                    </div>
                    <div class="d-flex align-items-center mt-1">
                        <h3 class="fw-bold text-warning mb-0">{{ $eksPengelolaanCounts['Dipindahtangankan'] }} <span class="fs-6 text-muted fw-normal">NUP</span></h3>
                        <i class="fas fa-trash-can ms-auto text-warning opacity-50 fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <!-- Horizontal Bar: Usulan Pengelolaan -->
        <div class="col-lg-6">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <div class="fw-bold text-dark"><i class="fas fa-chart-bar text-primary me-2"></i> Usulan Pengelolaan BMN Eks BMN Idle</div>
                    <span class="badge bg-light text-muted border">Klik batang untuk rincian</span>
                </div>
                <div class="card-body" style="height: 300px;">
                    <canvas id="chartUsulanEksIdle" class="chart-interactive"></canvas>
                </div>
            </div>
        </div>

        <!-- Donut Chart Palembang -->
        <div class="col-lg-6">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <div class="fw-bold text-dark"><i class="fas fa-chart-pie text-success me-2"></i> Tindak Lanjut BMN Eks Idle (KPKNL Palembang)</div>
                    <span class="badge bg-success-subtle text-success border cursor-pointer" onclick="openDrilldown('eks', 'all', 'Seluruh BMN Eks Idle Palembang', '{{ $eksSaldoMasuk }} NUP')">{{ $eksSaldoMasuk }} NUP Total</span>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-7" style="height: 270px;">
                            <canvas id="chartDonutPalembang" class="chart-interactive"></canvas>
                        </div>
                        <div class="col-5">
                            <div class="p-2 mb-2 rounded-3 bg-light border-start border-4 border-warning interactive-card" onclick="openDrilldown('eks', 'usulan_psp', 'BMN Eks Idle - Usulan Penggunaan PSP (Dokumen Usulan Pengelolaan BMN)', '{{ $eksPengelolaanCounts['PSP'] }} Aset')" title="Klik rincian Usulan PSP">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="small fw-semibold text-muted">Usulan PSP</div>
                                    <i class="fas fa-arrow-up-right-from-square text-muted" style="font-size: 0.65rem;"></i>
                                </div>
                                <div class="h5 fw-bold text-primary mb-0">{{ $eksPengelolaanCounts['PSP'] }} <small class="fs-6 text-muted">NUP</small></div>
                            </div>
                            <div class="p-2 mb-2 rounded-3 bg-light border-start border-4 border-success interactive-card" onclick="openDrilldown('eks', 'penghapusan', 'BMN Eks Idle - Penghapusan / Penjualan', '10 Aset')" title="Klik rincian Penghapusan">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="small fw-semibold text-muted">Penghapusan / Penjualan</div>
                                    <i class="fas fa-arrow-up-right-from-square text-muted" style="font-size: 0.65rem;"></i>
                                </div>
                                <div class="h5 fw-bold text-success mb-0">{{ $eksPengelolaanCounts['Dipindahtangankan'] }} <small class="fs-6 text-muted">NUP</small></div>
                            </div>
                            <div class="p-2 rounded-3 bg-light border-start border-4 border-info interactive-card" onclick="openDrilldown('eks', 'dokumen_pengelolaan', 'BMN Eks Idle - Saldo Per 2026 (Dokumen Pengelolaan BMN)', '{{ $eksSaldoBarang }} Aset')" title="Klik rincian Saldo Per 2026">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="small fw-semibold text-muted">Saldo Per 2026 (PSP Selesai)</div>
                                    <i class="fas fa-arrow-up-right-from-square text-muted" style="font-size: 0.65rem;"></i>
                                </div>
                                <div class="h5 fw-bold text-info mb-0">{{ $eksSaldoBarang }} <small class="fs-6 text-muted">NUP</small></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Executive Notes Section -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <div class="fw-bold text-dark"><i class="fas fa-pen-to-square text-primary me-2"></i> Catatan Eksekutif — Tindak Lanjut Eks BMN Idle (KPKNL Palembang)</div>
            <button type="button" class="btn btn-sm btn-primary px-3 shadow-sm" onclick="saveExecutiveNote('dashboard_eks_idle')">
                <i class="fas fa-save me-1"></i> Simpan Catatan
            </button>
        </div>
        <div class="card-body">
            <textarea class="form-control" id="note-input-dashboard_eks_idle" rows="4" placeholder="Tuliskan telaah pimpinan terkait tindak lanjut eks BMN idle...">{{ $notes['dashboard_eks_idle'] ?? "1. Optimalisasi BMN Eks BMN Idle sebanyak 16 NUP pada KPKNL Palembang terus didorong melalui skema PSP dan Penghapusan.\n2. Tiga NUP dalam tahap PSP pada Kementerian Pertahanan telah terbit Keputusan Menteri Keuangan.\n3. Aset bongkaran pada KPKNL Palembang telah diproses tindak lanjut penjualan/penghapusan." }}</textarea>
        </div>
    </div>
</div>


<!-- ========================================================================= -->
<!-- 2. TAB 2: BMN TERINDIKASI IDLE (KPKNL PALEMBANG)                          -->
<!-- ========================================================================= -->
<div class="dashboard-tab-pane d-none" id="tab-terindikasi">
    <div class="d-flex justify-content-between align-items-center mb-4 text-white">
        <div class="col-12">
            <h2 class="mb-1 text-white fw-bold">Executive Dashboard — BMN Terindikasi Idle</h2>
            <p class="mb-0 text-white-50">Posisi per {{ date('d F Y') }} &bull; Terfilter: <strong>KPKNL Palembang ({{ $totalPopulasi }} NUP)</strong></p>
        </div>
    </div>

    <!-- 5 Top KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col">
            <div class="card interactive-card shadow-sm border-0 h-100 border-start border-4 border-primary" onclick="openDrilldown('potensi', 'all', 'Seluruh Populasi BMN Terindikasi Idle', '256 Aset Tercatat')" title="Klik untuk rincian 256 aset">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem;">POPULASI TERCATAT</small>
                        <span class="interactive-badge-hint"><i class="fas fa-arrow-up-right-from-square"></i></span>
                    </div>
                    <h3 class="fw-bold text-dark mb-0">{{ $totalPopulasi }} <span class="fs-6 text-muted fw-normal">NUP</span></h3>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card interactive-card shadow-sm border-0 h-100 border-start border-4 border-success" onclick="openDrilldown('potensi', 'sudah_menjawab', 'BMN Sudah Menjawab Klarifikasi', '256 Aset')" title="Klik untuk rincian aset sudah menjawab">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-uppercase text-success fw-bold" style="font-size: 0.7rem;">SUDAH MENJAWAB</small>
                        <span class="interactive-badge-hint"><i class="fas fa-arrow-up-right-from-square"></i></span>
                    </div>
                    <h3 class="fw-bold text-success mb-0">{{ $sudahMenjawab }} <span class="fs-6 text-muted fw-normal">NUP</span></h3>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card interactive-card shadow-sm border-0 h-100 border-start border-4 border-warning" onclick="openDrilldown('potensi', 'menunggu', 'BMN Menunggu Jawaban Klarifikasi', '0 Aset')" title="Klik untuk rincian aset menunggu jawaban">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-uppercase text-warning fw-bold" style="font-size: 0.7rem;">MENUNGGU JAWABAN</small>
                        <span class="interactive-badge-hint"><i class="fas fa-arrow-up-right-from-square"></i></span>
                    </div>
                    <h3 class="fw-bold text-warning mb-0">{{ $menungguJawaban }} <span class="fs-6 text-muted fw-normal">NUP</span></h3>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card interactive-card shadow-sm border-0 h-100 border-start border-4 border-danger" onclick="openDrilldown('potensi', 'belum_klarifikasi', 'BMN Belum Diklarifikasi', '0 Aset')" title="Klik untuk rincian aset belum diklarifikasi">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-uppercase text-danger fw-bold" style="font-size: 0.7rem;">BELUM DIKLARIFIKASI</small>
                        <span class="interactive-badge-hint"><i class="fas fa-arrow-up-right-from-square"></i></span>
                    </div>
                    <h3 class="fw-bold text-danger mb-0">{{ $belumKlarifikasi }} <span class="fs-6 text-muted fw-normal">NUP</span></h3>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card interactive-card shadow-sm border-0 h-100 border-start border-4 border-warning" onclick="openDrilldown('potensi', 'potensi_19', 'Potensi Menjadi BMN Idle Lingkup Kemenkeu', '19 Aset')" title="Klik untuk rincian 19 aset potensi idle">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-uppercase text-warning-emphasis fw-bold" style="font-size: 0.7rem;">POTENSI IDLE</small>
                        <span class="interactive-badge-hint"><i class="fas fa-arrow-up-right-from-square"></i></span>
                    </div>
                    <h3 class="fw-bold text-warning mb-0">{{ $potensiIdleCount }} <span class="fs-6 text-muted fw-normal">NUP</span></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Donut Chart & Breakdown Card (Full Width - Bar Chart se-SJB Removed) -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <div class="fw-bold text-dark"><i class="fas fa-chart-pie text-primary me-2"></i> Komposisi Tahap Klarifikasi BMN Terindikasi Idle (KPKNL Palembang)</div>
                    <span class="badge bg-primary px-3 py-1 cursor-pointer" onclick="openDrilldown('potensi', 'all', 'Seluruh Komposisi Tahap Klarifikasi', '{{ $totalPopulasi }} NUP')">Total {{ $komposisiTahap['Pemantauan'] + $komposisiTahap['Penelusuran'] + $komposisiTahap['Penelitian'] }} NUP</span>
                </div>
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-lg-5 text-center mb-3 mb-lg-0" style="height: 280px;">
                            <canvas id="chartKomposisiKlarifikasi" class="chart-interactive"></canvas>
                        </div>
                        <div class="col-lg-7">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="p-3 rounded-3 bg-success-subtle border border-success-subtle h-100 shadow-sm interactive-card" onclick="openDrilldown('potensi', 'pemantauan', 'BMN Terindikasi Idle - Tahap Pemantauan', '198 Aset')" title="Klik rincian 198 aset pemantauan">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <small class="fw-bold text-success text-uppercase d-block"><i class="fas fa-eye me-1"></i> Pemantauan</small>
                                            <i class="fas fa-arrow-up-right-from-square text-success" style="font-size: 0.7rem;"></i>
                                        </div>
                                        <div class="h3 fw-bold text-success mb-1">{{ $komposisiTahap['Pemantauan'] }} <small class="fs-6 text-muted">NUP</small></div>
                                        <small class="text-muted" style="font-size: 0.75rem;">Aset dalam monitoring berkala hingga batas waktu Semester I 2027.</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-3 rounded-3 bg-warning-subtle border border-warning-subtle h-100 shadow-sm interactive-card" onclick="openDrilldown('potensi', 'penelusuran', 'BMN Terindikasi Idle - Tahap Penelusuran', '58 Aset')" title="Klik rincian 58 aset penelusuran">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <small class="fw-bold text-warning-emphasis text-uppercase d-block"><i class="fas fa-route me-1"></i> Penelusuran</small>
                                            <i class="fas fa-arrow-up-right-from-square text-warning" style="font-size: 0.7rem;"></i>
                                        </div>
                                        <div class="h3 fw-bold text-warning mb-1">{{ $komposisiTahap['Penelusuran'] }} <small class="fs-6 text-muted">NUP</small></div>
                                        <small class="text-muted" style="font-size: 0.75rem;">Konfirmasi lapangan terkait fisik & pemanfaatan satker.</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-3 rounded-3 bg-danger-subtle border border-danger-subtle h-100 shadow-sm interactive-card" onclick="openDrilldown('potensi', 'penelitian', 'BMN Terindikasi Idle - Tahap Penelitian', '0 Aset')" title="Klik rincian aset penelitian">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <small class="fw-bold text-danger text-uppercase d-block"><i class="fas fa-magnifying-glass me-1"></i> Penelitian</small>
                                            <i class="fas fa-arrow-up-right-from-square text-danger" style="font-size: 0.7rem;"></i>
                                        </div>
                                        <div class="h3 fw-bold text-danger mb-1">{{ $komposisiTahap['Penelitian'] }} <small class="fs-6 text-muted">NUP</small></div>
                                        <small class="text-muted" style="font-size: 0.75rem;">Penelitian mendalam dokumen hukum dan pemenuhan SBSK.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- ========================================================================= -->
<!-- 3. TAB 3: MATRIKS CAPAIAN PER KPKNL                                      -->
<!-- ========================================================================= -->
<div class="dashboard-tab-pane d-none" id="tab-matriks">
    <div class="d-flex justify-content-between align-items-center mb-4 text-white">
        <div>
            <h2 class="mb-1 text-white fw-bold">Matriks Capaian KPKNL Palembang</h2>
            <p class="mb-0 text-white-50">Capaian Klarifikasi dan Sebaran Tahapan Tindak Lanjut Wilayah Kerja KPKNL Palembang</p>
        </div>
    </div>



    <!-- Matriks Table Card -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <div class="fw-bold text-dark"><i class="fas fa-table text-primary me-2"></i> Matriks Capaian Klarifikasi KPKNL Palembang</div>
            <span class="badge bg-primary px-3 py-1">Tahun 2026</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle mb-0 text-center" id="tableMatriksCapaian">
                <thead class="table-primary text-primary-emphasis fw-bold">
                    <tr>
                        <th class="text-start ps-3 py-3">KPKNL</th>
                        <th>Target</th>
                        <th>Sudah Klarifikasi</th>
                        <th>Belum Klarifikasi</th>
                        <th>Menunggu Jawaban</th>
                        <th>Penelusuran</th>
                        <th>Pemantauan</th>
                        <th>Penelitian</th>
                        <th class="bg-warning-subtle text-warning-emphasis">Potensi BMN Idle</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totTarget = 0; $totSudah = 0; $totBelum = 0; $totTunggu = 0;
                        $totTelusur = 0; $totPantau = 0; $totTeliti = 0; $totPotensi = 0;
                    @endphp
                    @foreach($matriksKPKNL as $m)
                    @php
                        $totTarget += $m['target'];
                        $totSudah += $m['sudah_klarifikasi'];
                        $totBelum += $m['belum_klarifikasi'];
                        $totTunggu += $m['menunggu_jawaban'];
                        $totTelusur += $m['penelusuran'];
                        $totPantau += $m['pemantauan'];
                        $totTeliti += $m['penelitian'];
                        $totPotensi += $m['potensi_idle'];
                        $isPalembang = str_contains($m['kpknl'], 'Palembang');
                    @endphp
                    <tr class="matriks-row {{ $isPalembang ? 'table-warning fw-bold' : '' }}" data-kpknl="{{ $m['kpknl'] }}" data-target="{{ $m['target'] }}" data-sudah="{{ $m['sudah_klarifikasi'] }}" data-belum="{{ $m['belum_klarifikasi'] }}" data-potensi="{{ $m['potensi_idle'] }}">
                        <td class="text-start ps-3 {{ $isPalembang ? 'text-primary' : '' }}">
                            @if($isPalembang) <i class="fas fa-star text-warning me-1"></i> @endif
                            {{ $m['kpknl'] }}
                        </td>
                        <td class="fw-semibold">{{ $m['target'] }}</td>
                        <td class="fw-semibold text-success">{{ $m['sudah_klarifikasi'] }}</td>
                        <td class="text-muted">{{ $m['belum_klarifikasi'] }}</td>
                        <td class="text-muted">{{ $m['menunggu_jawaban'] }}</td>
                        <td class="fw-semibold text-warning">{{ $m['penelusuran'] }}</td>
                        <td class="fw-semibold text-success">{{ $m['pemantauan'] }}</td>
                        <td class="fw-semibold text-danger">{{ $m['penelitian'] }}</td>
                        <td class="fw-bold text-warning-emphasis bg-warning-subtle">{{ $m['potensi_idle'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-dark fw-bold" id="matriks-footer-total">
                    <tr>
                        <td class="text-start ps-3">TOTAL KPKNL PALEMBANG</td>
                        <td>{{ $totTarget }}</td>
                        <td>{{ $totSudah }}</td>
                        <td>{{ $totBelum }}</td>
                        <td>{{ $totTunggu }}</td>
                        <td>{{ $totTelusur }}</td>
                        <td>{{ $totPantau }}</td>
                        <td>{{ $totTeliti }}</td>
                        <td class="bg-warning text-dark">{{ $totPotensi }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- 4. Summary KPI Cards Dinamis (#tab-matriks > div:nth-of-type(4)) -->
    <div class="row g-3">
        <div class="col-md-3">
            <div class="card interactive-card shadow-sm border-0 h-100 border-start border-4 border-primary" onclick="openDrilldown('potensi', 'all', 'Target Populasi BMN Terindikasi Idle (KPKNL Palembang)', '256 Aset')" title="Klik rincian target populasi">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem;">Target Tahun Berjalan</small>
                        <span class="interactive-badge-hint"><i class="fas fa-arrow-up-right-from-square"></i></span>
                    </div>
                    <h3 class="fw-bold text-dark mb-0" id="kpi-matriks-target">{{ $totalPopulasi }} <span class="fs-6 text-muted fw-normal">NUP</span></h3>
                    <small class="text-muted" id="sub-matriks-target">KPKNL Palembang</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card interactive-card shadow-sm border-0 h-100 border-start border-4 border-success" onclick="openDrilldown('potensi', 'sudah_menjawab', 'BMN Sudah Diklarifikasi (KPKNL Palembang)', '256 Aset')" title="Klik rincian aset sudah diklarifikasi">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-uppercase text-success fw-bold" style="font-size: 0.7rem;">Sudah Diklarifikasi</small>
                        <span class="interactive-badge-hint"><i class="fas fa-arrow-up-right-from-square"></i></span>
                    </div>
                    <h3 class="fw-bold text-success mb-0" id="kpi-matriks-sudah">{{ $sudahMenjawab }} <span class="fs-6 text-muted fw-normal">NUP</span></h3>
                    <small class="text-success" id="sub-matriks-sudah">{{ $totalPopulasi > 0 ? round(($sudahMenjawab / $totalPopulasi) * 100) : 0 }}% Tercapai</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card interactive-card shadow-sm border-0 h-100 border-start border-4 border-danger" onclick="openDrilldown('potensi', 'belum_klarifikasi', 'BMN Belum Diklarifikasi (KPKNL Palembang)', '0 Aset')" title="Klik rincian aset belum diklarifikasi">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-uppercase text-danger fw-bold" style="font-size: 0.7rem;">Belum Diklarifikasi</small>
                        <span class="interactive-badge-hint"><i class="fas fa-arrow-up-right-from-square"></i></span>
                    </div>
                    <h3 class="fw-bold text-danger mb-0" id="kpi-matriks-belum">{{ $belumKlarifikasi }} <span class="fs-6 text-muted fw-normal">NUP</span></h3>
                    <small class="text-muted" id="sub-matriks-belum">Semua selesai klarifikasi</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card interactive-card shadow-sm border-0 h-100 border-start border-4 border-warning" onclick="openDrilldown('potensi', 'potensi_19', 'Potensi BMN Idle (KPKNL Palembang)', '19 Aset')" title="Klik rincian aset potensi idle">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-uppercase text-warning fw-bold" style="font-size: 0.7rem;">Potensi BMN Idle</small>
                        <span class="interactive-badge-hint"><i class="fas fa-arrow-up-right-from-square"></i></span>
                    </div>
                    <h3 class="fw-bold text-warning mb-0" id="kpi-matriks-potensi">{{ $potensiIdleCount }} <span class="fs-6 text-muted fw-normal">NUP</span></h3>
                    <small class="text-muted" id="sub-matriks-potensi">KPKNL Palembang</small>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- ========================================================================= -->
<!-- 4. TAB 4: PEMETAAN BMN PER KEMENTERIAN / LEMBAGA (KLASTER K/L)        -->
<!-- ========================================================================= -->
<div class="dashboard-tab-pane d-none" id="tab-pemetaan-kemenkeu">
    <div class="d-flex justify-content-between align-items-center mb-4 text-white">
        <div>
            <h2 class="mb-1 text-white fw-bold">Pemetaan Klaster BMN Terindikasi Idle per K/L</h2>
            <p class="mb-0 text-white-50">Sebaran Tahapan Klarifikasi dan Profil Satker &bull; Terfilter: <strong id="klaster-banner-kl-label">KEMENTERIAN KEUANGAN</strong> (KPKNL Palembang)</p>
        </div>
    </div>

    <!-- Filter Bar Interaktif Pemetaan K/L -->
    <div class="card shadow-sm mb-4">
        <div class="card-body py-3">
            <div class="row g-3 align-items-center">
                <div class="col-md-6">
                    <label class="form-label fw-bold small text-muted mb-1"><i class="fas fa-filter text-primary me-1"></i> Pilih Kementerian / Lembaga (K/L):</label>
                    <select class="form-select form-select-sm fw-semibold" id="select-klaster-kl" onchange="filterKlasterKl(this.value)">
                        <option value="ALL">-- Seluruh Kementerian / Lembaga (Semua Klaster: 256 NUP) --</option>
                        @foreach($listKementerianLembaga as $kl)
                            <option value="{{ $kl }}" {{ str_contains(strtoupper($kl), 'KEUANGAN') ? 'selected' : '' }}>{{ $kl }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 text-md-end">
                    <span class="badge bg-primary-subtle text-primary border px-3 py-2" id="klaster-filter-badge">
                        <i class="fas fa-building me-1"></i> Klaster Terpilih: <strong>KEMENTERIAN KEUANGAN (79 NUP)</strong>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Top 5 KPI Cards Dinamis -->
    <div class="row g-3 mb-4">
        <div class="col">
            <div class="card interactive-card shadow-sm border-0 h-100 border-start border-4 border-primary" onclick="drilldownCurrentKl('all')" title="Klik rincian seluruh BMN K/L terpilih">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem;" id="kpi-klaster-total-label">TOTAL KEMENKEU</small>
                        <span class="interactive-badge-hint"><i class="fas fa-arrow-up-right-from-square"></i></span>
                    </div>
                    <h3 class="fw-bold text-dark mb-0" id="kpi-klaster-total">{{ $totalKemenkeu }} <span class="fs-6 text-muted fw-normal">NUP</span></h3>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card interactive-card shadow-sm border-0 h-100 border-start border-4 border-success" onclick="drilldownCurrentKl('penelitian')" title="Klik rincian tahap penelitian">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-uppercase text-success fw-bold" style="font-size: 0.7rem;">PENELITIAN</small>
                        <span class="interactive-badge-hint"><i class="fas fa-arrow-up-right-from-square"></i></span>
                    </div>
                    <h3 class="fw-bold text-success mb-0" id="kpi-klaster-penelitian">{{ $kemenkeuPenelitian }} <span class="fs-6 text-muted fw-normal">NUP</span></h3>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card interactive-card shadow-sm border-0 h-100 border-start border-4 border-info" onclick="drilldownCurrentKl('pemantauan')" title="Klik rincian aset pemantauan">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-uppercase text-info fw-bold" style="font-size: 0.7rem;">PEMANTAUAN</small>
                        <span class="interactive-badge-hint"><i class="fas fa-arrow-up-right-from-square"></i></span>
                    </div>
                    <h3 class="fw-bold text-info mb-0" id="kpi-klaster-pemantauan">{{ $kemenkeuPemantauan }} <span class="fs-6 text-muted fw-normal">NUP</span></h3>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card interactive-card shadow-sm border-0 h-100 border-start border-4 border-warning" onclick="drilldownCurrentKl('penelusuran')" title="Klik rincian aset penelusuran">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-uppercase text-warning fw-bold" style="font-size: 0.7rem;">PENELUSURAN</small>
                        <span class="interactive-badge-hint"><i class="fas fa-arrow-up-right-from-square"></i></span>
                    </div>
                    <h3 class="fw-bold text-warning mb-0" id="kpi-klaster-penelusuran">{{ $kemenkeuPenelusuran }} <span class="fs-6 text-muted fw-normal">NUP</span></h3>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card interactive-card shadow-sm border-0 h-100 border-start border-4 border-warning" onclick="drilldownCurrentKl('potensi')" title="Klik rincian aset potensi idle">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-uppercase text-warning-emphasis fw-bold" style="font-size: 0.7rem;">POTENSI IDLE</small>
                        <span class="interactive-badge-hint"><i class="fas fa-arrow-up-right-from-square"></i></span>
                    </div>
                    <h3 class="fw-bold text-warning mb-0" id="kpi-klaster-potensi">{{ $potensiCount }} <span class="fs-6 text-muted fw-normal">NUP</span></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Sebaran Table & Donut Eselon I / Klaster / Satker -->
    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header bg-white py-3 border-bottom">
                    <div class="fw-bold text-dark"><i class="fas fa-table-list text-primary me-2"></i> <span id="table-klaster-card-title">Sebaran Tahapan Tindak Lanjut K/L</span></div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0 text-center">
                        <thead class="table-primary text-primary-emphasis fw-bold">
                            <tr>
                                <th class="text-start ps-3">Unit / Wilayah</th>
                                <th>Total (NUP)</th>
                                <th>Penelusuran</th>
                                <th>Pemantauan</th>
                                <th>Penelitian</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-start ps-3 fw-bold text-primary" id="td-klaster-name">KPKNL Palembang (Kemenkeu)</td>
                                <td class="fw-bold cursor-pointer text-decoration-underline" id="td-klaster-total" onclick="drilldownCurrentKl('all')">{{ $totalKemenkeu }}</td>
                                <td class="text-warning fw-semibold cursor-pointer text-decoration-underline" id="td-klaster-penelusuran" onclick="drilldownCurrentKl('penelusuran')">{{ $kemenkeuPenelusuran }}</td>
                                <td class="text-info fw-semibold cursor-pointer text-decoration-underline" id="td-klaster-pemantauan" onclick="drilldownCurrentKl('pemantauan')">{{ $kemenkeuPemantauan }}</td>
                                <td class="text-success fw-semibold cursor-pointer text-decoration-underline" id="td-klaster-penelitian" onclick="drilldownCurrentKl('penelitian')">{{ $kemenkeuPenelitian }}</td>
                            </tr>
                        </tbody>
                        <tfoot class="table-dark fw-bold">
                            <tr>
                                <td class="text-start ps-3" id="table-klaster-footer-label">TOTAL KEMENKEU</td>
                                <td class="cursor-pointer text-warning" id="td-klaster-footer-total" onclick="drilldownCurrentKl('all')">{{ $totalKemenkeu }}</td>
                                <td class="cursor-pointer text-warning" id="td-klaster-footer-penelusuran" onclick="drilldownCurrentKl('penelusuran')">{{ $kemenkeuPenelusuran }}</td>
                                <td class="cursor-pointer text-info" id="td-klaster-footer-pemantauan" onclick="drilldownCurrentKl('pemantauan')">{{ $kemenkeuPemantauan }}</td>
                                <td class="cursor-pointer text-success" id="td-klaster-footer-penelitian" onclick="drilldownCurrentKl('penelitian')">{{ $kemenkeuPenelitian }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="p-3 border-top bg-light">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold small text-muted"><i class="fas fa-note-sticky text-success me-1"></i> Catatan Pimpinan</span>
                        <button type="button" class="btn btn-sm btn-primary py-0 shadow-sm" onclick="saveExecutiveNote('pemetaan_kemenkeu')">Simpan</button>
                    </div>
                    <textarea class="form-control form-control-sm" id="note-input-pemetaan_kemenkeu" rows="3">{{ $notes['pemetaan_kemenkeu'] ?? "• Mayoritas aset terindikasi idle lingkup Kemenkeu berada pada unit DJPb dan DJP berupa Rumah Negara Golongan II.\n• Sebanyak 51 NUP dalam tahap Pemantauan rencana penggunaan aktif." }}</textarea>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <div class="fw-bold text-dark" id="chart-klaster-title"><i class="fas fa-chart-pie text-primary me-2"></i> Komposisi Unit Eselon I (Palembang)</div>
                    <span class="badge bg-light text-muted border" id="chart-klaster-badge">Kemenkeu</span>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-7" style="height: 270px;">
                            <canvas id="chartEselon1Kemenkeu" class="chart-interactive"></canvas>
                        </div>
                        <div class="col-5" id="chart-klaster-legend-container">
                            <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded-3 border-start border-4 border-primary interactive-card" onclick="openDrilldown('potensi', 'kemenkeu_eselon_djp', 'BMN Kemenkeu - Ditjen Pajak (DJP)', '{{ $eselon1Counts['DJP'] }} Aset')" title="Klik rincian DJP">
                                <span class="fw-semibold">DJP</span>
                                <span class="h5 fw-bold text-primary mb-0">{{ $eselon1Counts['DJP'] }} <small class="fs-6 text-muted">NUP</small></span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded-3 border-start border-4 border-success interactive-card" onclick="openDrilldown('potensi', 'kemenkeu_eselon_djbc', 'BMN Kemenkeu - Ditjen Bea Cukai (DJBC)', '{{ $eselon1Counts['DJBC'] }} Aset')" title="Klik rincian DJBC">
                                <span class="fw-semibold">DJBC</span>
                                <span class="h5 fw-bold text-success mb-0">{{ $eselon1Counts['DJBC'] }} <small class="fs-6 text-muted">NUP</small></span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded-3 border-start border-4 border-info interactive-card" onclick="openDrilldown('potensi', 'kemenkeu_eselon_djpb', 'BMN Kemenkeu - Ditjen Perbendaharaan (DJPb)', '{{ $eselon1Counts['DJPb'] }} Aset')" title="Klik rincian DJPb">
                                <span class="fw-semibold">DJPb</span>
                                <span class="h5 fw-bold text-info mb-0">{{ $eselon1Counts['DJPb'] }} <small class="fs-6 text-muted">NUP</small></span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded-3 border-start border-4 border-warning interactive-card" onclick="openDrilldown('potensi', 'kemenkeu_eselon_djkn', 'BMN Kemenkeu - Ditjen Kekayaan Negara (DJKN)', '{{ $eselon1Counts['DJKN'] }} Aset')" title="Klik rincian DJKN">
                                <span class="fw-semibold">DJKN</span>
                                <span class="h5 fw-bold text-warning mb-0">{{ $eselon1Counts['DJKN'] }} <small class="fs-6 text-muted">NUP</small></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Jenis Barang Cards Dinamis -->
    <div class="row g-3" id="row-jenis-barang-kl">
        <div class="col-md-3">
            <div class="card interactive-card shadow-sm border-0 h-100 border-start border-4 border-success" onclick="drilldownBarang(0)" id="card-jb-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem;" id="jb-0-title">Rumah Negara</small>
                        <span class="interactive-badge-hint"><i class="fas fa-arrow-up-right-from-square"></i></span>
                    </div>
                    <h3 class="fw-bold text-success mb-0" id="jb-0-val">{{ $jenisBarangCounts['Rumah Negara'] }} <span class="fs-6 text-muted">NUP</span></h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card interactive-card shadow-sm border-0 h-100 border-start border-4 border-primary" onclick="drilldownBarang(1)" id="card-jb-1">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem;" id="jb-1-title">Bangunan Gedung Kantor</small>
                        <span class="interactive-badge-hint"><i class="fas fa-arrow-up-right-from-square"></i></span>
                    </div>
                    <h3 class="fw-bold text-primary mb-0" id="jb-1-val">{{ $jenisBarangCounts['Bangunan Gedung Kantor'] }} <span class="fs-6 text-muted">NUP</span></h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card interactive-card shadow-sm border-0 h-100 border-start border-4 border-info" onclick="drilldownBarang(2)" id="card-jb-2">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem;" id="jb-2-title">Tanah Rumah Negara</small>
                        <span class="interactive-badge-hint"><i class="fas fa-arrow-up-right-from-square"></i></span>
                    </div>
                    <h3 class="fw-bold text-info mb-0" id="jb-2-val">{{ $jenisBarangCounts['Tanah Rumah Negara'] }} <span class="fs-6 text-muted">NUP</span></h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card interactive-card shadow-sm border-0 h-100 border-start border-4 border-warning" onclick="drilldownBarang(3)" id="card-jb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem;" id="jb-3-title">Tanah Bangunan Kantor</small>
                        <span class="interactive-badge-hint"><i class="fas fa-arrow-up-right-from-square"></i></span>
                    </div>
                    <h3 class="fw-bold text-warning mb-0" id="jb-3-val">{{ $jenisBarangCounts['Tanah Bangunan Kantor'] }} <span class="fs-6 text-muted">NUP</span></h3>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- ========================================================================= -->
<!-- 5. TAB 5: POTENSI MENJADI BMN IDLE PER K/L                                -->
<!-- ========================================================================= -->
<div class="dashboard-tab-pane d-none" id="tab-potensi-kemenkeu">
    <div class="d-flex justify-content-between align-items-center mb-4 text-white">
        <div>
            <h2 class="mb-1 text-white fw-bold">Potensi Menjadi BMN Idle per Kementerian/Lembaga</h2>
            <p class="mb-0 text-white-50">Daftar Rincian Aset Berpotensi Idle &bull; Terfilter: <strong>KPKNL Palembang</strong></p>
        </div>
    </div>

    <!-- Filter Bar Interaktif Potensi K/L -->
    <div class="card shadow-sm mb-4">
        <div class="card-body py-3">
            <div class="row g-3 align-items-center">
                <div class="col-md-6">
                    <label class="form-label fw-bold small text-muted mb-1"><i class="fas fa-filter text-primary me-1"></i> Filter Tabel Potensi per Kementerian / Lembaga:</label>
                    <select class="form-select form-select-sm fw-semibold" id="select-potensi-kl" onchange="filterPotensiTableKl(this.value)">
                        <option value="ALL">-- Seluruh Kementerian / Lembaga (Semua Data) --</option>
                        @foreach($listKementerianLembaga as $kl)
                            <option value="{{ $kl }}" {{ str_contains(strtoupper($kl), 'KEUANGAN') ? 'selected' : '' }}>{{ $kl }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 text-md-end">
                    <span class="badge bg-warning-subtle text-warning-emphasis border px-3 py-2" id="potensi-filter-badge">
                        <i class="fas fa-building me-1"></i> Menampilkan: <strong>KEMENTERIAN KEUANGAN ({{ $potensiCount }} NUP)</strong>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- 3 KPI Cards -->
    <div class="row g-3 mb-4">
        <!-- Card 1: NUP Count -->
        <div class="col-md-4">
            <div class="card interactive-card shadow-sm border-0 h-100 border-start border-4 border-warning" onclick="openDrilldown('potensi', 'potensi_19', 'Rincian BMN Potensi Idle', '{{ $potensiCount }} Aset')" title="Klik rincian BMN Potensi Idle">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-uppercase text-warning fw-bold" style="font-size: 0.7rem;">BERPOTENSI MENJADI BMN IDLE</small>
                        <span class="interactive-badge-hint"><i class="fas fa-arrow-up-right-from-square"></i></span>
                    </div>
                    <div class="d-flex align-items-baseline mt-1">
                        <h2 class="fw-bold text-warning mb-0" id="kpi-potensi-count">{{ $potensiCount }}</h2>
                        <span class="fs-6 text-muted ms-2 fw-semibold">NUP</span>
                    </div>
                    <div class="small text-muted mt-2" id="kpi-potensi-desc">Aset teridentifikasi berpotensi idle wilayah KPKNL Palembang.</div>
                </div>
            </div>
        </div>

        <!-- Card 2: Persentase Dinamis -->
        <div class="col-md-4">
            <div class="card interactive-card shadow-sm border-0 h-100 border-start border-4 border-primary" onclick="openDrilldown('potensi', 'potensi_19', 'Rincian BMN Potensi Idle', '{{ $potensiCount }} Aset')" title="Klik rincian persentase">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-uppercase text-primary fw-bold" style="font-size: 0.7rem;">PERSENTASE DARI POPULASI</small>
                        <span class="interactive-badge-hint"><i class="fas fa-arrow-up-right-from-square"></i></span>
                    </div>
                    <div class="d-flex align-items-baseline mt-1">
                        <h2 class="fw-bold text-primary mb-0" id="kpi-potensi-percentage">{{ $potensiPersentase }}%</h2>
                        <span class="fs-6 text-muted ms-2 fw-semibold">Populasi</span>
                    </div>
                    <div class="mt-2">
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-primary" id="kpi-potensi-progress-bar" role="progressbar" style="width: {{ min(100, max(5, $potensiPersentase)) }}%;" aria-valuenow="{{ $potensiPersentase }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="d-flex justify-content-between small text-muted mt-1" style="font-size: 0.7rem;">
                            <span id="kpi-potensi-progress-label"><strong>{{ $potensiCount }} NUP</strong> dari {{ $totalKemenkeu }} Kemenkeu</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3: Langkah Tindak Lanjut Dinamis -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100 border-start border-4 border-success">
                <div class="card-body p-3">
                    <small class="text-uppercase text-success fw-bold" style="font-size: 0.7rem;">LANGKAH TINDAK LANJUT</small>
                    
                    <div class="mt-2 d-flex flex-column gap-2">
                        <!-- Section 1: Pemantauan -->
                        <div class="d-flex justify-content-between align-items-center p-2 rounded-2 bg-success-subtle border border-success-subtle interactive-card" onclick="openDrilldown('potensi', 'potensi_19_pemantauan', 'Potensi BMN Idle - Tahap Pemantauan', '{{ $potensiTindakLanjutCounts["Pemantauan"] ?? 0 }} Aset')" title="Klik rincian tahap pemantauan">
                            <div>
                                <span class="fw-bold text-success small d-block" style="font-size: 0.78rem;"><i class="fas fa-eye me-1"></i> Pemantauan</span>
                                <span class="text-muted" style="font-size: 0.68rem;">Batas waktu: <strong>Akhir Semester I 2027</strong></span>
                            </div>
                            <span class="badge bg-success fs-6 fw-bold" id="kpi-potensi-tl-pemantauan">{{ $potensiTindakLanjutCounts['Pemantauan'] ?? 0 }} NUP</span>
                        </div>

                        <!-- Section 2: Penelitian -->
                        <div class="d-flex justify-content-between align-items-center p-2 rounded-2 bg-danger-subtle border border-danger-subtle interactive-card" onclick="openDrilldown('potensi', 'kemenkeu_penelitian', 'Potensi BMN Idle - Tahap Penelitian', '{{ $potensiTindakLanjutCounts["Penelitian"] ?? 0 }} Aset')" title="Klik rincian tahap penelitian">
                            <div>
                                <span class="fw-bold text-danger small d-block" style="font-size: 0.78rem;"><i class="fas fa-magnifying-glass me-1"></i> Penelitian</span>
                                <span class="text-muted" style="font-size: 0.68rem;">Klarifikasi dokumen & SBSK</span>
                            </div>
                            <span class="badge bg-danger fs-6 fw-bold" id="kpi-potensi-tl-penelitian">{{ $potensiTindakLanjutCounts['Penelitian'] ?? 0 }} NUP</span>
                        </div>

                        <!-- Section 3: Penelusuran -->
                        <div class="d-flex justify-content-between align-items-center p-2 rounded-2 bg-warning-subtle border border-warning-subtle interactive-card" onclick="openDrilldown('potensi', 'potensi_19_penelusuran', 'Potensi BMN Idle - Tahap Penelusuran', '{{ $potensiTindakLanjutCounts["Penelusuran"] ?? 0 }} Aset')" title="Klik rincian aset penelusuran">
                            <div>
                                <span class="fw-bold text-warning-emphasis small d-block" style="font-size: 0.78rem;"><i class="fas fa-route me-1"></i> Penelusuran</span>
                                <span class="text-muted" style="font-size: 0.68rem;">Fisik & pemanfaatan satker</span>
                            </div>
                            <span class="badge bg-warning text-dark fs-6 fw-bold" id="kpi-potensi-tl-penelusuran">{{ $potensiTindakLanjutCounts['Penelusuran'] ?? 0 }} NUP</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Explanatory Breakdown Box -->
    <div class="card shadow-sm border-0 mb-4 bg-light">
        <div class="card-body p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-primary text-white rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                    <i class="fas fa-info"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-dark mb-0">Penjelasan Dasar Perhitungan Populasi:</h6>
                    <p class="small text-muted mb-0" id="potensi-explanation-text">
                        Angka <strong>{{ $potensiPersentase }}%</strong> diperoleh dari perbandingan <strong>{{ $potensiCount }} NUP Potensi Idle</strong> terhadap <strong>{{ $totalKemenkeu }} NUP Total BMN Terindikasi Idle Lingkup Kementerian Keuangan</strong> pada KPKNL Palembang. Apabila dibandingkan terhadap seluruh populasi K/L di wilayah kerja KPKNL Palembang ({{ $totalPopulasi }} NUP), maka aset ini mewakili <strong>{{ $potensiPersentaseTotal }}%</strong> dari total keseluruhan populasi.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- DataTables Table dengan DUAL SCROLLBAR & OPSI JUMLAH BARIS & KOLOM KLASTER -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <div class="fw-bold text-dark" id="potensi-table-title"><i class="fas fa-list-check text-primary me-2"></i> Rincian {{ $potensiCount }} Aset Kemenkeu Berpotensi Idle (KPKNL Palembang)</div>
            <span class="badge bg-warning text-dark px-3 py-1" id="potensi-table-badge">{{ $potensiCount }} NUP Terpilih</span>
        </div>
        <div class="card-body">
            <!-- Top Scrollbar Potensi 19 -->
            <div class="top-scrollbar-container mb-2" id="topScrollContainerPotensi19">
                <div id="topScrollDummyPotensi19" style="height: 1px;"></div>
            </div>

            <!-- Table Wrapper Potensi 19 -->
            <div class="table-responsive" id="tableWrapperPotensi19">
                <table class="table table-hover table-striped table-bordered align-middle w-100" id="tablePotensi19">
                    <thead class="table-primary text-primary-emphasis fw-bold small text-uppercase">
                        <tr>
                            <th style="width: 40px;">No</th>
                            <th>Kementerian/Lembaga</th>
                            <th>Nama Satker</th>
                            <th>Kode Barang</th>
                            <th>NUP</th>
                            <th>Jenis Barang</th>
                            <th>Nama Barang</th>
                            <th class="text-center">Klaster</th>
                            <th>Status SBSK</th>
                            <th>Tindak Lanjut</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        @foreach($potensi19List as $idx => $item)
                        <tr>
                            <td class="text-center fw-bold">{{ $idx + 1 }}</td>
                            <td>{{ $item->kementerian_lembaga ?: 'KEMENTERIAN KEUANGAN' }}</td>
                            <td class="fw-semibold text-primary">{{ $item->nama_satker }}</td>
                            <td><code>{{ $item->kode_barang }}</code></td>
                            <td class="text-center fw-bold">{{ $item->nup }}</td>
                            <td><span class="badge bg-secondary-subtle text-secondary border">{{ $item->kelompok_barang ?: 'Rumah Negara' }}</span></td>
                            <td>{{ $item->nama_barang }}</td>
                            <td class="text-center"><span class="badge bg-dark-subtle text-dark border fw-bold">{{ $item->klasterisasi ?: 'K1' }}</span></td>
                            <td><span class="badge bg-warning text-dark">{{ $item->hasil_pengukuran_sbsk ?: 'Tidak Optimalisasi' }}</span></td>
                            <td><span class="badge bg-info text-dark">{{ $item->status_tindak_lanjut ?: 'Pemantauan' }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


<!-- ========================================================================= -->
<!-- 6. TAB 6: TABEL SUMBER LENGKAP - POTENSI IDLE                             -->
<!-- ========================================================================= -->
<div class="dashboard-tab-pane d-none" id="tab-source-potensi">
    <div class="d-flex justify-content-between align-items-center mb-4 text-white">
        <div class="col-md-7">
            <h2 class="mb-1 text-white fw-bold">Tabel Sumber: Potensi Idle (Lengkap)</h2>
            <p class="mb-0 text-white-50">Data lengkap dari Sheet 'potensi idle' Google Spreadsheet Online</p>
        </div>
        <!-- TOMBOL BUKA GOOGLE SPREADSHEET (Sesuai Poin 3) -->
        <div class="col-md-5 text-md-end d-flex align-items-center justify-content-md-end gap-2">
            <button type="button" onclick="openSpreadsheetLink('{{ $spreadsheetPotensiUrl }}')" class="btn btn-sm btn-success fw-semibold shadow-sm text-white">
                <i class="fas fa-file-excel me-1"></i> Buka Google Spreadsheet
            </button>
            <span class="badge bg-light text-dark fw-bold px-3 py-2">Total: {{ count($allPotensiRows) }} Baris</span>
        </div>
    </div>

    <!-- Filter Controls Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-bold small text-muted"><i class="fas fa-building me-1"></i> Lingkup Kementerian/Lembaga</label>
                    <select class="form-select form-select-sm" id="filter-potensi-kl">
                        <option value="">-- Semua Kementerian / Lembaga (13 K/L) --</option>
                        @foreach($listKementerianLembaga as $kl)
                            <option value="{{ $kl }}">{{ $kl }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold small text-muted"><i class="fas fa-filter me-1"></i> Status Tindak Lanjut</label>
                    <select class="form-select form-select-sm" id="filter-potensi-status">
                        <option value="">-- Semua Status --</option>
                        <option value="Pemantauan">Pemantauan</option>
                        <option value="Penelitian">Penelitian</option>
                        <option value="Penelusuran">Penelusuran</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <button type="button" class="btn btn-sm btn-outline-secondary w-100" onclick="resetPotensiFilters()">
                        <i class="fas fa-undo me-1"></i> Reset Filter
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Full Data Table Card dengan DUAL SCROLLBAR (Top & Bottom) & OPSI JUMLAH BARIS -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <!-- Top Scrollbar Potensi -->
            <div class="top-scrollbar-container mb-2" id="topScrollContainerPotensi">
                <div id="topScrollDummyPotensi" style="height: 1px;"></div>
            </div>

            <!-- Table Wrapper Potensi -->
            <div class="table-responsive" id="tableWrapperPotensi">
                <table class="table table-hover table-striped table-bordered align-middle w-100" id="tableSourcePotensi">
                    <thead class="table-primary text-primary-emphasis fw-bold small text-uppercase">
                        <tr>
                            <th style="min-width: 50px; width: 50px;" class="text-center">No</th>
                            <th style="min-width: 140px;">KPKNL</th>
                            <th style="min-width: 130px;">Kode Satker</th>
                            <th style="min-width: 250px;">Nama Satker</th>
                            <th style="min-width: 180px;">K/L</th>
                            <th style="min-width: 130px;">Kode Barang</th>
                            <th style="min-width: 60px;" class="text-center">NUP</th>
                            <th style="min-width: 220px;">Nama Barang</th>
                            <th style="min-width: 150px;">SBSK</th>
                            <th style="min-width: 280px;">Surat Klarifikasi</th>
                            <th style="min-width: 120px;">Tgl Klarifikasi</th>
                            <th style="min-width: 150px;">Status Klarifikasi</th>
                            <th style="min-width: 150px;">Tindak Lanjut</th>
                            <th style="min-width: 150px;">Status BMN Idle</th>
                            <th style="min-width: 80px;" class="text-center">Klaster</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        @foreach($allPotensiRows as $row)
                        <tr>
                            <td class="text-center fw-bold">{{ $row->row_no ?: $loop->iteration }}</td>
                            <td>{{ $row->kpknl }}</td>
                            <td><code>{{ $row->kode_satker }}</code></td>
                            <td class="fw-semibold text-primary" style="min-width: 250px; white-space: normal;">{{ $row->nama_satker }}</td>
                            <td><span class="badge bg-light text-dark border" style="white-space: normal;">{{ $row->kementerian_lembaga }}</span></td>
                            <td><code>{{ $row->kode_barang }}</code></td>
                            <td class="text-center fw-bold">{{ $row->nup }}</td>
                            <td style="min-width: 220px; white-space: normal;">{{ $row->nama_barang }}</td>
                            <td>
                                @if(str_contains($row->hasil_pengukuran_sbsk, 'Tidak'))
                                    <span class="badge bg-warning text-dark">{{ $row->hasil_pengukuran_sbsk }}</span>
                                @else
                                    <span class="badge bg-success">{{ $row->hasil_pengukuran_sbsk ?: '-' }}</span>
                                @endif
                            </td>
                            <td>
                                <div style="min-width: 280px; max-width: 420px; word-break: break-word; white-space: normal; line-height: 1.45;">
                                    {{ $row->surat_klarifikasi ?: '-' }}
                                </div>
                            </td>
                            <td style="white-space: nowrap;"><small>{{ $row->tanggal_klarifikasi ? $row->tanggal_klarifikasi->format('d/m/Y') : '-' }}</small></td>
                            <td>{{ $row->status_klarifikasi ?: '-' }}</td>
                            <td>
                                @if($row->status_tindak_lanjut === 'Pemantauan')
                                    <span class="badge bg-success">Pemantauan</span>
                                @elseif($row->status_tindak_lanjut === 'Penelitian')
                                    <span class="badge bg-danger">Penelitian</span>
                                @else
                                    <span class="badge bg-warning text-dark">{{ $row->status_tindak_lanjut ?: 'Penelusuran' }}</span>
                                @endif
                            </td>
                            <td>{{ $row->status_bmn_idle ?: 'Bukan BMN Idle' }}</td>
                            <td class="text-center"><span class="badge bg-secondary">{{ $row->klasterisasi ?: 'K1' }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


<!-- ========================================================================= -->
<!-- 7. TAB 7: TABEL SUMBER LENGKAP - EKS BMN IDLE                             -->
<!-- ========================================================================= -->
<div class="dashboard-tab-pane d-none" id="tab-source-eks">
    <div class="d-flex justify-content-between align-items-center mb-4 text-white">
        <div class="col-md-7">
            <h2 class="mb-1 text-white fw-bold">Tabel Sumber: BMN Eks BMN Idle (Lengkap)</h2>
            <p class="mb-0 text-white-50">Data lengkap hasil evaluasi IMPORTRANGE dari Sheet 'eks bmn idle'</p>
        </div>
        <!-- TOMBOL BUKA GOOGLE SPREADSHEET (Sesuai Poin 3) -->
        <div class="col-md-5 text-md-end d-flex align-items-center justify-content-md-end gap-2">
            <button type="button" onclick="openSpreadsheetLink('{{ $spreadsheetEksUrl }}')" class="btn btn-sm btn-success fw-semibold shadow-sm text-white">
                <i class="fas fa-file-excel me-1"></i> Buka Google Spreadsheet
            </button>
            <span class="badge bg-light text-dark fw-bold px-3 py-2">Total: {{ count($allEksRows) }} NUP Eks Idle</span>
        </div>
    </div>

    <!-- Filter Controls Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-bold small text-muted">Filter Wilayah KPKNL</label>
                    <select class="form-select form-select-sm" id="filter-eks-kpknl">
                        <option value="PALEMBANG" selected>KPKNL Palembang (Default)</option>
                        <option value="">-- Semua KPKNL --</option>
                        <option value="JAMBI">KPKNL Jambi</option>
                        <option value="LAHAT">KPKNL Lahat</option>
                        <option value="PANGKAL PINANG">KPKNL Pangkal Pinang</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold small text-muted">Filter Jenis Pengelolaan</label>
                    <select class="form-select form-select-sm" id="filter-eks-pengelolaan">
                        <option value="">-- Semua Jenis Pengelolaan --</option>
                        <option value="PSP">Penggunaan PSP</option>
                        <option value="Penghapusan">Penghapusan</option>
                        <option value="Pemanfaatan">Pemanfaatan</option>
                        <option value="Koreksi">Koreksi</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <button type="button" class="btn btn-sm btn-outline-secondary w-100" onclick="resetEksFilters()">
                        <i class="fas fa-undo me-1"></i> Reset Filter
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Full Eks Idle Table Card dengan DUAL SCROLLBAR & OPSI JUMLAH BARIS -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <!-- Top Scrollbar Eks -->
            <div class="top-scrollbar-container mb-2" id="topScrollContainerEks">
                <div id="topScrollDummyEks" style="height: 1px;"></div>
            </div>

            <!-- Table Wrapper Eks -->
            <div class="table-responsive" id="tableWrapperEks">
                <table class="table table-hover table-striped table-bordered align-middle w-100" id="tableSourceEks">
                    <thead class="table-primary text-primary-emphasis fw-bold small text-uppercase">
                        <tr>
                            <th style="min-width: 50px; width: 50px;" class="text-center">No</th>
                            <th style="min-width: 140px;">KPKNL</th>
                            <th style="min-width: 130px;">Kode Barang</th>
                            <th style="min-width: 220px;">Uraian Barang</th>
                            <th style="min-width: 60px;" class="text-center">NUP</th>
                            <th style="min-width: 100px;" class="text-end">Luas (m²)</th>
                            <th style="min-width: 170px;" class="text-end">Nilai Perolehan (Rp)</th>
                            <th style="min-width: 380px;">Alamat Lengkap</th>
                            <th style="min-width: 160px;">Jenis Tindak Lanjut</th>
                            <th style="min-width: 160px;">Jenis Pengelolaan</th>
                            <th style="min-width: 320px;">Nomor Surat / Naskah</th>
                            <th style="min-width: 120px;">Tgl Surat</th>
                            <th style="min-width: 130px;" class="text-center">Verifikasi Kanwil</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        @foreach($allEksRows as $row)
                        <tr>
                            <td class="text-center fw-bold">{{ $row->row_no ?: $loop->iteration }}</td>
                            <td>{{ $row->nama_kpknl }}</td>
                            <td><code>{{ $row->kode_barang }}</code></td>
                            <td class="fw-semibold text-primary" style="min-width: 220px; white-space: normal;">{{ $row->uraian_barang }}</td>
                            <td class="text-center fw-bold">{{ $row->nup }}</td>
                            <td class="text-end">{{ number_format($row->luas, 0, ',', '.') }}</td>
                            <td class="text-end fw-bold text-success" style="white-space: nowrap;">Rp {{ number_format($row->nilai_perolehan, 0, ',', '.') }}</td>
                            <td>
                                <div style="min-width: 380px; max-width: 550px; word-break: break-word; white-space: normal; line-height: 1.45;">
                                    {{ $row->alamat ?: '-' }}
                                </div>
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ $row->jenis_tindak_lanjut }}</span></td>
                            <td>
                                @if(str_contains($row->jenis_pengelolaan, 'PSP'))
                                    <span class="badge bg-primary">{{ $row->jenis_pengelolaan }}</span>
                                @elseif(str_contains($row->jenis_pengelolaan, 'Penghapusan'))
                                    <span class="badge bg-warning text-dark">{{ $row->jenis_pengelolaan }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ $row->jenis_pengelolaan }}</span>
                                @endif
                            </td>
                            <td>
                                <div style="min-width: 320px; max-width: 480px; word-break: break-word; white-space: normal; line-height: 1.45;">
                                    {{ $row->no_surat ?: '-' }}
                                </div>
                            </td>
                            <td style="white-space: nowrap;"><small>{{ $row->tanggal_surat ? $row->tanggal_surat->format('d/m/Y') : '-' }}</small></td>
                            <td class="text-center"><span class="badge bg-success-subtle text-success"><i class="fas fa-check"></i> {{ $row->verifikasi_kanwil }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


<!-- ========================================================================= -->
<!-- 8. TAB 8: LOG RIWAYAT AKTIVITAS & SINKRONISASI SPREADSHEET                -->
<!-- ========================================================================= -->
<div class="dashboard-tab-pane d-none" id="tab-sync-history">
    <div class="d-flex justify-content-between align-items-center mb-4 text-white">
        <div class="col-md-8">
            <h2 class="mb-1 text-white fw-bold">Riwayat Aktivitas & Sinkronisasi Sistem</h2>
            <p class="mb-0 text-white-50">Audit trail lengkap aktivitas: Sync Otomatis, Sync Manual, dan Wipe All Data</p>
        </div>
        <div class="col-md-4 text-md-end">
            <button type="button" class="btn btn-warning text-white fw-bold shadow-sm" onclick="triggerSync()">
                <i class="fas fa-arrows-rotate me-1"></i> Sinkronisasi Sekarang
            </button>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped table-bordered align-middle w-100" id="tableSyncLog">
                    <thead class="table-dark text-uppercase small">
                        <tr>
                            <th style="min-width: 170px;">Waktu Aktivitas</th>
                            <th style="min-width: 140px;">Jenis Aktivitas</th>
                            <th style="min-width: 100px;">Status</th>
                            <th style="min-width: 130px;">Pemicu / Aktor</th>
                            <th style="min-width: 180px;">Hasil Data Terproses</th>
                            <th style="min-width: 80px;" class="text-center">Durasi</th>
                            <th style="min-width: 200px;">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        @foreach(App\Models\SyncLog::latest()->take(50)->get() as $log)
                        <tr>
                            <td style="white-space: nowrap;">
                                <i class="fas fa-clock text-muted me-1"></i> {{ $log->created_at->isoFormat('D MMMM Y, HH:mm:ss') }} WIB
                            </td>
                            <td>
                                @if($log->source_type === 'SYNC_OTOMATIS' || stripos($log->triggered_by, 'Scheduler') !== false || stripos($log->triggered_by, 'Cron') !== false)
                                    <span class="badge bg-info text-dark border"><i class="fas fa-robot me-1"></i> Sync Otomatis</span>
                                @elseif($log->source_type === 'WIPE_ALL_DATA')
                                    <span class="badge bg-danger"><i class="fas fa-trash-can me-1"></i> Wipe All Data</span>
                                @elseif($log->source_type === 'SYNC_MANUAL' || $log->source_type === 'GOOGLE_SHEET_LIVE' || $log->source_type === 'LOCAL_FALLBACK')
                                    <span class="badge bg-primary"><i class="fas fa-hand-pointer me-1"></i> Sync Manual</span>
                                @else
                                    <span class="badge bg-secondary">{{ $log->source_type }}</span>
                                @endif
                            </td>
                            <td>
                                @if($log->status === 'SUCCESS')
                                    <span class="badge bg-success"><i class="fas fa-check me-1"></i> SUCCESS</span>
                                @else
                                    <span class="badge bg-danger"><i class="fas fa-times me-1"></i> FAILED</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <i class="fas fa-user-circle me-1 text-primary"></i> {{ $log->triggered_by ?: 'System' }}
                                </span>
                            </td>
                            <td>
                                @if($log->source_type === 'WIPE_ALL_DATA')
                                    <span class="text-danger fw-semibold"><i class="fas fa-broom me-1"></i> Database Dikosongkan</span>
                                @else
                                    <span><strong>{{ $log->potensi_synced }}</strong> Potensi &bull; <strong>{{ $log->eks_idle_synced }}</strong> Eks Idle</span>
                                @endif
                            </td>
                            <td class="text-center font-monospace">{{ $log->duration_seconds }}s</td>
                            <td style="white-space: normal; line-height: 1.4;">
                                <small class="text-muted">{{ $log->error_message ?: 'Sinkronisasi live Google Spreadsheet berhasil tanpa kendala.' }}</small>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


<!-- ========================================================================= -->
<!-- 9. TAB 9: PENGATURAN LINK GOOGLE SPREADSHEET (MENU BARU Sesuai Poin 2)    -->
<!-- ========================================================================= -->
<div class="dashboard-tab-pane d-none" id="tab-settings-spreadsheet">
    <div class="d-flex justify-content-between align-items-center mb-4 text-white">
        <div class="col-md-8">
            <h2 class="mb-1 text-white fw-bold">Pengaturan Sumber Google Spreadsheet</h2>
            <p class="mb-0 text-white-50">Kelola dan perbarui tautan spreadsheet online untuk sinkronisasi otomatis</p>
        </div>
        <div class="col-md-4 text-md-end">
            <button type="button" class="btn btn-warning text-white fw-bold shadow-sm" onclick="triggerSync()">
                <i class="fas fa-arrows-rotate me-1"></i> Sinkronisasi Sekarang
            </button>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Form Settings Card -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <div class="fw-bold text-dark"><i class="fas fa-sliders text-primary me-2"></i> Konfigurasi Tautan Google Sheet</div>
                    <span class="badge bg-primary-subtle text-primary">Live Database Config</span>
                </div>
                <div class="card-body p-4">
                    <form id="form-spreadsheet-settings" onsubmit="event.preventDefault(); saveSpreadsheetConfig();">
                        <!-- Setting 1: Potensi Idle Sheet -->
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark mb-1">
                                <i class="fas fa-table-list text-primary me-1"></i> 1. Link Spreadsheet Potensi Idle & Klarifikasi (Sheet: <code>potensi idle</code>)
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-link text-muted"></i></span>
                                <input type="url" class="form-control" id="input-spreadsheet-potensi" value="{{ $spreadsheetPotensiUrl }}" placeholder="https://docs.google.com/spreadsheets/d/...">
                                <button type="button" onclick="openSpreadsheetLink($('#input-spreadsheet-potensi').val())" class="btn btn-outline-success" title="Buka Spreadsheet di Tab Baru">
                                    <i class="fas fa-arrow-up-right-from-square me-1"></i> Buka
                                </button>
                            </div>
                            <small class="text-muted d-block mt-1">Digunakan untuk mengambil seluruh data klarifikasi Kanwil SJB, matriks capaian KPKNL, dan 19 NUP aset potensi idle.</small>
                        </div>

                        <!-- Setting 2: Eks BMN Idle Sheet -->
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark mb-1">
                                <i class="fas fa-boxes-stacked text-success me-1"></i> 2. Link Spreadsheet BMN Eks BMN Idle (Sheet: <code>eks bmn idle</code>)
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-link text-muted"></i></span>
                                <input type="url" class="form-control" id="input-spreadsheet-eks" value="{{ $spreadsheetEksUrl }}" placeholder="https://docs.google.com/spreadsheets/d/...">
                                <button type="button" onclick="openSpreadsheetLink($('#input-spreadsheet-eks').val())" class="btn btn-outline-success" title="Buka Spreadsheet di Tab Baru">
                                    <i class="fas fa-arrow-up-right-from-square me-1"></i> Buka
                                </button>
                            </div>
                            <small class="text-muted d-block mt-1">Digunakan untuk mengambil data tindak lanjut BMN eks idle (16 NUP Palembang, nilai perolehan kolom H, usulan PSP, dan penghapusan).</small>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                            <button type="submit" class="btn btn-primary px-4 shadow-sm">
                                <i class="fas fa-save me-1"></i> Simpan Pengaturan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Info Guide Card -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 bg-light h-100">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-dark mb-3"><i class="fas fa-circle-info text-info me-2"></i> Petunjuk Penggantian Link:</h6>
                    <ul class="small text-muted ps-3 mb-3" style="line-height: 1.6;">
                        <li class="mb-2">Pastikan Google Spreadsheet telah diatur akses berbagi (*Share*) menjadi <strong>"Anyone with the link can view"</strong> (Siapa saja yang memiliki link dapat melihat).</li>
                        <li class="mb-2">Sistem secara otomatis akan mengekstrak Spreadsheet ID dan mengonversi format tautan menjadi format ekspor data realtime.</li>
                        <li class="mb-2">Setelah menyimpan link baru, Anda dapat langsung menekan tombol <strong>"Sinkronisasi Sekarang"</strong> untuk mengunduh dataset terbaru.</li>
                    </ul>
                    <div class="p-3 bg-white rounded-3 border border-warning-subtle">
                        <small class="text-warning-emphasis fw-semibold">
                            <i class="fas fa-shield-halved me-1"></i> Perubahan link tersimpan permanen di database dan berlaku untuk seluruh proses sinkronisasi background.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('modals')
<!-- ========================================================================= -->
<!-- MODAL DRILLDOWN DATA INTERAKTIF (POPUP RINCIAN DATA)                      -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalDrilldown" tabindex="-1" aria-labelledby="modalDrilldownLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-width: 92%;">
        <div class="modal-content border-0 shadow-lg">
            <!-- Modal Header: Title, Subtitle, Copy Button, Badge, Close Button -->
            <div class="modal-header bg-primary text-white py-3 px-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-white text-primary rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px; flex-shrink: 0;">
                        <i class="fas fa-layer-group fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="modalDrilldownTitle">Rincian Data Aset</h5>
                        <small class="text-white-50" id="modalDrilldownSubtitle">Menampilkan daftar data BMN berdasarkan elemen yang diklik</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 ms-auto">
                    <!-- Tombol Salin Data Pindah ke Header Sesuai Poin 1 -->
                    <button type="button" class="btn btn-sm btn-light text-success fw-bold shadow-sm d-flex align-items-center gap-1 px-3 py-2" onclick="exportDrilldownTable()" title="Salin seluruh data tabel untuk diexport ke Excel">
                        <i class="fas fa-file-excel fs-6"></i>
                        <span>Salin Excel</span>
                    </button>
                    <span class="badge bg-warning text-dark px-3 py-2 fs-6 fw-bold" id="modalDrilldownBadge">0 Aset</span>
                    <button type="button" class="btn-close btn-close-white ms-1" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <!-- Modal Body: Clean Table Container without duplicate alert box -->
            <div class="modal-body p-3 bg-light">
                <div class="card border-0 shadow-sm mb-0">
                    <div class="card-body p-3">
                        <div class="table-responsive" style="max-height: 520px;">
                            <table class="table table-hover table-striped table-bordered align-middle w-100 mb-0" id="tableDrilldown">
                                <thead class="table-dark text-uppercase small" id="tableDrilldownHead">
                                    <!-- Dynamic headers will be injected here -->
                                </thead>
                                <tbody class="small" id="tableDrilldownBody">
                                    <!-- Dynamic rows will be injected here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer: Navigation & Info Relocated Sesuai Poin 3 -->
            <div class="modal-footer bg-white py-2 px-3 d-flex flex-wrap justify-content-between align-items-center w-100 gap-2">
                <!-- Info Data di Kiri -->
                <div id="drilldownFooterInfo" class="small text-muted d-flex align-items-center"></div>

                <!-- Navigasi Pagination & Tombol Tutup di Kanan -->
                <div class="d-flex align-items-center gap-3 ms-auto">
                    <div id="drilldownFooterPaging"></div>
                    <button type="button" class="btn btn-secondary btn-sm px-4 shadow-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
    // Matriks Capaian Interactive Filter Function
    function filterMatriksTable(kpknlValue) {
        const rows = $('.matriks-row');
        const badge = $('#matriks-filter-badge');
        
        let targetSum = 0;
        let sudahSum = 0;
        let belumSum = 0;
        let potensiSum = 0;

        if (kpknlValue === 'ALL') {
            rows.removeClass('table-warning').show();
            badge.html('<i class="fas fa-globe me-1"></i> Menampilkan Data: <strong>Seluruh KPKNL (Kanwil DJKN SJB)</strong>');
            
            rows.each(function() {
                targetSum += parseInt($(this).data('target')) || 0;
                sudahSum += parseInt($(this).data('sudah')) || 0;
                belumSum += parseInt($(this).data('belum')) || 0;
                potensiSum += parseInt($(this).data('potensi')) || 0;
            });

            $('#kpi-matriks-target').html(`${targetSum} <span class="fs-6 text-muted fw-normal">NUP</span>`);
            $('#sub-matriks-target').text('Total Kanwil DJKN SJB');

            $('#kpi-matriks-sudah').html(`${sudahSum} <span class="fs-6 text-muted fw-normal">NUP</span>`);
            $('#sub-matriks-sudah').text(`${Math.round((sudahSum/targetSum)*100)}% Tercapai`);

            $('#kpi-matriks-belum').html(`${belumSum} <span class="fs-6 text-muted fw-normal">NUP</span>`);
            $('#sub-matriks-belum').text('Sisa Belum Menjawab');

            $('#kpi-matriks-potensi').html(`${potensiSum} <span class="fs-6 text-muted fw-normal">NUP</span>`);
            $('#sub-matriks-potensi').text('Total se-Kanwil SJB');

        } else {
            rows.removeClass('table-warning');
            badge.html(`<i class="fas fa-building me-1"></i> Menampilkan Data: <strong>${kpknlValue}</strong>`);

            rows.each(function() {
                const rowKpknl = $(this).data('kpknl');
                if (rowKpknl === kpknlValue) {
                    $(this).addClass('table-warning').show();
                    targetSum = parseInt($(this).data('target')) || 0;
                    sudahSum = parseInt($(this).data('sudah')) || 0;
                    belumSum = parseInt($(this).data('belum')) || 0;
                    potensiSum = parseInt($(this).data('potensi')) || 0;
                } else {
                    $(this).removeClass('table-warning');
                }
            });

            $('#kpi-matriks-target').html(`${targetSum} <span class="fs-6 text-muted fw-normal">NUP</span>`);
            $('#sub-matriks-target').text(kpknlValue);

            $('#kpi-matriks-sudah').html(`${sudahSum} <span class="fs-6 text-muted fw-normal">NUP</span>`);
            $('#sub-matriks-sudah').text('100% Tercapai');

            $('#kpi-matriks-belum').html(`${belumSum} <span class="fs-6 text-muted fw-normal">NUP</span>`);
            $('#sub-matriks-belum').text('Semua selesai');

            $('#kpi-matriks-potensi').html(`${potensiSum} <span class="fs-6 text-muted fw-normal">NUP</span>`);
            $('#sub-matriks-potensi').text(kpknlValue);
        }
    }

    $(document).ready(function() {
        // =========================================================================
        // Opsi Pilihan Jumlah Baris Data (10, 25, 50, 100, Semua)
        // =========================================================================
        const commonTableLang = {
            lengthMenu: "Tampilkan _MENU_ baris per halaman",
            search: "Pencarian Cepat:",
            info: "Menampilkan _START_ s.d. _END_ dari _TOTAL_ data",
            infoEmpty: "Menampilkan 0 data",
            infoFiltered: "(disaring dari _MAX_ total data)",
            paginate: { first: "Awal", last: "Akhir", next: "Berikutnya", previous: "Sebelumnya" }
        };

        const dtPotensi19 = $('#tablePotensi19').DataTable({
            responsive: false,
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
            language: commonTableLang,
            layout: {
                topStart: {
                    pageLength: {
                        menu: [10, 25, 50, 100, -1]
                    },
                    buttons: ['excel', 'pdf']
                },
                topEnd: 'search',
                bottomStart: 'info',
                bottomEnd: 'paging'
            }
        });

        const dtSourcePotensi = $('#tableSourcePotensi').DataTable({
            responsive: false,
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
            language: commonTableLang,
            layout: {
                topStart: {
                    pageLength: {
                        menu: [10, 25, 50, 100, -1]
                    },
                    buttons: ['excel', 'pdf']
                },
                topEnd: 'search',
                bottomStart: 'info',
                bottomEnd: 'paging'
            }
        });

        // Set default filter on source table to Palembang initially
        dtSourcePotensi.column(1).search('Palembang').draw();

        const dtSourceEks = $('#tableSourceEks').DataTable({
            responsive: false,
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
            language: commonTableLang,
            layout: {
                topStart: {
                    pageLength: {
                        menu: [10, 25, 50, 100, -1]
                    },
                    buttons: ['excel', 'pdf']
                },
                topEnd: 'search',
                bottomStart: 'info',
                bottomEnd: 'paging'
            }
        });

        // Set default filter on eks source table to PALEMBANG initially
        dtSourceEks.column(1).search('PALEMBANG').draw();

        $('#tableSyncLog').DataTable({
            responsive: true,
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            language: commonTableLang,
            order: [[0, 'desc']]
        });

        // -------------------------------------------------------------
        // DUAL SCROLLBAR SYNCHRONIZER (Untuk Seluruh Tabel yang Lebar)
        // -------------------------------------------------------------
        window.syncTopScrollPotensi19 = function() {
            const tableWidth = $('#tablePotensi19').outerWidth();
            $('#topScrollDummyPotensi19').width(tableWidth);
        };

        window.syncTopScrollPotensi = function() {
            const tableWidth = $('#tableSourcePotensi').outerWidth();
            $('#topScrollDummyPotensi').width(tableWidth);
        };

        window.syncTopScrollEks = function() {
            const tableWidth = $('#tableSourceEks').outerWidth();
            $('#topScrollDummyEks').width(tableWidth);
        };

        setTimeout(() => {
            window.syncTopScrollPotensi19();
            window.syncTopScrollPotensi();
            window.syncTopScrollEks();
        }, 500);

        // Sync scroll events for Potensi 19 Table
        const $topScrollPotensi19 = $('#topScrollContainerPotensi19');
        const $wrapperPotensi19 = $('#tableWrapperPotensi19');

        $topScrollPotensi19.on('scroll', function() {
            $wrapperPotensi19.scrollLeft($(this).scrollLeft());
        });
        $wrapperPotensi19.on('scroll', function() {
            $topScrollPotensi19.scrollLeft($(this).scrollLeft());
        });

        // Sync scroll events for Potensi Table
        const $topScrollPotensi = $('#topScrollContainerPotensi');
        const $wrapperPotensi = $('#tableWrapperPotensi');

        $topScrollPotensi.on('scroll', function() {
            $wrapperPotensi.scrollLeft($(this).scrollLeft());
        });
        $wrapperPotensi.on('scroll', function() {
            $topScrollPotensi.scrollLeft($(this).scrollLeft());
        });

        // Sync scroll events for Eks Idle Table
        const $topScrollEks = $('#topScrollContainerEks');
        const $wrapperEks = $('#tableWrapperEks');

        $topScrollEks.on('scroll', function() {
            $wrapperEks.scrollLeft($(this).scrollLeft());
        });
        $wrapperEks.on('scroll', function() {
            $topScrollEks.scrollLeft($(this).scrollLeft());
        });

        // Filter Controls for Table Source Potensi (Poin 4)
        $('#filter-potensi-status').on('change', function() {
            dtSourcePotensi.column(12).search($(this).val()).draw();
            setTimeout(window.syncTopScrollPotensi, 200);
        });

        $('#filter-potensi-kl').on('change', function() {
            dtSourcePotensi.column(4).search($(this).val()).draw();
            setTimeout(window.syncTopScrollPotensi, 200);
        });

        window.resetPotensiFilters = function() {
            $('#filter-potensi-status').val('');
            $('#filter-potensi-kl').val('');
            dtSourcePotensi.columns().search('').draw();
            setTimeout(window.syncTopScrollPotensi, 200);
        };

        // Filter Controls for Table Source Eks Idle
        $('#filter-eks-kpknl').on('change', function() {
            dtSourceEks.column(1).search($(this).val()).draw();
            setTimeout(window.syncTopScrollEks, 200);
        });

        $('#filter-eks-pengelolaan').on('change', function() {
            dtSourceEks.column(9).search($(this).val()).draw();
            setTimeout(window.syncTopScrollEks, 200);
        });

        window.resetEksFilters = function() {
            $('#filter-eks-kpknl').val('');
            $('#filter-eks-pengelolaan').val('');
            dtSourceEks.columns().search('').draw();
            setTimeout(window.syncTopScrollEks, 200);
        };

        // -------------------------------------------------------------
        // CHARTS SETUP (Using Chart.js with Interactive Click Events)
        // -------------------------------------------------------------
        // 1. Usulan Pengelolaan BMN Eks Idle (Horizontal Bar)
        const ctxUsulan = document.getElementById('chartUsulanEksIdle').getContext('2d');
        new Chart(ctxUsulan, {
            type: 'bar',
            data: {
                labels: ['Masih dalam kajian', 'Dipindahtangankan', 'Dimanfaatkan', 'PSP'],
                datasets: [{
                    label: 'Jumlah NUP',
                    data: [
                        {{ $eksPengelolaanCounts['Masih dalam kajian'] }},
                        {{ $eksPengelolaanCounts['Dipindahtangankan'] }},
                        {{ $eksPengelolaanCounts['Dimanfaatkan'] }},
                        {{ $eksPengelolaanCounts['PSP'] }}
                    ],
                    backgroundColor: ['#0284c7', '#22c55e', '#a855f7', '#f97316'],
                    borderRadius: 6,
                    barPercentage: 0.6
                }]
            },
            options: {
                animation: { duration: 700, easing: 'easeOutQuart' },
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            afterLabel: function() { return '👆 Klik batang untuk melihat rincian aset'; }
                        }
                    }
                },
                scales: {
                    x: { beginAtZero: true, max: 12, ticks: { stepSize: 2 } },
                    y: { grid: { display: false } }
                },
                onClick: (event, elements, chart) => {
                    if (elements.length > 0) {
                        const index = elements[0].index;
                        const label = chart.data.labels[index];
                        if (label.includes('PSP')) {
                            openDrilldown('eks', 'psp', 'BMN Eks Idle - Usulan Penggunaan (PSP)', '3 Aset');
                        } else if (label.includes('Dipindahtangankan')) {
                            openDrilldown('eks', 'penghapusan', 'BMN Eks Idle - Usulan Penghapusan / Penjualan', '10 Aset');
                        } else if (label.includes('Dimanfaatkan')) {
                            openDrilldown('eks', 'dimanfaatkan', 'BMN Eks Idle - Usulan Pemanfaatan', '0 Aset');
                        } else {
                            openDrilldown('eks', 'usulan_psp', 'BMN Eks Idle - Usulan Masih Dalam Kajian', '3 Aset');
                        }
                    }
                }
            }
        });

        // Donut Helper
        function createDonutChart(canvasId, dataArr, labelsArr, colorsArr, onClickHandler) {
            const ctx = document.getElementById(canvasId).getContext('2d');
            return new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labelsArr,
                    datasets: [{
                        data: dataArr,
                        backgroundColor: colorsArr,
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    animation: { duration: 700, easing: 'easeOutQuart' },
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '65%',
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: { boxWidth: 10, font: { size: 9, weight: '600' } }
                        },
                        tooltip: {
                            callbacks: {
                                afterLabel: function() { return '👆 Klik segmen untuk melihat rincian aset'; }
                            }
                        }
                    },
                    onClick: onClickHandler
                }
            });
        }

        const donutLabels = ['PSP Selesai (SK)', 'Penghapusan/Jual', 'Usulan PSP/Kajian'];
        const donutColors = ['#f97316', '#22c55e', '#0284c7'];

        window.chartTindakLanjutEksInstance = createDonutChart(
            'chartDonutPalembang',
            [{{ $eksPengelolaanCounts['PSP'] }}, {{ $eksPengelolaanCounts['Dipindahtangankan'] }}, {{ $eksPengelolaanCounts['Masih dalam kajian'] }}],
            donutLabels,
            donutColors,
            (event, elements, chart) => {
                if (elements.length > 0) {
                    const index = elements[0].index;
                    if (index === 0) {
                        openDrilldown('eks', 'psp_selesai', 'BMN Eks Idle - PSP Selesai (SK Definitif)', '3 Aset');
                    } else if (index === 1) {
                        openDrilldown('eks', 'penghapusan', 'BMN Eks Idle - Penghapusan / Penjualan', '10 Aset');
                    } else {
                        openDrilldown('eks', 'usulan_psp', 'BMN Eks Idle - Usulan PSP / Kajian', '3 Aset');
                    }
                }
            }
        );

        // 2. Komposisi Tahap Klarifikasi (Donut) - Total 256 NUP Palembang
        const ctxKlarifikasi = document.getElementById('chartKomposisiKlarifikasi').getContext('2d');
        window.chartKomposisiKlarifikasiInstance = new Chart(ctxKlarifikasi, {
            type: 'doughnut',
            data: {
                labels: ['Pemantauan', 'Penelusuran', 'Penelitian'],
                datasets: [{
                    data: [
                        {{ $komposisiTahap['Pemantauan'] }},
                        {{ $komposisiTahap['Penelusuran'] }},
                        {{ $komposisiTahap['Penelitian'] }}
                    ],
                    backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                animation: { duration: 700, easing: 'easeOutQuart' },
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            afterLabel: function() { return '👆 Klik untuk rincian tahap ini'; }
                        }
                    }
                },
                onClick: (event, elements, chart) => {
                    if (elements.length > 0) {
                        const index = elements[0].index;
                        if (index === 0) {
                            openDrilldown('potensi', 'pemantauan', 'BMN Terindikasi Idle - Tahap Pemantauan', '198 Aset');
                        } else if (index === 1) {
                            openDrilldown('potensi', 'penelusuran', 'BMN Terindikasi Idle - Tahap Penelusuran', '58 Aset');
                        } else {
                            openDrilldown('potensi', 'penelitian', 'BMN Terindikasi Idle - Tahap Penelitian', '0 Aset');
                        }
                    }
                }
            }
        });

        // 4. Eselon I / Klaster Donut Chart (Dinamis per K/L)
        const ctxEselon = document.getElementById('chartEselon1Kemenkeu').getContext('2d');
        window.donutKlasterChart = new Chart(ctxEselon, {
            type: 'doughnut',
            data: {
                labels: ['DJP', 'DJBC', 'DJPb', 'DJKN'],
                datasets: [{
                    data: [
                        {{ $eselon1Counts['DJP'] }},
                        {{ $eselon1Counts['DJBC'] }},
                        {{ $eselon1Counts['DJPb'] }},
                        {{ $eselon1Counts['DJKN'] }}
                    ],
                    backgroundColor: ['#0c306b', '#10b981', '#0284c7', '#f59e0b'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                animation: { duration: 700, easing: 'easeOutQuart' },
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            afterLabel: function() { return '👆 Klik untuk rincian unit/klaster ini'; }
                        }
                    }
                },
                onClick: (event, elements, chart) => {
                    if (elements.length > 0) {
                        const index = elements[0].index;
                        const label = chart.data.labels[index];
                        if (window.currentSelectedKl && window.currentSelectedKl.toUpperCase().includes('KEUANGAN')) {
                            openDrilldown('potensi', 'kemenkeu_eselon_' + label.toLowerCase(), 'BMN Kemenkeu - Ditjen ' + label, 'Rincian Eselon I ' + label);
                        } else if (window.currentSelectedKl === 'ALL') {
                            const klName = (window.currentChartKlKeys && window.currentChartKlKeys[index]) ? window.currentChartKlKeys[index] : label;
                            openDrilldown('potensi', 'kl_' + encodeURIComponent(klName), 'BMN ' + klName, 'Rincian Aset ' + klName);
                        } else {
                            const satkerName = (window.currentChartSatkerKeys && window.currentChartSatkerKeys[index]) ? window.currentChartSatkerKeys[index] : label;
                            openDrilldown('potensi', 'satker_' + encodeURIComponent(satkerName), satkerName, 'Rincian Aset ' + satkerName);
                        }
                    }
                }
            }
        });

        // -------------------------------------------------------------
        // DRILLDOWN ENGINE (Interactive Data Popup Modal)
        // -------------------------------------------------------------
        window.bmnPotensiData = @json($allPotensiRows);
        window.bmnEksData = @json($allEksRows);

        // State Filter K/L Terpilih (Default: Kemenkeu)
        window.currentSelectedKl = 'KEMENTERIAN KEUANGAN';
        window.currentTopBarang = [
            { name: 'Rumah Negara', count: {{ $jenisBarangCounts['Rumah Negara'] ?? 0 }} },
            { name: 'Bangunan Gedung Kantor', count: {{ $jenisBarangCounts['Bangunan Gedung Kantor'] ?? 0 }} },
            { name: 'Tanah Rumah Negara', count: {{ $jenisBarangCounts['Tanah Rumah Negara'] ?? 0 }} },
            { name: 'Tanah Bangunan Kantor', count: {{ $jenisBarangCounts['Tanah Bangunan Kantor'] ?? 0 }} }
        ];

        window.getCurrentKlRows = function() {
            if (!window.currentSelectedKl || window.currentSelectedKl === 'ALL') {
                return window.bmnPotensiData;
            }
            return window.bmnPotensiData.filter(r => (r.kementerian_lembaga || '').toUpperCase() === window.currentSelectedKl.toUpperCase());
        };

        window.drilldownCurrentKl = function(stage) {
            const klName = window.currentSelectedKl === 'ALL' ? 'Seluruh K/L' : window.currentSelectedKl;
            const rows = window.getCurrentKlRows();
            let filtered = rows;
            let title = `BMN ${klName}`;
            let subtitle = 'Semua Tahapan';

            if (stage === 'penelitian') {
                filtered = rows.filter(r => (r.status_tindak_lanjut || '').trim() === 'Penelitian');
                title = `BMN ${klName} — Tahap Penelitian`;
                subtitle = `${filtered.length} Aset Penelitian`;
            } else if (stage === 'pemantauan') {
                filtered = rows.filter(r => (r.status_tindak_lanjut || '').trim() === 'Pemantauan');
                title = `BMN ${klName} — Tahap Pemantauan`;
                subtitle = `${filtered.length} Aset Pemantauan`;
            } else if (stage === 'penelusuran') {
                filtered = rows.filter(r => (r.status_tindak_lanjut || '').trim() === 'Penelusuran');
                title = `BMN ${klName} — Tahap Penelusuran`;
                subtitle = `${filtered.length} Aset Penelusuran`;
            } else if (stage === 'potensi') {
                filtered = rows.filter(r => (r.hasil_jawaban || '').includes('RB tidak') || (r.hasil_jawaban || '').includes('diserahkan') || (r.status_bmn_idle || '').includes('Potensi'));
                title = `BMN ${klName} — Berpotensi Idle`;
                subtitle = `${filtered.length} Aset Potensi Idle`;
            } else {
                title = `Populasi BMN ${klName}`;
                subtitle = `${filtered.length} Total Aset`;
            }

            renderPotensiDrilldown(filtered);
            $('#modalDrilldownTitle').text(title);
            $('#modalDrilldownSubtitle').text(subtitle);
            $('#modalDrilldownFilterDesc').html(`Kategori: <strong>${title}</strong> (${subtitle})`);
            $('#modalDrilldownBadge').text(`${filtered.length} NUP / Aset`);
            const modalDom = document.getElementById('modalDrilldown');
            if (modalDom && modalDom.parentElement !== document.body) {
                document.body.appendChild(modalDom);
            }
            const modalEl = bootstrap.Modal.getOrCreateInstance(modalDom);
            modalEl.show();
        };

        window.drilldownBarang = function(idx) {
            const item = window.currentTopBarang[idx];
            if (!item || !item.name || item.name === '-') return;
            const klName = window.currentSelectedKl === 'ALL' ? 'Seluruh K/L' : window.currentSelectedKl;
            const rows = window.getCurrentKlRows();
            const filtered = rows.filter(r => (r.kelompok_barang || '').trim() === item.name);
            const title = `BMN ${klName} — ${item.name}`;
            const subtitle = `${filtered.length} Aset ${item.name}`;

            renderPotensiDrilldown(filtered);
            $('#modalDrilldownTitle').text(title);
            $('#modalDrilldownSubtitle').text(subtitle);
            $('#modalDrilldownFilterDesc').html(`Kategori: <strong>${title}</strong> (${subtitle})`);
            $('#modalDrilldownBadge').text(`${filtered.length} NUP / Aset`);
            const modalDom = document.getElementById('modalDrilldown');
            if (modalDom && modalDom.parentElement !== document.body) {
                document.body.appendChild(modalDom);
            }
            const modalEl = bootstrap.Modal.getOrCreateInstance(modalDom);
            modalEl.show();
        };

        window.filterKlasterKl = function(selectedKl) {
            window.currentSelectedKl = selectedKl;
            const isAll = (selectedKl === 'ALL');
            const filteredRows = isAll 
                ? window.bmnPotensiData 
                : window.bmnPotensiData.filter(r => (r.kementerian_lembaga || '').toUpperCase() === selectedKl.toUpperCase());

            const total = filteredRows.length;
            const penelitian = filteredRows.filter(r => (r.status_tindak_lanjut || '').trim() === 'Penelitian').length;
            const pemantauan = filteredRows.filter(r => (r.status_tindak_lanjut || '').trim() === 'Pemantauan').length;
            const penelusuran = filteredRows.filter(r => (r.status_tindak_lanjut || '').trim() === 'Penelusuran').length;
            const potensi = filteredRows.filter(r => (r.hasil_jawaban || '').includes('RB tidak') || (r.hasil_jawaban || '').includes('diserahkan') || (r.status_bmn_idle || '').includes('Potensi')).length;

            const displayName = isAll ? 'Seluruh Kementerian / Lembaga' : selectedKl;
            const shortName = isAll ? 'SELURUH K/L' : (selectedKl.length > 20 ? selectedKl.substring(0, 18) + '...' : selectedKl);

            $('#klaster-banner-kl-label').text(displayName);
            $('#klaster-filter-badge').html(isAll 
                ? `<i class="fas fa-globe me-1"></i> Klaster: <strong>Seluruh K/L (${total} NUP)</strong>`
                : `<i class="fas fa-building me-1"></i> Klaster Terpilih: <strong>${selectedKl} (${total} NUP)</strong>`
            );

            $('#kpi-klaster-total-label').text('TOTAL ' + shortName);
            $('#kpi-klaster-total').html(`${total} <span class="fs-6 text-muted fw-normal">NUP</span>`);
            $('#kpi-klaster-penelitian').html(`${penelitian} <span class="fs-6 text-muted fw-normal">NUP</span>`);
            $('#kpi-klaster-pemantauan').html(`${pemantauan} <span class="fs-6 text-muted fw-normal">NUP</span>`);
            $('#kpi-klaster-penelusuran').html(`${penelusuran} <span class="fs-6 text-muted fw-normal">NUP</span>`);
            $('#kpi-klaster-potensi').html(`${potensi} <span class="fs-6 text-muted fw-normal">NUP</span>`);

            $('#td-klaster-name').text(isAll ? 'KPKNL Palembang (Seluruh K/L)' : `KPKNL Palembang (${shortName})`);
            $('#td-klaster-total').text(total);
            $('#td-klaster-penelusuran').text(penelusuran);
            $('#td-klaster-pemantauan').text(pemantauan);
            $('#td-klaster-penelitian').text(penelitian);

            $('#table-klaster-footer-label').text('TOTAL ' + shortName);
            $('#td-klaster-footer-total').text(total);
            $('#td-klaster-footer-penelusuran').text(penelusuran);
            $('#td-klaster-footer-pemantauan').text(pemantauan);
            $('#td-klaster-footer-penelitian').text(penelitian);

            // Update Top 4 Jenis Barang Cards Dinamis
            const barangCounts = {};
            filteredRows.forEach(r => {
                const b = (r.kelompok_barang || 'Lainnya').trim();
                barangCounts[b] = (barangCounts[b] || 0) + 1;
            });
            const sortedBarang = Object.keys(barangCounts)
                .map(k => ({ name: k, count: barangCounts[k] }))
                .sort((a, b) => b.count - a.count);

            window.currentTopBarang = [];
            for (let i = 0; i < 4; i++) {
                if (i < sortedBarang.length) {
                    window.currentTopBarang.push(sortedBarang[i]);
                    $(`#jb-${i}-title`).text(sortedBarang[i].name);
                    $(`#jb-${i}-val`).html(`${sortedBarang[i].count} <span class="fs-6 text-muted">NUP</span>`);
                    $(`#card-jb-${i}`).show();
                } else {
                    window.currentTopBarang.push({ name: '-', count: 0 });
                    $(`#jb-${i}-title`).text('-');
                    $(`#jb-${i}-val`).html(`0 <span class="fs-6 text-muted">NUP</span>`);
                }
            }

            // Update Donut Chart & Legend Dinamis
            window.updateKlasterDonutChart(selectedKl, filteredRows);
        };

        window.currentChartSatkerKeys = [];
        window.currentChartKlKeys = [];

        window.updateKlasterDonutChart = function(selectedKl, rows) {
            let labels = [];
            let data = [];
            let colors = [];
            let titleText = '';
            let badgeText = '';
            let legendItemsHtml = '';

            const executivePalette = [
                '#0c306b', '#10b981', '#0284c7', '#f59e0b', '#8b5cf6', 
                '#ec4899', '#14b8a6', '#f43f5e', '#6366f1', '#eab308',
                '#64748b', '#06b6d4', '#84cc16', '#a855f7', '#d97706'
            ];

            if (selectedKl.toUpperCase().includes('KEUANGAN')) {
                titleText = 'Komposisi Unit Eselon I Kemenkeu (Palembang)';
                badgeText = 'Kemenkeu';
                labels = ['DJP', 'DJBC', 'DJPb', 'DJKN'];
                data = [
                    rows.filter(r => (r.eselon1_kemenkeu || '').toUpperCase() === 'DJP').length,
                    rows.filter(r => (r.eselon1_kemenkeu || '').toUpperCase() === 'DJBC').length,
                    rows.filter(r => (r.eselon1_kemenkeu || '').toUpperCase() === 'DJPB').length,
                    rows.filter(r => (r.eselon1_kemenkeu || '').toUpperCase() === 'DJKN').length,
                ];
                colors = ['#0c306b', '#10b981', '#0284c7', '#f59e0b'];
                const bgClasses = ['border-primary text-primary', 'border-success text-success', 'border-info text-info', 'border-warning text-warning'];
                labels.forEach((lbl, i) => {
                    legendItemsHtml += `
                        <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded-3 border-start border-4 ${bgClasses[i].split(' ')[0]} interactive-card" onclick="openDrilldown('potensi', 'kemenkeu_eselon_${lbl.toLowerCase()}', 'BMN Kemenkeu - Ditjen ${lbl}', '${data[i]} Aset')" title="Klik rincian ${lbl}">
                            <span class="fw-semibold">${lbl}</span>
                            <span class="h5 fw-bold ${bgClasses[i].split(' ')[1]} mb-0">${data[i]} <small class="fs-6 text-muted">NUP</small></span>
                        </div>
                    `;
                });
            } else if (selectedKl === 'ALL') {
                titleText = 'Komposisi Portofolio Seluruh K/L (Palembang)';
                badgeText = '13 K/L';
                const klCounts = {};
                rows.forEach(r => {
                    const k = (r.kementerian_lembaga || 'Lainnya').trim();
                    klCounts[k] = (klCounts[k] || 0) + 1;
                });
                const sortedKl = Object.keys(klCounts)
                    .map(k => ({ name: k, count: klCounts[k] }))
                    .sort((a, b) => b.count - a.count);

                window.currentChartKlKeys = sortedKl.map(k => k.name);
                labels = sortedKl.map(k => k.name.length > 20 ? k.name.substring(0, 18) + '...' : k.name);
                data = sortedKl.map(k => k.count);
                colors = executivePalette.slice(0, labels.length);

                sortedKl.forEach((k, i) => {
                    const c = executivePalette[i % executivePalette.length];
                    const safeKl = encodeURIComponent(k.name);
                    legendItemsHtml += `
                        <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded-3 interactive-card" style="border-left: 4px solid ${c};" onclick="openDrilldown('potensi', 'kl_${safeKl}', 'BMN ${k.name.replace(/'/g, "\\'")}', '${k.count} Aset')" title="Klik rincian ${k.name}">
                            <span class="small fw-semibold text-truncate me-2" style="max-width: 140px;" title="${k.name}">${k.name}</span>
                            <span class="h6 fw-bold mb-0" style="color: ${c}; white-space: nowrap;">${k.count} <small class="text-muted">NUP</small></span>
                        </div>
                    `;
                });
            } else {
                // Poin 6: Mengelompokkan berdasarkan SATUAN KERJA (Satker) riil untuk K/L terpilih
                const satkerCounts = {};
                rows.forEach(r => {
                    const s = (r.nama_satker || 'Satuan Kerja').trim();
                    satkerCounts[s] = (satkerCounts[s] || 0) + 1;
                });
                const sortedSatker = Object.keys(satkerCounts)
                    .map(k => ({ name: k, count: satkerCounts[k] }))
                    .sort((a, b) => b.count - a.count);

                window.currentChartSatkerKeys = sortedSatker.map(s => s.name);
                const displayKl = selectedKl.length > 24 ? selectedKl.substring(0, 22) + '...' : selectedKl;
                titleText = `Komposisi Satuan Kerja (${displayKl})`;
                badgeText = `${sortedSatker.length} Satker`;
                labels = sortedSatker.map(s => s.name.length > 22 ? s.name.substring(0, 20) + '...' : s.name);
                data = sortedSatker.map(s => s.count);
                colors = executivePalette.slice(0, labels.length);

                sortedSatker.forEach((s, i) => {
                    const c = executivePalette[i % executivePalette.length];
                    const safeSatker = encodeURIComponent(s.name);
                    legendItemsHtml += `
                        <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded-3 interactive-card" style="border-left: 4px solid ${c};" onclick="openDrilldown('potensi', 'satker_${safeSatker}', '${s.name.replace(/'/g, "\\'")}', '${s.count} Aset')" title="Klik rincian aset ${s.name}">
                            <span class="small fw-semibold text-truncate me-2" style="max-width: 140px;" title="${s.name}">${s.name}</span>
                            <span class="h6 fw-bold mb-0" style="color: ${c}; white-space: nowrap;">${s.count} <small class="text-muted">NUP</small></span>
                        </div>
                    `;
                });
            }

            $('#chart-klaster-title').html(`<i class="fas fa-chart-pie text-primary me-2"></i> ${titleText}`);
            $('#chart-klaster-badge').text(badgeText);
            $('#chart-klaster-legend-container').html(legendItemsHtml);

            if (window.donutKlasterChart) {
                window.donutKlasterChart.data.labels = labels;
                window.donutKlasterChart.data.datasets[0].data = data;
                window.donutKlasterChart.data.datasets[0].backgroundColor = colors.slice(0, labels.length);
                window.donutKlasterChart.update();
            }
        };

        window.filterPotensiTableKl = function(selectedKl) {
            if (selectedKl === 'ALL') {
                dtPotensi19.column(1).search('').draw();
                const count = dtPotensi19.rows({ search: 'applied' }).count();
                $('#potensi-filter-badge').html(`<i class="fas fa-globe me-1"></i> Menampilkan: <strong>Seluruh Kementerian / Lembaga (${count} NUP)</strong>`);
                $('#potensi-table-title').html('<i class="fas fa-list-check text-primary me-2"></i> Rincian Seluruh Aset Berpotensi Idle (KPKNL Palembang)');
                $('#potensi-table-badge').text(`${count} NUP Terpilih`);
                $('#kpi-potensi-count').text(count);
                $('#kpi-potensi-percentage').text('{{ $potensiPersentaseTotal }}%');
                $('#kpi-potensi-progress-bar').css('width', '{{ min(100, max(5, $potensiPersentaseTotal)) }}%');
                $('#kpi-potensi-progress-label').html(`<strong>${count} NUP</strong> dari {{ $totalPopulasi }} Total Populasi`);
                $('#kpi-potensi-tl-pemantauan').text('{{ $potensiTindakLanjutCounts["Pemantauan"] ?? 0 }} NUP');
                $('#kpi-potensi-tl-penelitian').text('{{ $potensiTindakLanjutCounts["Penelitian"] ?? 0 }} NUP');
                $('#kpi-potensi-tl-penelusuran').text('{{ $potensiTindakLanjutCounts["Penelusuran"] ?? 0 }} NUP');
            } else {
                dtPotensi19.column(1).search(selectedKl).draw();
                const countKl = dtPotensi19.rows({ search: 'applied' }).count();
                $('#potensi-filter-badge').html(`<i class="fas fa-building me-1"></i> Menampilkan: <strong>${selectedKl} (${countKl} NUP)</strong>`);
                $('#potensi-table-title').html(`<i class="fas fa-list-check text-primary me-2"></i> Rincian Aset ${selectedKl} Berpotensi Idle (KPKNL Palembang)`);
                $('#potensi-table-badge').text(`${countKl} NUP Terpilih`);
                $('#kpi-potensi-count').text(countKl);
                
                const totalKlRows = window.bmnPotensiData.filter(r => (r.kementerian_lembaga || '').toUpperCase().includes(selectedKl.toUpperCase())).length || 1;
                const pct = Math.round((countKl / totalKlRows) * 100);
                $('#kpi-potensi-percentage').text(`${pct}%`);
                $('#kpi-potensi-progress-bar').css('width', `${Math.min(100, Math.max(5, pct))}%`);
                $('#kpi-potensi-progress-label').html(`<strong>${countKl} NUP</strong> dari ${totalKlRows} Total Aset ${selectedKl.substring(0, 20)}`);
                
                if (selectedKl.toUpperCase().includes('KEUANGAN')) {
                    $('#kpi-potensi-tl-pemantauan').text('{{ $potensiTindakLanjutCounts["Pemantauan"] ?? 0 }} NUP');
                    $('#kpi-potensi-tl-penelitian').text('{{ $potensiTindakLanjutCounts["Penelitian"] ?? 0 }} NUP');
                    $('#kpi-potensi-tl-penelusuran').text('{{ $potensiTindakLanjutCounts["Penelusuran"] ?? 0 }} NUP');
                } else {
                    $('#kpi-potensi-tl-pemantauan').text('0 NUP');
                    $('#kpi-potensi-tl-penelitian').text('0 NUP');
                    $('#kpi-potensi-tl-penelusuran').text('0 NUP');
                }
            }
            setTimeout(window.syncTopScrollPotensi19, 150);
        };

        let dtDrilldown = null;

        window.openDrilldown = function(type, filterKey, title, subtitle) {
            $('#modalDrilldownTitle').text(title || 'Rincian Data Aset');
            $('#modalDrilldownSubtitle').text(subtitle || 'Data BMN Terkait');
            $('#modalDrilldownFilterDesc').html(`Kategori: <strong>${title}</strong> (${subtitle || ''})`);

            let filtered = [];

            if (type === 'eks') {
                if (filterKey === 'all') {
                    filtered = window.bmnEksData;
                } else if (filterKey === 'dokumen_pengelolaan' || filterKey === 'saldo_2026') {
                    // Poin 2: Data saldo per 2026 diambil dari Dokumen Pengelolaan BMN non-penghapusan (3 NUP)
                    filtered = window.bmnEksData.filter(r => (r.jenis_tindak_lanjut || '').trim() === 'Dokumen Pengelolaan BMN' && !(r.jenis_pengelolaan || '').includes('Penghapusan') && !(r.jenis_pengelolaan || '').includes('Penjualan'));
                } else if (filterKey === 'usulan_psp' || filterKey === 'psp') {
                    // Poin 1: Data Usulan PSP diambil dari Dokumen Usulan Pengelolaan BMN (3 NUP)
                    filtered = window.bmnEksData.filter(r => (r.jenis_tindak_lanjut || '').trim() === 'Dokumen Usulan Pengelolaan BMN');
                } else if (filterKey === 'psp_selesai') {
                    filtered = window.bmnEksData.filter(r => (r.jenis_pengelolaan || '').includes('PSP') && (r.jenis_tindak_lanjut || '').includes('Pengelolaan') && !(r.jenis_pengelolaan || '').includes('Penghapusan'));
                } else if (filterKey === 'penghapusan') {
                    // Poin 2: Penghapusan berada pada card penghapusan tersendiri (10 NUP)
                    filtered = window.bmnEksData.filter(r => (r.jenis_pengelolaan || '').includes('Penghapusan') || (r.jenis_pengelolaan || '').includes('Penjualan') || (r.jenis_pengelolaan || '').includes('Dipindahtangankan'));
                } else if (filterKey === 'dimanfaatkan') {
                    filtered = window.bmnEksData.filter(r => (r.jenis_pengelolaan || '').includes('Manfaat') || (r.jenis_pengelolaan || '').includes('Pemanfaatan'));
                } else if (filterKey === 'kajian') {
                    filtered = window.bmnEksData.filter(r => (r.jenis_tindak_lanjut || '').includes('Kajian') || (r.jenis_pengelolaan || '').includes('kajian'));
                } else {
                    filtered = window.bmnEksData.filter(r => (r.jenis_pengelolaan || '').toLowerCase().includes(filterKey.toLowerCase()) || (r.jenis_tindak_lanjut || '').toLowerCase().includes(filterKey.toLowerCase()));
                }

                renderEksDrilldown(filtered);
            } else {
                // Potensi Idle
                if (filterKey === 'all') {
                    filtered = window.bmnPotensiData;
                } else if (filterKey.startsWith('satker_')) {
                    const sName = decodeURIComponent(filterKey.replace('satker_', ''));
                    filtered = window.bmnPotensiData.filter(r => (r.nama_satker || '').trim().toLowerCase() === sName.trim().toLowerCase());
                } else if (filterKey.startsWith('kl_')) {
                    const klName = decodeURIComponent(filterKey.replace('kl_', ''));
                    filtered = window.bmnPotensiData.filter(r => (r.kementerian_lembaga || '').trim().toUpperCase() === klName.trim().toUpperCase());
                } else if (filterKey.startsWith('klaster_')) {
                    const kCode = filterKey.replace('klaster_', '');
                    filtered = window.bmnPotensiData.filter(r => (r.klasterisasi || '').trim() === kCode);
                } else if (filterKey === 'sudah_menjawab') {
                    filtered = window.bmnPotensiData.filter(r => r.hasil_jawaban && r.hasil_jawaban.trim() !== '');
                } else if (filterKey === 'menunggu' || filterKey === 'belum_klarifikasi') {
                    filtered = window.bmnPotensiData.filter(r => !r.hasil_jawaban || r.hasil_jawaban.trim() === '');
                } else if (filterKey === 'potensi_19') {
                    filtered = window.bmnPotensiData.filter(r => (r.is_kemenkeu || (r.kementerian_lembaga || '').toUpperCase().includes('KEUANGAN')) && ((r.hasil_jawaban || '').includes('RB tidak ada rencana') || (r.hasil_jawaban || '').includes('diserahkan kembal') || (r.status_bmn_idle || '').includes('Potensi')));
                } else if (filterKey === 'pemantauan') {
                    filtered = window.bmnPotensiData.filter(r => (r.status_tindak_lanjut || '').trim() === 'Pemantauan');
                } else if (filterKey === 'penelusuran') {
                    filtered = window.bmnPotensiData.filter(r => (r.status_tindak_lanjut || '').trim() === 'Penelusuran');
                } else if (filterKey === 'penelitian') {
                    filtered = window.bmnPotensiData.filter(r => (r.status_tindak_lanjut || '').trim() === 'Penelitian');
                } else if (filterKey === 'kemenkeu_all') {
                    filtered = window.bmnPotensiData.filter(r => r.is_kemenkeu || (r.kementerian_lembaga || '').toUpperCase().includes('KEUANGAN'));
                } else if (filterKey === 'kemenkeu_pemantauan') {
                    filtered = window.bmnPotensiData.filter(r => (r.is_kemenkeu || (r.kementerian_lembaga || '').toUpperCase().includes('KEUANGAN')) && (r.status_tindak_lanjut || '').trim() === 'Pemantauan');
                } else if (filterKey === 'kemenkeu_penelusuran') {
                    filtered = window.bmnPotensiData.filter(r => (r.is_kemenkeu || (r.kementerian_lembaga || '').toUpperCase().includes('KEUANGAN')) && (r.status_tindak_lanjut || '').trim() === 'Penelusuran');
                } else if (filterKey === 'kemenkeu_penelitian') {
                    filtered = window.bmnPotensiData.filter(r => (r.is_kemenkeu || (r.kementerian_lembaga || '').toUpperCase().includes('KEUANGAN')) && (r.status_tindak_lanjut || '').trim() === 'Penelitian');
                } else if (filterKey.startsWith('kemenkeu_eselon_')) {
                    const unit = filterKey.replace('kemenkeu_eselon_', '').toUpperCase();
                    filtered = window.bmnPotensiData.filter(r => (r.is_kemenkeu || (r.kementerian_lembaga || '').toUpperCase().includes('KEUANGAN')) && (r.eselon1_kemenkeu || '').toUpperCase() === unit);
                } else if (filterKey === 'kemenkeu_rn') {
                    filtered = window.bmnPotensiData.filter(r => (r.is_kemenkeu || (r.kementerian_lembaga || '').toUpperCase().includes('KEUANGAN')) && (r.kelompok_barang || '').trim() === 'Rumah Negara');
                } else if (filterKey === 'kemenkeu_bgk') {
                    filtered = window.bmnPotensiData.filter(r => (r.is_kemenkeu || (r.kementerian_lembaga || '').toUpperCase().includes('KEUANGAN')) && (r.kelompok_barang || '').trim() === 'Bangunan Gedung Kantor');
                } else if (filterKey === 'kemenkeu_trn') {
                    filtered = window.bmnPotensiData.filter(r => (r.is_kemenkeu || (r.kementerian_lembaga || '').toUpperCase().includes('KEUANGAN')) && (r.kelompok_barang || '').trim() === 'Tanah Rumah Negara');
                } else if (filterKey === 'kemenkeu_tbk') {
                    filtered = window.bmnPotensiData.filter(r => (r.is_kemenkeu || (r.kementerian_lembaga || '').toUpperCase().includes('KEUANGAN')) && (r.kelompok_barang || '').trim() === 'Tanah Bangunan Kantor');
                } else if (filterKey === 'potensi_19_penelusuran') {
                    filtered = window.bmnPotensiData.filter(r => (r.is_kemenkeu || (r.kementerian_lembaga || '').toUpperCase().includes('KEUANGAN')) && ((r.hasil_jawaban || '').includes('RB tidak ada rencana') || (r.hasil_jawaban || '').includes('diserahkan kembal') || (r.status_bmn_idle || '').includes('Potensi')) && (r.status_tindak_lanjut || '').trim() === 'Penelusuran');
                } else if (filterKey === 'potensi_19_pemantauan') {
                    filtered = window.bmnPotensiData.filter(r => (r.is_kemenkeu || (r.kementerian_lembaga || '').toUpperCase().includes('KEUANGAN')) && ((r.hasil_jawaban || '').includes('RB tidak ada rencana') || (r.hasil_jawaban || '').includes('diserahkan kembal') || (r.status_bmn_idle || '').includes('Potensi')) && (r.status_tindak_lanjut || '').trim() === 'Pemantauan');
                } else {
                    filtered = window.bmnPotensiData.filter(r => (r.nama_satker || '').toLowerCase().includes(filterKey.toLowerCase()) || (r.status_tindak_lanjut || '').toLowerCase().includes(filterKey.toLowerCase()));
                }

                renderPotensiDrilldown(filtered);
            }

            $('#modalDrilldownBadge').text(`${filtered.length} NUP / Aset`);
            const modalDom = document.getElementById('modalDrilldown');
            if (modalDom && modalDom.parentElement !== document.body) {
                document.body.appendChild(modalDom);
            }
            const modalEl = bootstrap.Modal.getOrCreateInstance(modalDom);
            modalEl.show();
        };

        // -------------------------------------------------------------
        // HIGHLIGHT SEARCH KEYWORDS IN DRILLDOWN TABLE
        // -------------------------------------------------------------
        function highlightTextInTable(tableSelector, searchTerm) {
            const $table = $(tableSelector);
            // Remove previous highlights
            $table.find('mark.dt-highlight').each(function() {
                const text = $(this).text();
                $(this).replaceWith(text);
            });

            if (!searchTerm || searchTerm.trim().length === 0) return;

            const term = searchTerm.trim().replace(/[.*+?^${}()|[\]\\]/g, '\\$&');

            $table.find('tbody tr td').each(function() {
                highlightNodeInElement(this, term);
            });
        }

        function highlightNodeInElement(node, term) {
            if (node.nodeType === 3) { // Text node
                const text = node.nodeValue;
                if (!text || text.trim().length === 0) return;
                
                const regex = new RegExp(`(${term})`, 'gi');
                if (regex.test(text)) {
                    regex.lastIndex = 0;
                    const span = document.createElement('span');
                    span.innerHTML = text.replace(regex, '<mark class="dt-highlight">$1</mark>');
                    node.parentNode.replaceChild(span, node);
                }
            } else if (node.nodeType === 1 && node.childNodes && !/(script|style|textarea)/i.test(node.tagName) && !node.classList.contains('dt-highlight')) {
                const children = Array.from(node.childNodes);
                for (let i = 0; i < children.length; i++) {
                    highlightNodeInElement(children[i], term);
                }
            }
        }

        function renderPotensiDrilldown(data) {
            if (dtDrilldown) {
                dtDrilldown.destroy();
                $('#tableDrilldown').empty();
            }
            $('#drilldownFooterInfo').empty();
            $('#drilldownFooterPaging').empty();

            const theadHtml = `
                <tr>
                    <th class="text-center" style="width: 40px;">No</th>
                    <th>Satker & K/L</th>
                    <th>Kode Barang / NUP</th>
                    <th>Nama & Kelompok Barang</th>
                    <th class="text-center">Klaster</th>
                    <th>Status TL</th>
                    <th>Hasil Jawaban Satker</th>
                    <th>Status Idle</th>
                </tr>
            `;
            $('#tableDrilldown').html(`<thead>${theadHtml}</thead><tbody></tbody>`);

            let rowsHtml = '';
            if (data.length === 0) {
                rowsHtml = `<tr><td colspan="8" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x mb-2 d-block opacity-50"></i>Tidak ada aset yang sesuai kriteria ini.</td></tr>`;
            } else {
                data.forEach((r, idx) => {
                    let badgeTl = 'bg-secondary';
                    if ((r.status_tindak_lanjut || '').trim() === 'Pemantauan') badgeTl = 'bg-success';
                    else if ((r.status_tindak_lanjut || '').trim() === 'Penelusuran') badgeTl = 'bg-warning text-dark';
                    else if ((r.status_tindak_lanjut || '').trim() === 'Penelitian') badgeTl = 'bg-danger';

                    const isPotensi = (r.status_bmn_idle || '').includes('Potensi') || (r.hasil_jawaban && r.hasil_jawaban.includes('RB tidak'));
                    const badgeIdle = isPotensi ? '<span class="badge bg-warning text-dark">Potensi BMN Idle</span>' : '<span class="badge bg-light text-muted border">Bukan BMN Idle</span>';
                    const klasterBadge = `<span class="badge bg-dark-subtle text-dark border fw-bold">${r.klasterisasi || '-'}</span>`;

                    rowsHtml += `
                        <tr>
                            <td class="text-center fw-bold">${idx + 1}</td>
                            <td>
                                <strong class="text-primary">${r.nama_satker || '-'}</strong>
                                <small class="text-muted d-block">${r.kode_satker || ''} &bull; ${r.kementerian_lembaga || ''}</small>
                            </td>
                            <td>
                                <code>${r.kode_barang || '-'}</code>
                                <div class="fw-bold text-dark">NUP: ${r.nup || '-'}</div>
                            </td>
                            <td>
                                <strong>${r.nama_barang || '-'}</strong>
                                <small class="text-muted d-block"><i class="fas fa-tag me-1"></i>${r.kelompok_barang || '-'}</small>
                            </td>
                            <td class="text-center">
                                ${klasterBadge}
                            </td>
                            <td class="text-center">
                                <span class="badge ${badgeTl} px-2 py-1">${r.status_tindak_lanjut || '-'}</span>
                            </td>
                            <td>
                                <div style="max-width: 260px; font-size: 0.82rem; line-height: 1.35;">${r.hasil_jawaban || '<span class="text-muted fst-italic">Belum ada jawaban</span>'}</div>
                            </td>
                            <td class="text-center">
                                ${badgeIdle}
                            </td>
                        </tr>
                    `;
                });
            }

            $('#tableDrilldown tbody').html(rowsHtml);

            if (data.length > 0) {
                dtDrilldown = $('#tableDrilldown').DataTable({
                    pageLength: 10,
                    lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Semua"]],
                    language: commonTableLang,
                    layout: {
                        topStart: {
                            pageLength: { menu: [10, 25, 50, -1] }
                        },
                        topEnd: 'search',
                        bottomStart: 'info',
                        bottomEnd: 'paging'
                    },
                    drawCallback: function() {
                        const $info = $('#tableDrilldown_wrapper .dt-info');
                        const $paging = $('#tableDrilldown_wrapper .dt-paging');
                        
                        if ($info.length) {
                            $('#drilldownFooterInfo').empty().append($info);
                        }
                        if ($paging.length) {
                            $('#drilldownFooterPaging').empty().append($paging);
                        }

                        const currentSearch = this.api().search();
                        highlightTextInTable('#tableDrilldown', currentSearch);
                    }
                });
            }
        }

        function renderEksDrilldown(data) {
            if (dtDrilldown) {
                dtDrilldown.destroy();
                $('#tableDrilldown').empty();
            }
            $('#drilldownFooterInfo').empty();
            $('#drilldownFooterPaging').empty();

            const theadHtml = `
                <tr>
                    <th class="text-center" style="width: 40px;">No</th>
                    <th>Satker & KPKNL</th>
                    <th>Kode Barang / NUP</th>
                    <th>Uraian Barang</th>
                    <th class="text-end">Luas (m²)</th>
                    <th class="text-end">Nilai Perolehan</th>
                    <th>Pengelolaan</th>
                    <th>Tindak Lanjut</th>
                    <th>No Surat</th>
                </tr>
            `;
            $('#tableDrilldown').html(`<thead>${theadHtml}</thead><tbody></tbody>`);

            let rowsHtml = '';
            if (data.length === 0) {
                rowsHtml = `<tr><td colspan="9" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x mb-2 d-block opacity-50"></i>Tidak ada aset yang sesuai kriteria ini.</td></tr>`;
            } else {
                data.forEach((r, idx) => {
                    const luasFmt = r.luas ? new Intl.NumberFormat('id-ID').format(r.luas) : '-';
                    const nilaiFmt = r.nilai_perolehan ? 'Rp ' + new Intl.NumberFormat('id-ID').format(r.nilai_perolehan) : '-';

                    let badgePengelolaan = 'bg-primary';
                    if ((r.jenis_pengelolaan || '').includes('PSP')) badgePengelolaan = 'bg-primary';
                    else if ((r.jenis_pengelolaan || '').includes('Penghapusan') || (r.jenis_pengelolaan || '').includes('Dipindahtangankan')) badgePengelolaan = 'bg-success';
                    else badgePengelolaan = 'bg-info';

                    rowsHtml += `
                        <tr>
                            <td class="text-center fw-bold">${idx + 1}</td>
                            <td>
                                <strong class="text-dark">${r.nama_satker || '-'}</strong>
                                <small class="text-muted d-block">${r.nama_kpknl || ''}</small>
                            </td>
                            <td>
                                <code>${r.kode_barang || '-'}</code>
                                <div class="fw-bold text-dark">NUP: ${r.nup || '-'}</div>
                            </td>
                            <td>
                                <strong>${r.uraian_barang || '-'}</strong>
                            </td>
                            <td class="text-end fw-semibold">${luasFmt}</td>
                            <td class="text-end fw-bold text-primary">${nilaiFmt}</td>
                            <td>
                                <span class="badge ${badgePengelolaan} px-2 py-1">${r.jenis_pengelolaan || '-'}</span>
                            </td>
                            <td>
                                <small class="fw-semibold">${r.jenis_tindak_lanjut || '-'}</small>
                            </td>
                            <td>
                                <small class="text-muted d-block" style="max-width: 220px; font-size: 0.75rem;">${r.no_surat || '-'}</small>
                            </td>
                        </tr>
                    `;
                });
            }

            $('#tableDrilldown tbody').html(rowsHtml);

            if (data.length > 0) {
                dtDrilldown = $('#tableDrilldown').DataTable({
                    pageLength: 10,
                    lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Semua"]],
                    language: commonTableLang,
                    layout: {
                        topStart: {
                            pageLength: { menu: [10, 25, 50, -1] }
                        },
                        topEnd: 'search',
                        bottomStart: 'info',
                        bottomEnd: 'paging'
                    },
                    drawCallback: function() {
                        const $info = $('#tableDrilldown_wrapper .dt-info');
                        const $paging = $('#tableDrilldown_wrapper .dt-paging');
                        
                        if ($info.length) {
                            $('#drilldownFooterInfo').empty().append($info);
                        }
                        if ($paging.length) {
                            $('#drilldownFooterPaging').empty().append($paging);
                        }

                        const currentSearch = this.api().search();
                        highlightTextInTable('#tableDrilldown', currentSearch);
                    }
                });
            }
        }

        window.exportDrilldownTable = function() {
            const table = document.getElementById('tableDrilldown');
            if (!table) return;

            let csv = [];
            const rows = table.querySelectorAll('tr');
            for (let i = 0; i < rows.length; i++) {
                let row = [], cols = rows[i].querySelectorAll('td, th');
                for (let j = 0; j < cols.length; j++) {
                    let cleanText = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, ' ').replace(/"/g, '""');
                    row.push('"' + cleanText.trim() + '"');
                }
                csv.push(row.join('\t'));
            }

            const csvString = csv.join('\n');
            navigator.clipboard.writeText(csvString).then(() => {
                alert('Tabel rincian aset berhasil disalin ke clipboard! Anda dapat langsung menempelkannya (paste) di Microsoft Excel atau Google Spreadsheet.');
            }).catch(err => {
                console.error('Clipboard copy failed:', err);
            });
        };
    });
</script>
@endpush

@extends('layouts.app')

@section('title', 'Diagram & Analitika Kepegawaian — SI-KEP KPKNL Palembang')
@section('hero-title', 'Diagram & Visualisasi Analitika')
@section('hero-subtitle', 'Pusat eksplorasi data grafis interaktif mengenai demografi, tingkat pendidikan, almamater, hierarki kepangkatan, dan masa penugasan aparatur.')

@section('content')

<!-- Grid Diagram 1: Generasi & Gender -->
<div class="row g-4 mb-4">
    <!-- 1. Piramida Generasi -->
    <div class="col-lg-7">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="fas fa-people-group text-primary me-2"></i>Distribusi Kelompok Generasi &amp; Usia
                </h6>
                <span class="badge bg-light text-secondary border">{{ $totalPegawai }} Personil</span>
            </div>
            <div class="card-body p-4">
                <div style="height: 290px; cursor: pointer;">
                    <canvas id="chartGenerasiFull"></canvas>
                </div>
                <div class="row text-center mt-3 pt-3 border-top g-2">
                    <div class="col-3 cursor-pointer transition-all p-1 rounded" onclick="showAggregateModal('generasi', 'gen_z', 'Personil Generasi Z (< 28 Thn)')" title="Klik untuk melihat personil Generasi Z">
                        <div class="small text-muted" style="font-size: 0.72rem;">GEN Z (&lt;28 Thn)</div>
                        <div class="fw-bold fs-5 text-info">{{ $genZ }}</div>
                    </div>
                    <div class="col-3 cursor-pointer transition-all p-1 rounded" onclick="showAggregateModal('generasi', 'milenial', 'Personil Generasi Milenial (28-43 Thn)')" title="Klik untuk melihat personil Generasi Milenial">
                        <div class="small text-muted" style="font-size: 0.72rem;">MILENIAL (28-43)</div>
                        <div class="fw-bold fs-5 text-primary">{{ $milenial }}</div>
                    </div>
                    <div class="col-3 cursor-pointer transition-all p-1 rounded" onclick="showAggregateModal('generasi', 'gen_x', 'Personil Generasi X (44-59 Thn)')" title="Klik untuk melihat personil Generasi X">
                        <div class="small text-muted" style="font-size: 0.72rem;">GEN X (44-59)</div>
                        <div class="fw-bold fs-5 text-warning">{{ $genX }}</div>
                    </div>
                    <div class="col-3 cursor-pointer transition-all p-1 rounded" onclick="showAggregateModal('generasi', 'boomer', 'Personil Baby Boomer (≥ 60 Thn)')" title="Klik untuk melihat personil Baby Boomer">
                        <div class="small text-muted" style="font-size: 0.72rem;">BOOMER (&ge;60)</div>
                        <div class="fw-bold fs-5 text-danger">{{ $boomer }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Gender Ratio -->
    <div class="col-lg-5">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="fas fa-venus-mars text-primary me-2"></i>Komposisi Gender
                </h6>
                <span class="badge bg-light text-secondary border">L / P</span>
            </div>
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div style="height: 250px; cursor: pointer;">
                    <canvas id="chartGenderFull"></canvas>
                </div>
                <div class="row text-center pt-3 border-top mt-2">
                    <div class="col-6 border-end cursor-pointer transition-all p-2 rounded" onclick="showAggregateModal('gender', 'L', 'Personil Pegawai Laki-laki')" title="Klik untuk melihat pegawai laki-laki">
                        <div class="small text-muted"><i class="fas fa-mars text-primary me-1"></i> Laki-laki</div>
                        <div class="fw-bold fs-4 text-primary">{{ $genderLaki }} <span class="small text-muted fs-6">({{ $totalPegawai > 0 ? round(($genderLaki/$totalPegawai)*100, 1) : 0 }}%)</span></div>
                    </div>
                    <div class="col-6 cursor-pointer transition-all p-2 rounded" onclick="showAggregateModal('gender', 'P', 'Personil Pegawai Perempuan')" title="Klik untuk melihat pegawai perempuan">
                        <div class="small text-muted"><i class="fas fa-venus text-danger me-1"></i> Perempuan</div>
                        <div class="fw-bold fs-4 text-danger">{{ $genderPerempuan }} <span class="small text-muted fs-6">({{ $totalPegawai > 0 ? round(($genderPerempuan/$totalPegawai)*100, 1) : 0 }}%)</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Grid Diagram 2: Pangkat & Pendidikan -->
<div class="row g-4 mb-4">
    <!-- 3. Distribusi Golongan -->
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="fas fa-ranking-star text-primary me-2"></i>Sebaran Pangkat &amp; Golongan Ruang
                </h6>
                <span class="badge bg-light text-secondary border">Jenjang Pangkat</span>
            </div>
            <div class="card-body p-4">
                <div style="height: 280px; cursor: pointer;">
                    <canvas id="chartGolongan"></canvas>
                </div>
                <div class="text-center mt-2">
                    <small class="text-muted" style="font-size: 0.72rem;">* Klik batang grafik untuk melihat personil pada golongan tersebut</small>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Tingkat Pendidikan -->
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="fas fa-graduation-cap text-primary me-2"></i>Kualifikasi Tingkat Pendidikan Terakhir
                </h6>
                <span class="badge bg-light text-secondary border">Jenjang Kelulusan</span>
            </div>
            <div class="card-body p-4">
                <div style="height: 280px; cursor: pointer;">
                    <canvas id="chartPendidikan"></canvas>
                </div>
                <div class="text-center mt-2">
                    <small class="text-muted" style="font-size: 0.72rem;">* Klik juring diagram untuk melihat personil jenjang pendidikan tersebut</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Grid Diagram 3: Almamater & Tour of Duty -->
<div class="row g-4 mb-4">
    <!-- 5. Top Perguruan Tinggi / Universitas -->
    <div class="col-lg-7">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="fas fa-building-columns text-primary me-2"></i>Top Almamater / Perguruan Tinggi Terbanyak
                </h6>
                <span class="badge bg-light text-secondary border">Kampus Kelulusan</span>
            </div>
            <div class="card-body p-4">
                <div style="height: 300px; cursor: pointer;">
                    <canvas id="chartUniv"></canvas>
                </div>
                <div class="text-center mt-2">
                    <small class="text-muted" style="font-size: 0.72rem;">* Klik batang kampus untuk melihat personil alumni</small>
                </div>
            </div>
        </div>
    </div>

    <!-- 6. Tour of Duty (Masa Tugas di Palembang) -->
    <div class="col-lg-5">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="fas fa-arrows-split-up-and-left text-primary me-2"></i>Masa Penugasan di Palembang (Tour of Duty)
                </h6>
                <span class="badge bg-light text-secondary border">Kesiapan Mutasi</span>
            </div>
            <div class="card-body p-4">
                <div style="height: 300px; cursor: pointer;">
                    <canvas id="chartTourOfDuty"></canvas>
                </div>
                <div class="text-center mt-2">
                    <small class="text-muted" style="font-size: 0.72rem;">* Klik kategori donat untuk melihat personil masa tugas tersebut</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Grid Diagram 4: Formasi Unit Kerja & Job Grading (Dipindah dari menu Jabatan) -->
<div class="row g-4 mb-4">
    <!-- 7. Formasi Unit Kerja -->
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="fas fa-sitemap text-primary me-2"></i>Formasi Kekuatan Personil per Seksi &amp; Subbagian
                </h6>
                <span class="badge bg-light text-secondary border">Total Personil</span>
            </div>
            <div class="card-body p-4">
                <div style="height: 300px; cursor: pointer;">
                    <canvas id="chartUnit"></canvas>
                </div>
                <div class="text-center mt-2">
                    <small class="text-muted" style="font-size: 0.72rem;">* Klik batang unit kerja untuk memfilter daftar aparatur</small>
                </div>
            </div>
        </div>
    </div>

    <!-- 8. Distribusi Kelas Jabatan (Job Grading Kemenkeu - Pindahan dari Jabatan) -->
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle p-2 bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i class="fas fa-chart-pie" style="font-size: 0.85rem;"></i>
                    </div>
                    <h6 class="mb-0 fw-bold text-dark">
                        Distribusi Kelas Jabatan (Job Grading Kemenkeu)
                    </h6>
                </div>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1">
                    Remunerasi DJKN
                </span>
            </div>
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <!-- Doughnut Canvas Container with Central Label -->
                <div class="position-relative my-auto text-center" style="min-height: 220px; cursor: pointer;">
                    <div style="position: relative; height: 210px; max-width: 230px; margin: 0 auto;">
                        <canvas id="chartGradingDoughnut"></canvas>
                        <!-- Central Hole Overlay -->
                        <div class="position-absolute top-50 start-50 translate-middle text-center" style="pointer-events: none;">
                            <div class="fs-4 fw-bold text-dark lh-1">{{ $grades->sum('total') }}</div>
                            <small class="text-muted fw-semibold" style="font-size: 0.65rem; letter-spacing: 0.05em;">PEGAWAI</small>
                            <div class="text-primary fw-bold" style="font-size: 0.62rem;">{{ $grades->count() }} GRADES</div>
                        </div>
                    </div>
                </div>

                <!-- Clean Horizontal Grade Chips (Clickable) -->
                <div class="mt-3 pt-3 border-top">
                    <div class="small fw-bold text-muted text-uppercase mb-2 text-center" style="font-size: 0.68rem; letter-spacing: 0.05em;">
                        Sebaran Personil per Kelas Jabatan (Grade) &bull; Klik untuk detail
                    </div>
                    <div class="d-flex flex-wrap justify-content-center gap-2">
                        @php
                            $palette = [
                                '#0c306b', '#1e40af', '#2563eb', '#3b82f6', '#60a5fa',
                                '#059669', '#10b981', '#34d399', '#d97706', '#f59e0b',
                                '#ef4444', '#8b5cf6', '#a855f7', '#6366f1'
                            ];
                        @endphp
                        @foreach($grades as $idx => $g)
                            @php $color = $palette[$idx % count($palette)]; @endphp
                            <div class="d-inline-flex align-items-center gap-2 bg-light border px-2 py-1 rounded-pill small cursor-pointer transition-all"
                                 onclick="showAggregateModal('job_grade', '{{ $g->job_grade }}', 'Personil Kelas Jabatan Grade {{ $g->job_grade }}')"
                                 title="Klik untuk melihat pegawai Grade {{ $g->job_grade }}">
                                <span style="width: 10px; height: 10px; border-radius: 50%; background-color: {{ $color }}; display: inline-block;"></span>
                                <span class="fw-semibold text-dark" style="font-size: 0.75rem;">Grade {{ $g->job_grade }}</span>
                                <span class="badge bg-white text-dark border px-2 py-0 fw-bold" style="font-size: 0.72rem;">{{ $g->total }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // 1. Chart Generasi Full
        new Chart(document.getElementById('chartGenerasiFull').getContext('2d'), {
            type: 'bar',
            data: {
                labels: ['Gen Z (<28 th)', 'Milenial (28-43 th)', 'Gen X (44-59 th)', 'Boomer (≥60 th)'],
                datasets: [{
                    label: 'Jumlah Pegawai',
                    data: [{{ $genZ }}, {{ $milenial }}, {{ $genX }}, {{ $boomer }}],
                    backgroundColor: ['#38bdf8', '#0284c7', '#f59e0b', '#ef4444'],
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 2 } } },
                onClick: (evt, elements) => {
                    if (elements.length > 0) {
                        const idx = elements[0].index;
                        const keys = ['gen_z', 'milenial', 'gen_x', 'boomer'];
                        const titles = ['Generasi Z (< 28 Thn)', 'Generasi Milenial (28 - 43 Thn)', 'Generasi X (44 - 59 Thn)', 'Baby Boomer (≥ 60 Thn)'];
                        showAggregateModal('generasi', keys[idx], titles[idx]);
                    }
                }
            }
        });

        // 2. Chart Gender Full
        new Chart(document.getElementById('chartGenderFull').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Laki-laki', 'Perempuan'],
                datasets: [{
                    data: [{{ $genderLaki }}, {{ $genderPerempuan }}],
                    backgroundColor: ['#0c306b', '#e11d48'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 14, font: { weight: '600', family: 'Outfit' } } }
                },
                cutout: '68%',
                onClick: (evt, elements) => {
                    if (elements.length > 0) {
                        const idx = elements[0].index;
                        const val = idx === 0 ? 'L' : 'P';
                        const title = idx === 0 ? 'Personil Pegawai Laki-laki' : 'Personil Pegawai Perempuan';
                        showAggregateModal('gender', val, title);
                    }
                }
            }
        });

        // 3. Chart Golongan
        const golLabels = {!! json_encode($golonganDist->pluck('golongan_ruang')) !!};
        const golData = {!! json_encode($golonganDist->pluck('pegawai_count')) !!};

        new Chart(document.getElementById('chartGolongan').getContext('2d'), {
            type: 'bar',
            data: {
                labels: golLabels,
                datasets: [{
                    label: 'Jumlah Pegawai',
                    data: golData,
                    backgroundColor: '#1e40af',
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 2 } } },
                onClick: (evt, elements) => {
                    if (elements.length > 0) {
                        const idx = elements[0].index;
                        const gol = golLabels[idx];
                        showAggregateModal('golongan', gol, 'Pegawai Golongan Ruang ' + gol);
                    }
                }
            }
        });

        // 4. Chart Pendidikan
        const pendLabels = {!! json_encode($pendidikanDist->pluck('pendidikan_terakhir')) !!};
        const pendData = {!! json_encode($pendidikanDist->pluck('total')) !!};

        new Chart(document.getElementById('chartPendidikan').getContext('2d'), {
            type: 'pie',
            data: {
                labels: pendLabels,
                datasets: [{
                    data: pendData,
                    backgroundColor: ['#0284c7', '#10b981', '#f59e0b', '#6366f1', '#ec4899'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'right', labels: { boxWidth: 14, font: { weight: '600', family: 'Outfit' } } }
                },
                onClick: (evt, elements) => {
                    if (elements.length > 0) {
                        const idx = elements[0].index;
                        const pend = pendLabels[idx];
                        showAggregateModal('pendidikan', pend, 'Pegawai Kualifikasi Pendidikan ' + pend);
                    }
                }
            }
        });

        // 5. Chart Universitas
        const univLabels = {!! json_encode($univDist->pluck('nama_universitas')) !!};
        const univData = {!! json_encode($univDist->pluck('total')) !!};

        new Chart(document.getElementById('chartUniv').getContext('2d'), {
            type: 'bar',
            indexAxis: 'y',
            data: {
                labels: univLabels,
                datasets: [{
                    label: 'Lulusan',
                    data: univData,
                    backgroundColor: '#0c306b',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } },
                onClick: (evt, elements) => {
                    if (elements.length > 0) {
                        const idx = elements[0].index;
                        const u = univLabels[idx];
                        showAggregateModal('kampus', u, 'Almamater Kampus ' + u);
                    }
                }
            }
        });

        // 6. Chart Tour of Duty
        new Chart(document.getElementById('chartTourOfDuty').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['< 1 Tahun', '1 - 2 Tahun', '3 - 4 Tahun', '> 4 Tahun (Siap Rotasi)'],
                datasets: [{
                    data: [{{ $todUnder1 }}, {{ $tod1to2 }}, {{ $tod2to4 }}, {{ $todOver4 }}],
                    backgroundColor: ['#10b981', '#0ea5e9', '#f59e0b', '#dc2626'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 14, font: { weight: '600', family: 'Outfit' } } }
                },
                cutout: '62%',
                onClick: (evt, elements) => {
                    if (elements.length > 0) {
                        const idx = elements[0].index;
                        const keys = ['lt_1', '1_2', '2_4', 'gt_4'];
                        const titles = ['Masa Tugas < 1 Tahun', 'Masa Tugas 1 - 2 Tahun', 'Masa Tugas 3 - 4 Tahun', 'Masa Tugas > 4 Tahun (Siap Rotasi)'];
                        showAggregateModal('masa_tugas', keys[idx], titles[idx]);
                    }
                }
            }
        });

        // 7. Chart Unit Kerja
        const unitLabels = {!! json_encode($unitDist->pluck('singkatan')) !!};
        const unitData = {!! json_encode($unitDist->pluck('pegawai_count')) !!};

        new Chart(document.getElementById('chartUnit').getContext('2d'), {
            type: 'bar',
            data: {
                labels: unitLabels,
                datasets: [{
                    label: 'Jumlah Personil',
                    data: unitData,
                    backgroundColor: '#1e40af',
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
                onClick: (evt, elements) => {
                    if (elements.length > 0) {
                        const idx = elements[0].index;
                        const unitName = unitLabels[idx];
                        showAggregateModal('unit_kerja', unitName, 'Personil Formasi Unit: ' + unitName);
                    }
                }
            }
        });

        // 8. Chart Grading Doughnut (Dipindah dari Jabatan)
        const ctxGrading = document.getElementById('chartGradingDoughnut');
        if (ctxGrading) {
            const gradeLabels = {!! json_encode($grades->map(fn($g) => 'Grade ' . $g->job_grade)) !!};
            const gradeData = {!! json_encode($grades->pluck('total')) !!};

            const colors = [
                '#0c306b', '#1e40af', '#2563eb', '#3b82f6', '#60a5fa',
                '#059669', '#10b981', '#34d399', '#d97706', '#f59e0b',
                '#ef4444', '#8b5cf6', '#a855f7', '#6366f1'
            ];

            new Chart(ctxGrading.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: gradeLabels,
                    datasets: [{
                        data: gradeData,
                        backgroundColor: colors.slice(0, gradeLabels.length),
                        borderWidth: 2,
                        borderColor: '#ffffff',
                        hoverOffset: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const val = context.parsed;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
                                    return ` ${context.label}: ${val} Pegawai (${percentage}%)`;
                                }
                            }
                        }
                    },
                    cutout: '72%',
                    onClick: (evt, elements) => {
                        if (elements.length > 0) {
                            const idx = elements[0].index;
                            const rawGrade = gradeLabels[idx].replace('Grade ', '');
                            showAggregateModal('job_grade', rawGrade, 'Personil Kelas Jabatan (Grade ' + rawGrade + ')');
                        }
                    }
                }
            });
        }
    });
</script>
@endpush

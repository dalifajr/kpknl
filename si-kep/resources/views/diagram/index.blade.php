@extends('layouts.app')

@section('title', 'Diagram & Analitika Kepegawaian — SI-KEP KPKNL Palembang')
@section('hero-title', 'Diagram & Visualisasi Analitika')
@section('hero-subtitle', 'Pusat eksplorasi data grafis interaktif mengenai demografi, tingkat pendidikan, almamater, hierarki kepangkatan, dan masa penugasan aparatur.')

@section('content')

<!-- Grid Diagram 1: Generasi & Gender (From referensi_desain) -->
<div class="row g-4 mb-4">
    <!-- 1. Piramida Generasi -->
    <div class="col-lg-7">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="fas fa-people-group text-primary me-2"></i>Distribusi Kelompok Generasi &amp; Usia
                </h6>
                <span class="badge bg-light text-secondary border">33 ASN</span>
            </div>
            <div class="card-body p-4">
                <div style="height: 290px;">
                    <canvas id="chartGenerasiFull"></canvas>
                </div>
                <div class="row text-center mt-3 pt-3 border-top g-2">
                    <div class="col-3">
                        <div class="small text-muted" style="font-size: 0.72rem;">GEN Z (&lt;28 Thn)</div>
                        <div class="fw-bold fs-5 text-info">{{ $genZ }}</div>
                    </div>
                    <div class="col-3">
                        <div class="small text-muted" style="font-size: 0.72rem;">MILENIAL (28-43)</div>
                        <div class="fw-bold fs-5 text-primary">{{ $milenial }}</div>
                    </div>
                    <div class="col-3">
                        <div class="small text-muted" style="font-size: 0.72rem;">GEN X (44-59)</div>
                        <div class="fw-bold fs-5 text-warning">{{ $genX }}</div>
                    </div>
                    <div class="col-3">
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
                <div style="height: 250px;">
                    <canvas id="chartGenderFull"></canvas>
                </div>
                <div class="row text-center pt-3 border-top mt-2">
                    <div class="col-6 border-end">
                        <div class="small text-muted"><i class="fas fa-mars text-primary me-1"></i> Laki-laki</div>
                        <div class="fw-bold fs-4 text-primary">{{ $genderLaki }} <span class="small text-muted fs-6">({{ $totalPegawai > 0 ? round(($genderLaki/$totalPegawai)*100, 1) : 0 }}%)</span></div>
                    </div>
                    <div class="col-6">
                        <div class="small text-muted"><i class="fas fa-venus text-danger me-1"></i> Perempuan</div>
                        <div class="fw-bold fs-4 text-danger">{{ $genderPerempuan }} <span class="small text-muted fs-6">({{ $totalPegawai > 0 ? round(($genderPerempuan/$totalPegawai)*100, 1) : 0 }}%)</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Grid Diagram 2: Pangkat & Pendidikan (From referensi_desain) -->
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
                <div style="height: 280px;">
                    <canvas id="chartGolongan"></canvas>
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
                <div style="height: 280px;">
                    <canvas id="chartPendidikan"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Grid Diagram 3: Almamater & Tour of Duty (From referensi_desain) -->
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
                <div style="height: 300px;">
                    <canvas id="chartUniv"></canvas>
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
                <div style="height: 300px;">
                    <canvas id="chartTourOfDuty"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Grid Diagram 4: Formasi Unit Kerja (From referensi_desain) -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold text-dark">
            <i class="fas fa-sitemap text-primary me-2"></i>Formasi Kekuatan Personil per Seksi &amp; Subbagian
        </h6>
        <span class="badge bg-light text-secondary border">Total Personil</span>
    </div>
    <div class="card-body p-4">
        <div style="height: 280px;">
            <canvas id="chartUnit"></canvas>
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
                scales: { y: { beginAtZero: true, ticks: { stepSize: 2 } } }
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
                cutout: '68%'
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
                scales: { y: { beginAtZero: true, ticks: { stepSize: 2 } } }
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
                scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } }
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
                cutout: '62%'
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
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });
    });
</script>
@endpush

@extends('layouts.app')

@section('title', 'Dashboard Statistik & Analitik')

@push('styles')
<style>
    .metric-card {
        border-radius: 8px;
        padding: 20px;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .metric-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 15px rgba(0,0,0,0.12);
    }
    .red-dot-pulsing {
        display: inline-block;
        width: 10px;
        height: 10px;
        background-color: #e53935;
        border-radius: 50%;
        margin-right: 6px;
        box-shadow: 0 0 0 rgba(229, 57, 53, 0.4);
        animation: pulse-red 1.6s infinite;
        vertical-align: middle;
    }
    @keyframes pulse-red {
        0% { box-shadow: 0 0 0 0 rgba(229, 57, 53, 0.7); }
        70% { box-shadow: 0 0 0 7px rgba(229, 57, 53, 0); }
        100% { box-shadow: 0 0 0 0 rgba(229, 57, 53, 0); }
    }
</style>
@endpush

@section('content')
    <div class="row" style="margin-bottom: 25px; margin-top: 15px;">
        <div class="col s12">
            <div class="card-panel blue lighten-5" style="border-left: 5px solid #1565c0; border-radius: 6px; padding: 20px 25px;">
                <h4 class="blue-text text-darken-3" style="margin-top: 0; font-weight: 700; font-size: 1.8rem;">
                    Selamat Datang, {{ auth()->user()->name ?? 'Pengguna' }}!
                </h4>
                <p style="font-size: 1.05rem; margin-bottom: 0;" class="grey-text text-darken-2">
                    Monitoring dan rekapitulasi data aset kelolaan Eks BPPN KPKNL Palembang.
                </p>
            </div>
        </div>
    </div>

    {{-- Banner Alert jika ada Kontrak Sewa yang Perlu Perpanjangan (<= 6 bulan) --}}
    @if(isset($expiringLeases) && count($expiringLeases) > 0)
    <div class="row">
        <div class="col s12">
            <div class="card-panel amber lighten-5 orange-text text-darken-4" style="border-left: 5px solid #f57c00; border-radius: 6px; padding: 16px 20px; margin-bottom: 25px;">
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <span class="red-dot-pulsing" style="width: 14px; height: 14px;"></span>
                        <div>
                            <strong style="font-size: 1.05rem;">PERINGATAN: Terdapat {{ count($expiringLeases) }} Aset Disewakan yang Memerlukan Perpanjangan Kontrak (&le; 6 Bulan)!</strong>
                            <div style="font-size: 0.9rem; margin-top: 3px; color: #e65100;">
                                Beberapa kontrak sewa akan segera berakhir atau telah jatuh tempo. Silakan lakukan perpanjangan kontrak pada menu Gudang Aset.
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('assets.index', ['kondisi_aset' => 'DISEWAKAN']) }}" class="btn orange darken-3 waves-effect waves-light" style="border-radius: 4px;">
                        <i class="material-icons left">visibility</i> Lihat Aset Disewakan
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Quick Metric Cards --}}
    <div class="row">
        <!-- Total Aset -->
        <div class="col s12 m6 l3">
            <div class="card-panel blue darken-2 white-text center-align metric-card">
                <i class="material-icons" style="font-size: 42px;">assessment</i>
                <h6 style="margin-top: 5px; font-weight: 500;">Total Aset Terdata</h6>
                <h3 style="margin: 5px 0; font-weight: 700;">{{ \App\Models\Asset::count() }}</h3>
                <p style="margin: 0; font-size: 0.85rem; opacity: 0.9;">Semua Aset Fisik</p>
            </div>
        </div>

        <!-- Aset Tanah -->
        <div class="col s12 m6 l3">
            <div class="card-panel green darken-2 white-text center-align metric-card">
                <i class="material-icons" style="font-size: 42px;">landscape</i>
                <h6 style="margin-top: 5px; font-weight: 500;">Aset Tanah</h6>
                <h3 style="margin: 5px 0; font-weight: 700;">{{ \App\Models\Asset::where('jenis_aset', 'Tanah')->count() }}</h3>
                <p style="margin: 0; font-size: 0.85rem; opacity: 0.9;">Tanpa Bangunan</p>
            </div>
        </div>

        <!-- Aset Bangunan -->
        <div class="col s12 m6 l3">
            <div class="card-panel orange darken-2 white-text center-align metric-card">
                <i class="material-icons" style="font-size: 42px;">domain</i>
                <h6 style="margin-top: 5px; font-weight: 500;">Aset Bangunan</h6>
                <h3 style="margin: 5px 0; font-weight: 700;">{{ \App\Models\Asset::where('jenis_aset', '!=', 'Tanah')->count() }}</h3>
                <p style="margin: 0; font-size: 0.85rem; opacity: 0.9;">Bangunan / T&B</p>
            </div>
        </div>

        <!-- Aset Disewakan -->
        <div class="col s12 m6 l3">
            <div class="card-panel teal darken-2 white-text center-align metric-card">
                <i class="material-icons" style="font-size: 42px;">real_estate_agent</i>
                <h6 style="margin-top: 5px; font-weight: 500;">Aset Disewakan</h6>
                <h3 style="margin: 5px 0; font-weight: 700;">{{ \App\Models\Asset::where('kondisi_aset', 'DISEWAKAN')->count() }}</h3>
                <p style="margin: 0; font-size: 0.85rem; opacity: 0.9;">Total Rp {{ number_format($totalSewaNilai ?? 0, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    {{-- Kondisi Aset Breakdown (6 Kondisi) --}}
    <div class="row">
        <div class="col s12">
            <div class="card z-depth-1" style="border-radius: 8px;">
                <div class="card-content">
                    <span class="card-title" style="font-weight: 700; font-size: 1.25rem; margin-bottom: 20px; color: #1565c0; display: flex; align-items: center; gap: 8px;">
                        <i class="material-icons">pie_chart</i> Distribusi Kondisi Aset
                    </span>
                    <div class="row" style="margin-bottom: 0;">
                        <div class="col s6 m4 l2" style="margin-bottom: 10px;">
                            <div class="card-panel center-align z-depth-0" style="border: 1px solid #bbdefb; border-radius: 6px; padding: 12px 6px;">
                                <h4 class="blue-text text-darken-3" style="margin: 0; font-weight: 700;">{{ \App\Models\Asset::where('kondisi_aset', 'DIHUNI')->count() }}</h4>
                                <span style="font-weight: 600; font-size: 0.9rem; color: #1565c0;">DIHUNI</span>
                            </div>
                        </div>
                        <div class="col s6 m4 l2" style="margin-bottom: 10px;">
                            <div class="card-panel center-align z-depth-0" style="border: 1px solid #c8e6c9; border-radius: 6px; padding: 12px 6px;">
                                <h4 class="green-text text-darken-3" style="margin: 0; font-weight: 700;">{{ \App\Models\Asset::where('kondisi_aset', 'DIGUNAKAN PIHAK KETIGA')->count() }}</h4>
                                <span style="font-weight: 600; font-size: 0.85rem; color: #2e7d32;">PIHAK KETIGA</span>
                            </div>
                        </div>
                        <div class="col s6 m4 l2" style="margin-bottom: 10px;">
                            <div class="card-panel center-align z-depth-0" style="border: 1px solid #ffcdd2; border-radius: 6px; padding: 12px 6px;">
                                <h4 class="red-text text-darken-3" style="margin: 0; font-weight: 700;">{{ \App\Models\Asset::where('kondisi_aset', 'KOSONG')->count() }}</h4>
                                <span style="font-weight: 600; font-size: 0.9rem; color: #c62828;">KOSONG</span>
                            </div>
                        </div>
                        <div class="col s6 m4 l2" style="margin-bottom: 10px;">
                            <div class="card-panel center-align z-depth-0" style="border: 1px solid #b2dfdb; border-radius: 6px; padding: 12px 6px;">
                                <h4 class="teal-text text-darken-3" style="margin: 0; font-weight: 700;">{{ \App\Models\Asset::where('kondisi_aset', 'DISEWAKAN')->count() }}</h4>
                                <span style="font-weight: 600; font-size: 0.9rem; color: #00695c;">DISEWAKAN</span>
                            </div>
                        </div>
                        <div class="col s6 m4 l2" style="margin-bottom: 10px;">
                            <div class="card-panel center-align z-depth-0" style="border: 1px solid #ffe0b2; border-radius: 6px; padding: 12px 6px;">
                                <h4 class="orange-text text-darken-4" style="margin: 0; font-weight: 700;">{{ \App\Models\Asset::where('kondisi_aset', 'DILELANG')->count() }}</h4>
                                <span style="font-weight: 600; font-size: 0.9rem; color: #e65100;">DILELANG</span>
                            </div>
                        </div>
                        <div class="col s6 m4 l2" style="margin-bottom: 10px;">
                            <div class="card-panel center-align z-depth-0" style="border: 1px solid #e0e0e0; border-radius: 6px; padding: 12px 6px;">
                                <h4 class="grey-text text-darken-3" style="margin: 0; font-weight: 700;">{{ \App\Models\Asset::where('kondisi_aset', 'LAIN-LAIN')->count() }}</h4>
                                <span style="font-weight: 600; font-size: 0.9rem; color: #616161;">LAIN-LAIN</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Grafik Tren Nilai Sewa Aset Berdasarkan Nilai Kontrak --}}
    <div class="row">
        <!-- Grafik Tren Total Nilai Sewa Tahunan -->
        <div class="col s12 l7">
            <div class="card z-depth-1" style="border-radius: 8px;">
                <div class="card-content">
                    <span class="card-title" style="font-weight: 700; font-size: 1.15rem; color: #1565c0; margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">
                        <i class="material-icons">trending_up</i> Tren Nilai Kontrak Sewa per Tahun
                    </span>
                    <div style="position: relative; height: 300px; width: 100%;">
                        <canvas id="dashboardLeaseTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Komparasi Nilai Sewa Aset Aktif -->
        <div class="col s12 l5">
            <div class="card z-depth-1" style="border-radius: 8px;">
                <div class="card-content">
                    <span class="card-title" style="font-weight: 700; font-size: 1.15rem; color: #00695c; margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">
                        <i class="material-icons">bar_chart</i> Komparasi Kontrak Aset Disewakan
                    </span>
                    <div style="position: relative; height: 300px; width: 100%;">
                        <canvas id="dashboardLeaseAssetChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Data Tren Tahunan dari Server
        @php
            $yearlyLabels = ($leaseTrend ?? collect())->pluck('tahun');
            $yearlyValues = ($leaseTrend ?? collect())->pluck('total_nilai');
            
            // Fallback jika belum ada data multi-tahun
            if ($yearlyLabels->isEmpty()) {
                $yearlyLabels = [date('Y')];
                $yearlyValues = [$totalSewaNilai ?? 0];
            }
            
            $leasedAssetCodes = ($leasedAssets ?? collect())->pluck('kode_aset');
            $leasedAssetValues = ($leasedAssets ?? collect())->pluck('sewa_lelang_nilai');
        @endphp

        let trendLabels = {!! json_encode($yearlyLabels) !!};
        let trendValues = {!! json_encode($yearlyValues) !!};

        let ctxTrend = document.getElementById('dashboardLeaseTrendChart');
        if (ctxTrend) {
            new Chart(ctxTrend, {
                type: 'line',
                data: {
                    labels: trendLabels,
                    datasets: [{
                        label: 'Total Nilai Kontrak Sewa (Rp)',
                        data: trendValues,
                        borderColor: '#1565c0',
                        backgroundColor: 'rgba(21, 101, 192, 0.15)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.35,
                        pointBackgroundColor: '#0d47a1',
                        pointRadius: 6,
                        pointHoverRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: {
                        padding: {
                            left: 10,
                            right: 15,
                            top: 5,
                            bottom: 5
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    if (value >= 1000000) {
                                        return 'Rp ' + (value / 1000000).toLocaleString('id-ID') + ' Jt';
                                    }
                                    return 'Rp ' + Number(value).toLocaleString('id-ID');
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: { display: true, position: 'top' },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return ' Total Sewa: Rp ' + Number(context.raw).toLocaleString('id-ID', {minimumFractionDigits: 2});
                                }
                            }
                        }
                    }
                }
            });
        }

        // Komparasi per Aset
        let assetCodes = {!! json_encode($leasedAssetCodes) !!};
        let assetValues = {!! json_encode($leasedAssetValues) !!};

        let ctxAsset = document.getElementById('dashboardLeaseAssetChart');
        if (ctxAsset) {
            new Chart(ctxAsset, {
                type: 'bar',
                data: {
                    labels: assetCodes.length > 0 ? assetCodes : ['Belum Ada Data'],
                    datasets: [{
                        label: 'Nilai Sewa (Rp)',
                        data: assetValues.length > 0 ? assetValues : [0],
                        backgroundColor: [
                            'rgba(0, 150, 136, 0.7)',
                            'rgba(33, 150, 243, 0.7)',
                            'rgba(255, 152, 0, 0.7)',
                            'rgba(156, 39, 176, 0.7)',
                            'rgba(76, 175, 80, 0.7)'
                        ],
                        borderColor: [
                            '#00796b',
                            '#1976d2',
                            '#f57c00',
                            '#7b1fa2',
                            '#388e3c'
                        ],
                        borderWidth: 1.5,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: {
                        padding: {
                            left: 10,
                            right: 15,
                            top: 5,
                            bottom: 5
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    if (value >= 1000000) {
                                        return 'Rp ' + (value / 1000000).toLocaleString('id-ID') + ' Jt';
                                    }
                                    return 'Rp ' + Number(value).toLocaleString('id-ID');
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return ' Nilai: Rp ' + Number(context.raw).toLocaleString('id-ID', {minimumFractionDigits: 2});
                                }
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush

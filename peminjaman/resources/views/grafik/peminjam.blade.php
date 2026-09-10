@extends('layouts.app')

@section('title', 'Grafik Peminjam Risalah')

@section('content')
<div style="display: flex; justify-content: flex-end; margin-bottom: 16px;">
    <a href="{{ route('grafik.index') }}" class="btn btn-outline">
        ← Kembali ke Informasi Grafik
    </a>
</div>

<div class="card" style="max-width: 960px; margin: 0 auto 24px;">
    <div class="card-header">
        <div>
            <h1 class="card-title">GRAFIK PEMINJAM</h1>
            <p style="color: var(--text-muted); font-size: 0.875rem;">Statistik peminjaman berkas fisik bulanan dan tahunan per nama pengguna.</p>
        </div>
    </div>
    
    <form method="GET" action="{{ route('grafik.peminjam') }}" style="background: var(--bg-subtle); padding: 22px; border-radius: var(--radius-md); border: 1px solid var(--border-color); margin-bottom: 24px;">
        <div class="form-group">
            <label class="form-label" for="peminjam">Pilih Nama Peminjam:</label>
            <select name="peminjam" id="peminjam" class="form-control" required>
                <option value="">-- Pilih Nama Peminjam --</option>
                @foreach ($peminjamList as $name)
                    <option value="{{ $name }}" {{ $peminjam === $name ? 'selected' : '' }}>
                        {{ $name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div style="display: flex; gap: 24px; margin: 16px 0 20px; flex-wrap: wrap;">
            <label style="display: flex; align-items: center; gap: 8px; font-weight: 600; font-size: 0.9rem; cursor: pointer; color: var(--text-main);">
                <input type="radio" name="jenis" value="bulanan" {{ $jenis === 'bulanan' || empty($jenis) ? 'checked' : '' }} style="accent-color: var(--primary-accent); width: 18px; height: 18px;"> 
                Grafik Bulanan
            </label>
            <label style="display: flex; align-items: center; gap: 8px; font-weight: 600; font-size: 0.9rem; cursor: pointer; color: var(--text-main);">
                <input type="radio" name="jenis" value="tahunan" {{ $jenis === 'tahunan' ? 'checked' : '' }} style="accent-color: var(--primary-accent); width: 18px; height: 18px;"> 
                Grafik Tahunan
            </label>
        </div>

        <button type="submit" class="btn btn-primary" style="padding: 10px 24px;">
            📊 Tampilkan Grafik
        </button>
    </form>

    @if ($jenis === 'bulanan' && !empty($dataBulanan))
        <div style="text-align: center; margin: 20px 0 10px;">
            <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--primary-accent); letter-spacing: -0.01em;">
                GRAFIK BULANAN {{ strtoupper($peminjam) }}
            </h3>
        </div>
        
        <div class="year-pills-container">
            @foreach ($dataBulanan as $tahun => $bulanan)
                <button type="button" class="year-pill btn-tahun-peminjam" onclick="showChart({{ $tahun }}, this)">
                    {{ $tahun }}
                </button>
            @endforeach
        </div>

        <div class="chart-card" style="position: relative; height: 440px; width: 100%;">
            <canvas id="chartBulanan"></canvas>
        </div>
    @elseif ($jenis === 'tahunan' && !empty($dataTahunan))
        <div style="text-align: center; margin: 20px 0 15px;">
            <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--primary-accent); letter-spacing: -0.01em;">
                GRAFIK TAHUNAN {{ strtoupper($peminjam) }}
            </h3>
        </div>
        
        <div class="chart-card" style="position: relative; height: 440px; width: 100%;">
            <canvas id="chartTahunan"></canvas>
        </div>
    @elseif ($peminjam && empty($dataBulanan) && empty($dataTahunan))
        <div style="padding: 30px; text-align: center; color: var(--text-muted);">
            Tidak ditemukan data peminjaman untuk pengguna ini.
        </div>
    @endif
</div>
@endsection

@push('scripts')
@if ($jenis === 'bulanan' && !empty($dataBulanan))
<script>
    const allCharts = @json($dataBulanan);
    const ctx = document.getElementById('chartBulanan').getContext('2d');
    let chartInstance;

    function showChart(tahun, el) {
        const bulanLabels = ['JAN', 'FEB', 'MAR', 'APR', 'MEI', 'JUN', 'JUL', 'AGS', 'SEP', 'OKT', 'NOV', 'DES'];
        const dataBulan = Array(12).fill(0);
        const tahunData = allCharts[tahun] || {};
        for (let i in tahunData) {
            dataBulan[i - 1] = tahunData[i];
        }

        if (chartInstance) {
            chartInstance.destroy();
        }

        chartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: bulanLabels,
                datasets: [{
                    label: 'Jumlah Peminjaman Tahun ' + tahun,
                    data: dataBulan,
                    borderColor: '#0284c7',
                    backgroundColor: 'rgba(2, 132, 199, 0.15)',
                    pointBackgroundColor: '#0284c7',
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    fill: true,
                    tension: 0.25,
                    borderWidth: 2.5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { font: { family: "'Plus Jakarta Sans', sans-serif", weight: '600', size: 12 }, usePointStyle: true }
                    },
                    tooltip: { padding: 12, titleFont: { family: "'Plus Jakarta Sans', sans-serif", weight: '700' } }
                },
                scales: {
                    x: {
                        grid: { color: 'rgba(226, 232, 240, 0.6)' },
                        ticks: { font: { family: "'Plus Jakarta Sans', sans-serif", weight: '600' } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(226, 232, 240, 0.6)' },
                        ticks: { precision: 0, font: { family: "'Plus Jakarta Sans', sans-serif" } }
                    }
                }
            }
        });

        const buttons = document.querySelectorAll('.btn-tahun-peminjam');
        buttons.forEach(btn => btn.classList.remove('active'));
        if (el) {
            el.classList.add('active');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const buttons = document.querySelectorAll('.btn-tahun-peminjam');
        if (buttons.length > 0) {
            const firstYear = Object.keys(allCharts)[0];
            showChart(firstYear, buttons[0]);
        }
    });
</script>
@elseif ($jenis === 'tahunan' && !empty($dataTahunan))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tahunanLabels = @json(array_column($dataTahunan, 'tahun'));
        const tahunanData = @json(array_column($dataTahunan, 'total'));
        const ctx2 = document.getElementById('chartTahunan').getContext('2d');

        new Chart(ctx2, {
            type: 'line',
            data: {
                labels: tahunanLabels,
                datasets: [{
                    label: 'Jumlah Peminjaman per Tahun',
                    data: tahunanData,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.15)',
                    pointBackgroundColor: '#10b981',
                    pointRadius: 6,
                    pointHoverRadius: 8,
                    fill: true,
                    tension: 0.25,
                    borderWidth: 2.5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { font: { family: "'Plus Jakarta Sans', sans-serif", weight: '600', size: 12 }, usePointStyle: true }
                    },
                    tooltip: { padding: 12, titleFont: { family: "'Plus Jakarta Sans', sans-serif", weight: '700' } }
                },
                scales: {
                    x: {
                        grid: { color: 'rgba(226, 232, 240, 0.6)' },
                        ticks: { font: { family: "'Plus Jakarta Sans', sans-serif", weight: '600' } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(226, 232, 240, 0.6)' },
                        ticks: { precision: 0, font: { family: "'Plus Jakarta Sans', sans-serif" } }
                    }
                }
            }
        });
    });
</script>
@endif
@endpush

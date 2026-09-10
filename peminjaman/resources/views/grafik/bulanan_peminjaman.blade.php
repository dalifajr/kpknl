@extends('layouts.app')

@section('title', 'Grafik Bulanan Peminjaman (Total)')

@section('content')
<div style="display: flex; justify-content: flex-end; margin-bottom: 16px;">
    <a href="{{ route('grafik.bulanan') }}" class="btn btn-outline">
        ← Kembali ke Grafik Bulanan
    </a>
</div>

<div class="card">
    <div class="card-header">
        <div>
            <h1 class="card-title">GRAFIK BULANAN PEMINJAMAN (TOTAL SEMUA PEMINJAM)</h1>
            <p style="color: var(--text-muted); font-size: 0.875rem;">Frekuensi total transaksi peminjaman berkas seluruh pengguna per bulan.</p>
        </div>
    </div>

    @if (!empty($dataBulanan))
        <div style="text-align: center; margin-bottom: 12px;">
            <span style="font-size: 0.85rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">
                Pilih Tahun Peminjaman:
            </span>
        </div>

        <div class="year-pills-container">
            @foreach ($dataBulanan as $tahun => $bulanData)
                <button type="button" class="year-pill btn-tahun-peminjaman" onclick="showChart({{ $tahun }}, this)">
                    {{ $tahun }}
                </button>
            @endforeach
        </div>

        <div class="chart-card" style="position: relative; height: 460px; width: 100%;">
            <canvas id="chartBulanan"></canvas>
        </div>
    @else
        <div style="padding: 40px; text-align: center; color: var(--text-muted);">
            Belum ada data peminjaman yang tercatat.
        </div>
    @endif
</div>
@endsection

@push('scripts')
@if (!empty($dataBulanan))
<script>
    const allCharts = @json($dataBulanan);
    const ctx = document.getElementById('chartBulanan').getContext('2d');
    let chartInstance;

    function showChart(tahun, el) {
        const bulanLabels = ['JAN', 'FEB', 'MAR', 'APR', 'MEI', 'JUN', 'JUL', 'AGS', 'SEP', 'OKT', 'NOV', 'DES'];
        const tahunData = allCharts[tahun] || {};

        const dataBulan = Array(12).fill(0);
        for (let bulan in tahunData) {
            dataBulan[bulan - 1] = tahunData[bulan];
        }

        if (chartInstance) {
            chartInstance.destroy();
        }

        chartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: bulanLabels,
                datasets: [{
                    label: 'Total Peminjaman Tahun ' + tahun,
                    data: dataBulan,
                    backgroundColor: 'rgba(2, 132, 199, 0.15)',
                    borderColor: '#0284c7',
                    borderWidth: 2.5,
                    pointBackgroundColor: '#0284c7',
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    fill: true,
                    tension: 0.25
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: { family: "'Plus Jakarta Sans', sans-serif", weight: '600', size: 12 },
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        padding: 12,
                        titleFont: { family: "'Plus Jakarta Sans', sans-serif", weight: '700' }
                    }
                },
                scales: {
                    x: {
                        grid: { color: 'rgba(226, 232, 240, 0.6)' },
                        ticks: { font: { family: "'Plus Jakarta Sans', sans-serif", weight: '600' } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(226, 232, 240, 0.6)' },
                        ticks: {
                            precision: 0,
                            font: { family: "'Plus Jakarta Sans', sans-serif" }
                        }
                    }
                }
            }
        });

        const buttons = document.querySelectorAll('.btn-tahun-peminjaman');
        buttons.forEach(btn => btn.classList.remove('active'));
        if (el) {
            el.classList.add('active');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const buttons = document.querySelectorAll('.btn-tahun-peminjaman');
        if (buttons.length > 0) {
            const firstYear = Object.keys(allCharts)[0];
            showChart(firstYear, buttons[0]);
        }
    });
</script>
@endif
@endpush

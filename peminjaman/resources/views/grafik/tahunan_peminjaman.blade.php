@extends('layouts.app')

@section('title', 'Grafik Tahunan Peminjaman')

@section('content')
<div style="display: flex; justify-content: flex-end; margin-bottom: 16px;">
    <a href="{{ route('grafik.tahunan') }}" class="btn btn-outline">
        ← Kembali ke Grafik Tahunan
    </a>
</div>

<div class="card">
    <div class="card-header">
        <div>
            <h1 class="card-title">GRAFIK TAHUNAN PEMINJAMAN</h1>
            <p style="color: var(--text-muted); font-size: 0.875rem;">Total transaksi peminjaman berkas seluruh pengguna dari tahun ke tahun.</p>
        </div>
    </div>

    @if (!empty($tahun))
        <div class="chart-card" style="position: relative; height: 460px; width: 100%;">
            <canvas id="chartTahunan"></canvas>
        </div>
    @else
        <div style="padding: 40px; text-align: center; color: var(--text-muted);">
            Belum ada data peminjaman tahunan yang tercatat.
        </div>
    @endif
</div>
@endsection

@push('scripts')
@if (!empty($tahun))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('chartTahunan').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($tahun),
                datasets: [{
                    label: 'Total Peminjaman Berkas',
                    data: @json($total),
                    borderColor: '#0284c7',
                    backgroundColor: 'rgba(2, 132, 199, 0.15)',
                    fill: true,
                    tension: 0.25,
                    borderWidth: 2.5,
                    pointBackgroundColor: '#0284c7',
                    pointRadius: 6,
                    pointHoverRadius: 8
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
    });
</script>
@endif
@endpush

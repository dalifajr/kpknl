@extends('layouts.app')

@section('title', 'Grafik Tahunan Pelelangan')

@section('content')
<div style="display: flex; justify-content: flex-end; margin-bottom: 16px;">
    <a href="{{ route('grafik.tahunan') }}" class="btn btn-outline">
        ← Kembali ke Grafik Tahunan
    </a>
</div>

<div class="card">
    <div class="card-header">
        <div>
            <h1 class="card-title">GRAFIK TAHUNAN PELELANGAN</h1>
            <p style="color: var(--text-muted); font-size: 0.875rem;">Perbandingan volume total risalah lelang dari tahun ke tahun.</p>
        </div>
    </div>

    <!-- Filter Dropdown -->
    <div style="display: flex; justify-content: center; align-items: center; gap: 12px; margin-bottom: 24px;">
        <form method="GET" action="{{ route('grafik.tahunan.pelelangan') }}" id="filterForm" style="display: flex; align-items: center; gap: 12px;">
            <label for="jenis" style="font-weight: 700; font-size: 0.9rem; color: var(--text-main);">
                Pilih Jenis Risalah:
            </label>
            <select name="jenis" id="jenis" class="form-control" style="width: auto; min-width: 180px;" onchange="document.getElementById('filterForm').submit()">
                <option value="minuta" {{ $filter === 'minuta' ? 'selected' : '' }}>Risalah Minuta</option>
                <option value="tap" {{ $filter === 'tap' ? 'selected' : '' }}>Risalah TAP</option>
                <option value="batal" {{ $filter === 'batal' ? 'selected' : '' }}>Risalah Batal</option>
            </select>
        </form>
    </div>

    <div class="chart-card" style="position: relative; height: 460px; width: 100%;">
        <canvas id="grafik"></canvas>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('grafik').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($tahun),
                datasets: [{
                    label: 'Jumlah Pelelangan ({{ strtoupper($filter) }})',
                    data: @json($jumlah),
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
    });
</script>
@endpush

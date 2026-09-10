@extends('layouts.app')

@section('title', 'Grafik Bulanan Per Tahun Pelelangan')

@section('content')
<div style="display: flex; justify-content: flex-end; margin-bottom: 16px;">
    <a href="{{ route('grafik.bulanan') }}" class="btn btn-outline">
        ← Kembali ke Grafik Bulanan
    </a>
</div>

<div class="card">
    <div class="card-header">
        <div>
            <h1 class="card-title">GRAFIK BULANAN PER TAHUN PELELANGAN</h1>
            <p style="color: var(--text-muted); font-size: 0.875rem;">Visualisasi perbandingan jumlah risalah lelang per bulan pada tahun yang dipilih.</p>
        </div>
    </div>

    <!-- Pilihan Tahun (Modern Year Pills) -->
    <div style="text-align: center; margin-bottom: 12px;">
        <span style="font-size: 0.85rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">
            Pilih Tahun Anggaran:
        </span>
    </div>
    <div class="year-pills-container" id="yearButtons">
        @foreach ($years as $yr)
            <button type="button" class="year-pill" data-year="{{ $yr }}" onclick="setYear({{ $yr }}, this)">
                {{ $yr }}
            </button>
        @endforeach
    </div>

    <!-- Filter Jenis Risalah -->
    <div style="display: flex; justify-content: center; align-items: center; gap: 12px; margin: 20px 0;">
        <label for="risalahSelect" style="font-weight: 700; font-size: 0.9rem; color: var(--text-main);">
            Filter Jenis Risalah:
        </label>
        <select id="risalahSelect" class="form-control" style="width: auto; min-width: 180px;" onchange="updateChart()">
            <option value="">Semua (Minuta, TAP, Batal)</option>
            <option value="minuta">Risalah Minuta</option>
            <option value="tap">Risalah TAP</option>
            <option value="batal">Risalah Batal</option>
        </select>
    </div>

    <div style="text-align: center; margin: 15px 0 25px;">
        <h3 id="selectedYear" style="color: var(--primary-accent); font-size: 1.25rem; font-weight: 800; letter-spacing: -0.01em;">
            Pilih Tahun
        </h3>
    </div>

    <div class="chart-card" style="position: relative; height: 460px; width: 100%;">
        <canvas id="grafikChart"></canvas>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let chart;
    let selectedYear = null;
    const dataUrl = "{{ route('grafik.bulanan.pelelangan.data') }}";

    function setYear(year, el) {
        selectedYear = year;
        const buttons = document.querySelectorAll('.year-pill');
        buttons.forEach(btn => btn.classList.remove('active'));
        if (el) {
            el.classList.add('active');
        }
        updateChart();
    }

    function updateChart() {
        if (!selectedYear) {
            return;
        }
        const status = document.getElementById('risalahSelect').value;

        fetch(`${dataUrl}?year=${selectedYear}&status=${status}`)
            .then(response => response.json())
            .then(data => {
                const statusText = status ? " • " + status.toUpperCase() : " • SEMUA KATEGORI";
                document.getElementById('selectedYear').innerText = "Tahun " + selectedYear + statusText;

                const labels = data.labels;
                let datasets;
                if (data.datasets) {
                    datasets = data.datasets.map(d => ({
                        ...d,
                        borderWidth: 2.5,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        tension: 0.25
                    }));
                } else {
                    datasets = [{
                        label: 'Jumlah Risalah (' + (status ? status.toUpperCase() : 'Semua') + ')',
                        data: data.values,
                        borderColor: '#0284c7',
                        backgroundColor: 'rgba(2, 132, 199, 0.12)',
                        borderWidth: 2.5,
                        pointBackgroundColor: '#0284c7',
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        fill: true,
                        tension: 0.25
                    }];
                }

                if (chart) {
                    chart.destroy();
                }

                const ctx = document.getElementById('grafikChart').getContext('2d');
                chart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            intersect: false,
                            mode: 'index'
                        },
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: {
                                    font: { family: "'Plus Jakarta Sans', sans-serif", weight: '600', size: 12 },
                                    usePointStyle: true,
                                    padding: 16
                                }
                            },
                            tooltip: {
                                padding: 12,
                                boxPadding: 6,
                                titleFont: { family: "'Plus Jakarta Sans', sans-serif", weight: '700' },
                                bodyFont: { family: "'Plus Jakarta Sans', sans-serif" }
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
                                    stepSize: 1,
                                    font: { family: "'Plus Jakarta Sans', sans-serif" }
                                }
                            }
                        }
                    }
                });
            });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const yearButtons = document.querySelectorAll('.year-pill');
        if (yearButtons.length > 0) {
            const lastBtn = yearButtons[yearButtons.length - 1];
            const yearVal = parseInt(lastBtn.getAttribute('data-year'));
            setYear(yearVal, lastBtn);
        }
    });
</script>
@endpush

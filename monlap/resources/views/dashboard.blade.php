@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="card">
    <div class="d-flex justify-between align-center">
        <div>
            <h2 class="card-title">Selamat Datang, {{ auth()->user()->name }}!</h2>
            <p style="margin: 0;">Ini adalah halaman Dashboard Sistem Monitoring Todo-List KPKNL Palembang.</p>
        </div>
        
        <form action="{{ route('dashboard') }}" method="GET" class="d-flex" style="gap: 16px; align-items: flex-end;">
            <div>
                <div class="md-input-container" style="margin-bottom: 0;">
                    <select name="year" class="md-input" onchange="this.form.submit()">
                        <option value="">Semua Tahun</option>
                        @php $currentYear = date('Y'); @endphp
                        @for($i = $currentYear; $i >= 2024; $i--)
                            <option value="{{ $i }}" {{ request('year') == $i ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                    <label class="md-label">Tahun</label>
                    <div class="md-bar"></div>
                </div>
            </div>
            
            @if(in_array(auth()->user()->role, ['admin', 'superadmin']))
            <div>
                <div class="md-input-container" style="margin-bottom: 0;">
                    <select name="pic_id" class="md-input" onchange="this.form.submit()">
                        <option value="">Semua User</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('pic_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                        @endforeach
                    </select>
                    <label class="md-label">Filter User PIC</label>
                    <div class="md-bar"></div>
                </div>
            </div>
            @endif
        </form>
    </div>
</div>

<div class="d-flex" style="gap: 16px; margin-top: 16px;">
    <!-- User PIC Summary (4 cards) -->
    <div style="flex: {{ (auth()->user()->role === 'superadmin' || auth()->user()->role === 'admin') ? '1' : '100%' }}; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
        
        <div class="card" style="margin: 0;">
            <h3 class="card-title" style="font-size: 16px;">Todo-List Saya</h3>
            <div style="font-size: 36px; font-weight: 300; color: var(--primary); margin-bottom: 4px;">{{ $pendingTasks }}</div>
            <p style="color: var(--text-secondary); font-size: 12px; margin-bottom: 12px;">tugas (pending/draft)</p>
            <a href="{{ route('user-tasks.index') }}" class="btn btn-primary" style="width: 100%; text-align: center; padding: 6px;">Lihat Todo-List</a>
        </div>
        
        <div class="card" style="margin: 0;">
            <h3 class="card-title" style="font-size: 16px;">Menunggu Review</h3>
            <div style="font-size: 36px; font-weight: 300; color: #2196F3; margin-bottom: 4px;">{{ $submittedTasks }}</div>
            <p style="color: var(--text-secondary); font-size: 12px; margin-bottom: 12px;">laporan telah disubmit</p>
            <a href="{{ route('user-tasks.index', ['status' => 'submitted']) }}" class="btn btn-flat" style="width: 100%; text-align: center; padding: 6px; border: 1px solid var(--divider);">Lihat Detail</a>
        </div>

        <div class="card" style="margin: 0;">
            <h3 class="card-title" style="font-size: 16px;">Revisi</h3>
            <div style="font-size: 36px; font-weight: 300; color: #F44336; margin-bottom: 4px;">{{ $revisiTasks }}</div>
            <p style="color: var(--text-secondary); font-size: 12px; margin-bottom: 12px;">butuh perbaikan</p>
            <a href="{{ route('user-tasks.index', ['status' => 'revisi']) }}" class="btn btn-flat" style="width: 100%; text-align: center; padding: 6px; border: 1px solid var(--divider);">Lihat Detail</a>
        </div>

        <div class="card" style="margin: 0;">
            <h3 class="card-title" style="font-size: 16px;">Disetujui (ACC)</h3>
            <div style="font-size: 36px; font-weight: 300; color: #4CAF50; margin-bottom: 4px;">{{ $accTasks }}</div>
            <p style="color: var(--text-secondary); font-size: 12px; margin-bottom: 12px;">laporan selesai</p>
            <a href="{{ route('user-tasks.index', ['status' => 'acc']) }}" class="btn btn-flat" style="width: 100%; text-align: center; padding: 6px; border: 1px solid var(--divider);">Lihat Detail</a>
        </div>

    </div>
    
    @if(auth()->user()->role === 'superadmin' || auth()->user()->role === 'admin')
    <!-- Admin Summary -->
    <div class="card" style="flex: 1;">
        <h3 class="card-title">Menunggu Verifikasi</h3>
        <div style="font-size: 48px; font-weight: 300; color: #FF9800; margin-bottom: 8px;">{{ $waitingReviews }}</div>
        <p style="color: var(--text-secondary);">laporan dari PIC subbidang menunggu direview.</p>
        <a href="{{ route('reviews.index') }}" class="btn btn-flat mt-3" style="border: 1px solid var(--primary);">Review Sekarang</a>
    </div>
    @endif
</div>

@if(auth()->user()->role === 'superadmin' || auth()->user()->role === 'admin')
<!-- Admin Statistics -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 16px; margin-top: 16px;">
    <div class="card" style="margin: 0;">
        <h3 class="card-title">Statistik Status Laporan</h3>
        <div style="max-width: 300px; margin: 0 auto;">
            <canvas id="statusChart"></canvas>
        </div>
    </div>
    
    <div class="card" style="margin: 0;">
        <h3 class="card-title">Kinerja PIC (Top 10 Selesai)</h3>
        <div style="width: 100%; height: 250px;">
            <canvas id="picChart"></canvas>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
@if(auth()->user()->role === 'superadmin' || auth()->user()->role === 'admin')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Status Chart
        const ctxStatus = document.getElementById('statusChart').getContext('2d');
        new Chart(ctxStatus, {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'Draft', 'Submitted', 'ACC', 'Revisi'],
                datasets: [{
                    data: [
                        {{ $stats['pending'] ?? 0 }},
                        {{ $stats['draft'] ?? 0 }},
                        {{ $stats['submitted'] ?? 0 }},
                        {{ $stats['acc'] ?? 0 }},
                        {{ $stats['revisi'] ?? 0 }}
                    ],
                    backgroundColor: [
                        '#FF9800', '#9E9E9E', '#2196F3', '#4CAF50', '#F44336'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
                cutout: '70%'
            }
        });

        // PIC Performance Chart
        const ctxPic = document.getElementById('picChart').getContext('2d');
        new Chart(ctxPic, {
            type: 'bar',
            data: {
                labels: {!! json_encode($picStats['labels'] ?? []) !!},
                datasets: [{
                    label: 'Tugas Selesai (ACC)',
                    data: {!! json_encode($picStats['data'] ?? []) !!},
                    backgroundColor: '#009688',
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } }
                }
            }
        });
    });
</script>
@endif
@endpush

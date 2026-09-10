@extends('layouts.app')

@section('title', 'Todo-List Saya')

@section('content')
<div class="card">
    <h2 class="card-title">Daftar Tugas Anda</h2>

    <div style="margin-bottom: 24px; padding: 16px; background: var(--surface-color); border-radius: 8px;">
        <form action="{{ route('user-tasks.index') }}" method="GET" class="d-flex" style="gap: 16px; flex-wrap: wrap; align-items: flex-end;">
            <input type="hidden" name="status" value="{{ $status }}">
            
            <div style="flex: 1; min-width: 150px;">
                <div class="md-input-container" style="margin-bottom: 0;">
                    <select name="year" class="md-input">
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

            <div style="flex: 1; min-width: 150px;">
                <div class="md-input-container" style="margin-bottom: 0;">
                    <select name="period_type" class="md-input">
                        <option value="">Semua Periode</option>
                        <option value="bulanan" {{ request('period_type') == 'bulanan' ? 'selected' : '' }}>Bulanan</option>
                        <option value="triwulan" {{ request('period_type') == 'triwulan' ? 'selected' : '' }}>Triwulan</option>
                        <option value="semesteran" {{ request('period_type') == 'semesteran' ? 'selected' : '' }}>Semesteran</option>
                        <option value="tahunan" {{ request('period_type') == 'tahunan' ? 'selected' : '' }}>Tahunan</option>
                        <option value="tidak rutin" {{ request('period_type') == 'tidak rutin' ? 'selected' : '' }}>Tidak Rutin</option>
                        <option value="custom" {{ request('period_type') == 'custom' ? 'selected' : '' }}>Custom</option>
                    </select>
                    <label class="md-label">Jenis Periode</label>
                    <div class="md-bar"></div>
                </div>
            </div>

            @if(in_array(auth()->user()->role, ['admin', 'superadmin']))
            <div style="flex: 1; min-width: 200px;">
                <div class="md-input-container" style="margin-bottom: 0;">
                    <select name="pic_id" class="md-input">
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
            
            <div style="flex: 2; min-width: 250px;">
                <div class="md-input-container" style="margin-bottom: 0;">
                    <input type="text" name="search" value="{{ request('search') }}" class="md-input" placeholder=" ">
                    <label class="md-label">Cari Judul Tugas...</label>
                    <div class="md-bar"></div>
                </div>
            </div>

            <div style="flex: 1; min-width: 120px; max-width: 150px;">
                <div class="md-input-container" style="margin-bottom: 0;">
                    <select name="per_page" class="md-input">
                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10 baris</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 baris</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 baris</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 baris</option>
                    </select>
                    <label class="md-label">Tampilkan</label>
                    <div class="md-bar"></div>
                </div>
            </div>
            
            <div>
                <button type="submit" class="btn btn-primary ripple-surface" style="height: 48px; padding: 0 16px;">
                    <i class="material-icons" style="font-size: 20px; margin-right: 8px;">search</i> Terapkan
                </button>
            </div>
        </form>
    </div>

    <!-- Status Tabs -->
    <div class="d-flex" style="gap: 8px; border-bottom: 2px solid var(--divider); margin-bottom: 16px; flex-wrap: wrap;">
        <a href="{{ route('user-tasks.index', array_merge(request()->query(), ['status' => 'all'])) }}" 
           style="padding: 12px 16px; text-decoration: none; font-weight: 500; color: {{ $status === 'all' ? 'var(--primary)' : 'var(--text-secondary)' }}; border-bottom: 3px solid {{ $status === 'all' ? 'var(--primary)' : 'transparent' }}; margin-bottom: -2px;">
            Semua
        </a>
        <a href="{{ route('user-tasks.index', array_merge(request()->query(), ['status' => 'pending'])) }}" 
           style="padding: 12px 16px; text-decoration: none; font-weight: 500; color: {{ $status === 'pending' ? '#FF9800' : 'var(--text-secondary)' }}; border-bottom: 3px solid {{ $status === 'pending' ? '#FF9800' : 'transparent' }}; margin-bottom: -2px;">
            Pending
        </a>
        <a href="{{ route('user-tasks.index', array_merge(request()->query(), ['status' => 'draft'])) }}" 
           style="padding: 12px 16px; text-decoration: none; font-weight: 500; color: {{ $status === 'draft' ? '#9E9E9E' : 'var(--text-secondary)' }}; border-bottom: 3px solid {{ $status === 'draft' ? '#9E9E9E' : 'transparent' }}; margin-bottom: -2px;">
            Draft
        </a>
        <a href="{{ route('user-tasks.index', array_merge(request()->query(), ['status' => 'submitted'])) }}" 
           style="padding: 12px 16px; text-decoration: none; font-weight: 500; color: {{ $status === 'submitted' ? '#2196F3' : 'var(--text-secondary)' }}; border-bottom: 3px solid {{ $status === 'submitted' ? '#2196F3' : 'transparent' }}; margin-bottom: -2px;">
            Menunggu Review
        </a>
        <a href="{{ route('user-tasks.index', array_merge(request()->query(), ['status' => 'revisi'])) }}" 
           style="padding: 12px 16px; text-decoration: none; font-weight: 500; color: {{ $status === 'revisi' ? '#F44336' : 'var(--text-secondary)' }}; border-bottom: 3px solid {{ $status === 'revisi' ? '#F44336' : 'transparent' }}; margin-bottom: -2px;">
            Revisi
        </a>
        <a href="{{ route('user-tasks.index', array_merge(request()->query(), ['status' => 'acc'])) }}" 
           style="padding: 12px 16px; text-decoration: none; font-weight: 500; color: {{ $status === 'acc' ? '#4CAF50' : 'var(--text-secondary)' }}; border-bottom: 3px solid {{ $status === 'acc' ? '#4CAF50' : 'transparent' }}; margin-bottom: -2px;">
            Selesai (ACC)
        </a>
    </div>



    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 16px;">
        @forelse($assignments as $assignment)
            @include('user-tasks.partials.task-card', ['assignment' => $assignment])
        @empty
            <div style="grid-column: 1 / -1; padding: 64px 32px; text-align: center; color: var(--text-secondary); background: var(--surface-color); border-radius: 8px; box-shadow: var(--elevation-1);">
                <i class="material-icons" style="font-size: 72px; color: var(--divider); margin-bottom: 16px;">inbox</i>
                <h3 style="font-size: 20px; color: var(--text-primary); margin-bottom: 8px;">Tidak Ada Tugas</h3>
                <p style="font-size: 14px; max-width: 400px; margin: 0 auto;">Belum ada tugas yang cocok dengan filter Anda atau semua tugas telah diselesaikan. Selamat beristirahat!</p>
            </div>
        @endforelse
    </div>

    <div style="margin-top: 24px;">
        {{ $assignments->links() }}
    </div>
</div>
@endsection

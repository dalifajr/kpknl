@extends('layouts.app')

@section('title', 'Daftar Laporan')

@section('content')
<div class="card">
    <h2 class="card-title">Daftar Laporan</h2>
    
    <div style="margin-bottom: 24px; padding: 16px; background: var(--surface-color); border-radius: 8px;">
        <form action="{{ route('reviews.index') }}" method="GET" class="d-flex" style="gap: 16px; flex-wrap: wrap; align-items: flex-end;">
            <!-- Keep current status tab when searching -->
            <input type="hidden" name="status" value="{{ $status }}">
            
            <div style="flex: 1; min-width: 200px;">
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
            
            <div style="flex: 1; min-width: 200px;">
                <div class="md-input-container" style="margin-bottom: 0;">
                    <select name="user_id" class="md-input">
                        <option value="">Semua User</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                        @endforeach
                    </select>
                    <label class="md-label">User PIC</label>
                    <div class="md-bar"></div>
                </div>
            </div>
            
            <div style="flex: 2; min-width: 200px;">
                <div class="md-input-container" style="margin-bottom: 0;">
                    <input type="text" name="search" value="{{ request('search') }}" class="md-input" placeholder=" ">
                    <label class="md-label">Cari Nama PIC atau Judul Tugas...</label>
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
    <div class="d-flex" style="gap: 8px; border-bottom: 2px solid var(--divider); margin-bottom: 16px; overflow-x: auto;">
        <a href="{{ route('reviews.index', array_merge(request()->query(), ['status' => 'all'])) }}" 
           style="padding: 12px 24px; text-decoration: none; font-weight: 500; white-space: nowrap; color: {{ $status === 'all' ? 'var(--primary)' : 'var(--text-secondary)' }}; border-bottom: 3px solid {{ $status === 'all' ? 'var(--primary)' : 'transparent' }}; margin-bottom: -2px;">
            Semua
        </a>
        <a href="{{ route('reviews.index', array_merge(request()->query(), ['status' => 'pending'])) }}" 
           style="padding: 12px 24px; text-decoration: none; font-weight: 500; white-space: nowrap; color: {{ $status === 'pending' ? 'var(--primary)' : 'var(--text-secondary)' }}; border-bottom: 3px solid {{ $status === 'pending' ? 'var(--primary)' : 'transparent' }}; margin-bottom: -2px;">
            Pending
        </a>
        <a href="{{ route('reviews.index', array_merge(request()->query(), ['status' => 'submitted'])) }}" 
           style="padding: 12px 24px; text-decoration: none; font-weight: 500; white-space: nowrap; color: {{ $status === 'submitted' ? 'var(--primary)' : 'var(--text-secondary)' }}; border-bottom: 3px solid {{ $status === 'submitted' ? 'var(--primary)' : 'transparent' }}; margin-bottom: -2px;">
            Menunggu Review
        </a>
        <a href="{{ route('reviews.index', array_merge(request()->query(), ['status' => 'acc'])) }}" 
           style="padding: 12px 24px; text-decoration: none; font-weight: 500; white-space: nowrap; color: {{ $status === 'acc' ? '#4CAF50' : 'var(--text-secondary)' }}; border-bottom: 3px solid {{ $status === 'acc' ? '#4CAF50' : 'transparent' }}; margin-bottom: -2px;">
            ACC
        </a>
        <a href="{{ route('reviews.index', array_merge(request()->query(), ['status' => 'revisi'])) }}" 
           style="padding: 12px 24px; text-decoration: none; font-weight: 500; white-space: nowrap; color: {{ $status === 'revisi' ? '#F44336' : 'var(--text-secondary)' }}; border-bottom: 3px solid {{ $status === 'revisi' ? '#F44336' : 'transparent' }}; margin-bottom: -2px;">
            Revisi
        </a>
    </div>



    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="border-bottom: 2px solid var(--divider); text-align: left;">
                <th style="padding: 12px 8px;">User PIC</th>
                <th style="padding: 12px 8px;">Tugas Induk</th>
                <th style="padding: 12px 8px;">Periode</th>
                <th style="padding: 12px 8px;">Status</th>
                <th style="padding: 12px 8px;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($assignments as $assignment)
            <tr style="border-bottom: 1px solid var(--divider);">
                <td style="padding: 12px 8px;">
                    <strong>{{ $assignment->user->name }}</strong><br>
                    <small style="color: var(--text-secondary)">
                        Disubmit: {{ $assignment->submission ? $assignment->submission->created_at->format('d/m/Y H:i') : '-' }}
                        @if($assignment->submission && $assignment->submission->submitted_by && $assignment->submission->submitted_by !== $assignment->user_id)
                            <br><span style="color: var(--primary);">(Takeover oleh: {{ optional($assignment->submission->submitter)->name }})</span>
                        @endif
                    </small>
                </td>
                <td style="padding: 12px 8px;">
                    {{ $assignment->task->title }}
                </td>
                <td style="padding: 12px 8px;">{{ $assignment->period }}</td>
                <td style="padding: 12px 8px;">
                    @php
                        $color = match($assignment->status) {
                            'submitted' => '#2196F3',
                            'acc' => '#4CAF50',
                            'revisi' => '#F44336',
                            'pending' => '#FF9800',
                            'draft' => '#9E9E9E',
                            default => '#000'
                        };
                    @endphp
                    <span style="color: {{ $color }}; font-weight: 500; text-transform: uppercase; font-size: 12px;">
                        {{ $assignment->status }}
                    </span>
                </td>
                <td style="padding: 12px 8px;">
                    <a href="{{ route('reviews.show', $assignment->id) }}" class="btn btn-primary" style="font-size: 12px; padding: 0 12px; height: 28px;">
                        @if(in_array($assignment->status, ['pending', 'draft']))
                            Takeover
                        @elseif($assignment->status === 'submitted')
                            Review
                        @else
                            Detail
                        @endif
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="padding: 64px 32px; text-align: center; color: var(--text-secondary); background: transparent;">
                    <i class="material-icons" style="font-size: 72px; color: #E0E0E0; margin-bottom: 16px;">assignment</i>
                    <h3 style="font-size: 20px; color: var(--text-primary); margin-bottom: 8px; font-weight: 500;">Daftar Laporan Kosong</h3>
                    <p style="font-size: 14px; max-width: 400px; margin: 0 auto;">Belum ada laporan yang sesuai dengan filter Anda. Laporan yang sudah disubmit oleh PIC akan muncul di sini untuk direview.</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 16px;">
        {{ $assignments->links() }}
    </div>
</div>
@endsection

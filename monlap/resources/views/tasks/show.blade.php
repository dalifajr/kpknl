@extends('layouts.app')

@section('title', 'Detail Tugas Induk')

@section('content')
<div class="card" style="margin-bottom: 24px;">
    <div class="d-flex justify-between align-center mb-3">
        <h2 class="card-title" style="margin: 0;">{{ $task->title }}</h2>
        <div>
            <a href="{{ route('tasks.index') }}" class="btn btn-flat">Kembali</a>
            <a href="{{ route('tasks.edit', $task->id) }}" class="btn btn-primary">Edit Tugas</a>
        </div>
    </div>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
        <div>
            <p style="color: var(--text-secondary); margin-bottom: 4px;">Deskripsi</p>
            <p>{{ $task->description ?: '-' }}</p>
        </div>
        <div>
            <p style="color: var(--text-secondary); margin-bottom: 4px;">User PIC</p>
            <p>
                @forelse($task->users as $user)
                    <span style="background: #E3F2FD; color: #1565C0; padding: 2px 8px; border-radius: 12px; font-size: 11px; margin-right: 4px; display: inline-block; margin-bottom: 4px;">{{ $user->name }}</span>
                @empty
                    -
                @endforelse
            </p>
        </div>
        <div>
            <p style="color: var(--text-secondary); margin-bottom: 4px;">Jenis Periode</p>
            <p><span style="background: #E0F2F1; color: var(--primary-dark); padding: 4px 8px; border-radius: 4px; font-size: 12px;">{{ ucfirst($task->period_type) }}</span></p>
        </div>
        <div>
            <p style="color: var(--text-secondary); margin-bottom: 4px;">Aturan Deadline (Tanggal)</p>
            <p>{{ $task->deadline_rule ?: 'Akhir Bulan' }}</p>
        </div>
    </div>
</div>

<div class="card">
    <h3 class="card-title">Daftar Penugasan (Assignments)</h3>
    <table style="width: 100%; border-collapse: collapse; margin-top: 16px;">
        <thead>
            <tr style="border-bottom: 2px solid var(--divider); text-align: left;">
                <th style="padding: 12px 8px;">User PIC</th>
                <th style="padding: 12px 8px;">Periode Tugas</th>
                <th style="padding: 12px 8px;">Deadline</th>
                <th style="padding: 12px 8px;">Status</th>
                <th style="padding: 12px 8px;">Tanggal Submit</th>
            </tr>
        </thead>
        <tbody>
            @forelse($task->assignments as $assignment)
            <tr style="border-bottom: 1px solid var(--divider);">
                <td style="padding: 12px 8px;">{{ $assignment->user->name }}</td>
                <td style="padding: 12px 8px;">{{ $assignment->period }}</td>
                <td style="padding: 12px 8px;">{{ $assignment->deadline_date ? $assignment->deadline_date->format('d/m/Y') : '-' }}</td>
                <td style="padding: 12px 8px;">
                    @php
                        $color = match($assignment->status) {
                            'pending' => '#FF9800', // Orange
                            'draft' => '#9E9E9E', // Grey
                            'submitted' => '#2196F3', // Blue
                            'acc' => '#4CAF50', // Green
                            'revisi' => '#F44336', // Red
                            default => '#000'
                        };
                    @endphp
                    <span style="color: {{ $color }}; font-weight: 500; text-transform: uppercase; font-size: 12px;">
                        {{ $assignment->status }}
                    </span>
                </td>
                <td style="padding: 12px 8px;">
                    {{ $assignment->submission ? $assignment->submission->created_at->format('d/m/Y H:i') : '-' }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="padding: 24px; text-align: center; color: var(--text-secondary);">
                    Belum ada tugas yang dibagikan. Pastikan user PIC sudah dipilih.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

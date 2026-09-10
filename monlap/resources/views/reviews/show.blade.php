@extends('layouts.app')

@section('title', 'Review Laporan')

@section('content')
<div class="d-flex" style="gap: 24px; align-items: flex-start;">
    
    <!-- Data Laporan -->
    <div class="card" style="flex: 2;">
        <div class="d-flex justify-between align-center mb-3">
            <h3 class="card-title" style="margin: 0;">Laporan User PIC</h3>
            <span style="background: #E3F2FD; color: #1565C0; padding: 4px 12px; border-radius: 16px; font-size: 12px; font-weight: 500;">
                {{ $assignment->user->name }}
            </span>
        </div>

        <table style="width: 100%; border-collapse: collapse;">
            <tbody>
                <tr style="border-bottom: 1px solid var(--divider);">
                    <td style="padding: 12px 0; width: 30%; color: var(--text-secondary);">Nama Tugas</td>
                    <td style="padding: 12px 0; font-weight: 500;">{{ $assignment->task->title }}</td>
                </tr>
                <tr style="border-bottom: 1px solid var(--divider);">
                    <td style="padding: 12px 0; color: var(--text-secondary);">Periode</td>
                    <td style="padding: 12px 0;">{{ $assignment->period }}</td>
                </tr>
                <tr style="border-bottom: 1px solid var(--divider);">
                    <td style="padding: 12px 0; color: var(--text-secondary);">Nomor Surat</td>
                    <td style="padding: 12px 0;">{{ $assignment->submission->nomor_surat ?: '-' }}</td>
                </tr>
                <tr style="border-bottom: 1px solid var(--divider);">
                    <td style="padding: 12px 0; color: var(--text-secondary);">Tanggal Surat</td>
                    <td style="padding: 12px 0;">{{ $assignment->submission->tanggal_surat ? $assignment->submission->tanggal_surat->format('d/m/Y') : '-' }}</td>
                </tr>
                <tr style="border-bottom: 1px solid var(--divider);">
                    <td style="padding: 12px 0; color: var(--text-secondary);">Lampiran</td>
                    <td style="padding: 12px 0;">
                        @if($assignment->submission->attachment_path)
                            <a href="{{ asset('storage/' . $assignment->submission->attachment_path) }}" target="_blank" class="btn btn-flat" style="padding: 0; min-width: unset; color: var(--primary);">
                                <i class="material-icons" style="font-size: 16px; vertical-align: middle;">attachment</i> Buka Lampiran
                            </a>
                        @else
                            -
                        @endif
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px 0; color: var(--text-secondary); vertical-align: top;">Catatan User PIC</td>
                    <td style="padding: 12px 0; white-space: pre-wrap;">{{ $assignment->submission->notes ?: '-' }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Panel Verifikasi -->
    <div class="card" style="flex: 1; position: sticky; top: 88px;">
        <h3 class="card-title">Verifikasi</h3>
        


        <div style="margin-bottom: 24px; padding: 12px; border-radius: 4px; text-align: center; font-weight: 500; font-size: 16px; 
            @if($assignment->status === 'acc') background: #E8F5E9; color: #2E7D32; 
            @elseif($assignment->status === 'revisi') background: #FFEBEE; color: #C62828; 
            @else background: #E3F2FD; color: #1565C0; @endif">
            STATUS SAAT INI: {{ strtoupper($assignment->status) }}
            @if($assignment->status === 'acc' && $assignment->reviewed_by)
                <div style="font-size: 12px; font-weight: normal; margin-top: 4px; opacity: 0.9;">
                    (di-ACC oleh: {{ optional($assignment->reviewer)->name }})
                </div>
            @endif
        </div>

        <form action="{{ route('reviews.process', $assignment->id) }}" method="POST" id="processForm">
            @csrf
            
            <div class="md-input-container">
                <textarea name="comment" rows="3" class="md-input" placeholder=" "></textarea>
                <label class="md-label">Tulis catatan untuk User PIC (Opsional)</label>
                <div class="md-bar"></div>
            </div>

            @if($assignment->status !== 'acc')
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 16px;">
                <button type="button" class="btn btn-flat ripple-surface" style="background: #FFEBEE; color: #C62828;" onclick="showMDModal('Konfirmasi Revisi', 'Yakin ingin merevisi laporan ini?', () => submitAjax(this, 'revisi'), true)">
                    <i class="material-icons" style="font-size: 16px; margin-right: 4px;">edit_note</i> Revisi
                </button>
                <button type="button" class="btn btn-primary ripple-surface" onclick="showMDModal('Konfirmasi ACC', 'Yakin ingin ACC laporan ini?', () => submitAjax(this, 'acc'))">
                    <i class="material-icons" style="font-size: 16px; margin-right: 4px;">check_circle</i> ACC Final
                </button>
            </div>
            @else
                @if(auth()->user()->role === 'superadmin')
                <div style="margin-bottom: 16px;">
                    <button type="button" class="btn btn-flat ripple-surface" style="width: 100%; border: 1px solid #F44336; color: #F44336;" onclick="showMDModal('Batalkan ACC', 'Yakin ingin membatalkan ACC laporan ini?', () => submitAjax(this, 'batal_acc'), true)">
                        Batalkan ACC (Khusus Superadmin)
                    </button>
                </div>
                @else
                <div style="margin-bottom: 16px; text-align: center; color: var(--text-secondary); font-size: 12px;">
                    Laporan telah di-ACC dan bersifat final. Hanya Superadmin yang dapat membatalkannya.
                </div>
                @endif
            @endif

            <div style="display: flex; gap: 8px;">
                <a href="{{ route('reviews.index') }}" class="btn btn-flat ripple-surface" style="flex: 1; text-align: center; border: 1px solid var(--divider);">Kembali ke Daftar</a>
                
                @if(auth()->user()->role === 'superadmin' && $assignment->status !== 'acc')
                    <button type="button" class="btn btn-flat ripple-surface" style="background: #F44336; color: white;" onclick="showMDModal('Batalkan Pengisian', 'Yakin ingin mengosongkan data laporan ini dan mengembalikan statusnya ke pending?', () => submitAjax(this, 'cancel_submission'), true)">
                        <i class="material-icons" style="font-size: 16px;">remove_circle_outline</i> Batalkan Pengisian
                    </button>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Komentar Section -->
<div class="card" style="margin-top: 24px;">
    <h3 class="card-title">Riwayat Komentar & Diskusi</h3>
    
    <div style="margin-bottom: 24px;">
        @forelse($assignment->comments as $comment)
            <div style="margin-bottom: 16px; padding: 16px; background: #F5F5F5; border-radius: 4px; border-left: 4px solid {{ in_array($comment->user->role, ['superadmin', 'admin']) ? 'var(--primary)' : '#9E9E9E' }};">
                <div class="d-flex justify-between" style="margin-bottom: 8px;">
                    <strong style="font-size: 14px;">{{ $comment->user->name }} <span style="font-weight: normal; color: var(--text-secondary); font-size: 12px;">({{ $comment->user->role }})</span></strong>
                    <span style="color: var(--text-secondary); font-size: 12px;">{{ $comment->created_at->format('d M Y H:i') }}</span>
                </div>
                <p style="margin: 0; font-size: 14px; white-space: pre-wrap;">{{ $comment->body }}</p>
            </div>
        @empty
            <p style="color: var(--text-secondary); text-align: center;">Belum ada komentar.</p>
        @endforelse
    </div>
</div>
@endsection

@push('scripts')
<script>
async function submitAjax(btnOrModal, actionType) {
    const form = document.getElementById('processForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const formData = new FormData(form);
    formData.append('action', actionType);
    
    try {
        showToast('Memproses...', 'info');
        const response = await fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (response.ok) {
            showToast(data.message || 'Berhasil diproses', 'success');
            setTimeout(() => {
                window.location.href = data.redirect || window.location.href;
            }, 1000);
        } else {
            if (data.errors) {
                const errorMsg = Object.values(data.errors).flat().join('\n');
                showToast(errorMsg, 'error');
            } else {
                showToast(data.message || 'Terjadi kesalahan', 'error');
            }
        }
    } catch (e) {
        showToast('Terjadi kesalahan jaringan', 'error');
    }
}
</script>
@endpush

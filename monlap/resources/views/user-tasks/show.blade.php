@extends('layouts.app')

@section('title', 'Detail & Submit Tugas')

@section('content')
<div style="display: flex; gap: 24px; max-width: 1000px; margin: 0 auto; align-items: flex-start;">
    
    <!-- Informasi Tugas -->
    <div class="card" style="flex: 1; position: sticky; top: 88px;">
        <h3 class="card-title">Informasi Tugas</h3>
        
        <h4 style="margin-bottom: 8px;">{{ $assignment->task->title }}</h4>
        <p style="color: var(--text-secondary); margin-bottom: 16px; font-size: 14px;">{{ $assignment->task->description ?: 'Tidak ada deskripsi' }}</p>
        
        <div style="background: #F5F5F5; padding: 12px; border-radius: 4px; margin-bottom: 16px;">
            <div class="d-flex justify-between" style="margin-bottom: 8px;">
                <span style="color: var(--text-secondary); font-size: 12px;">Periode</span>
                <strong style="font-size: 12px;">{{ $assignment->period }}</strong>
            </div>
            <div class="d-flex justify-between" style="margin-bottom: 8px;">
                <span style="color: var(--text-secondary); font-size: 12px;">Deadline</span>
                <strong style="font-size: 12px; color: {{ $assignment->deadline_date && $assignment->deadline_date->isPast() && !in_array($assignment->status, ['acc', 'submitted']) ? '#F44336' : 'inherit' }}">{{ $assignment->deadline_date ? $assignment->deadline_date->format('d M Y') : '-' }}</strong>
            </div>
            <div class="d-flex justify-between" style="align-items: flex-start;">
                <span style="color: var(--text-secondary); font-size: 12px;">Status</span>
                <div style="text-align: right;">
                    <strong style="font-size: 12px; text-transform: uppercase; color: {{ match($assignment->status) { 'submitted' => '#2196F3', 'acc' => '#4CAF50', 'revisi' => '#F44336', 'pending' => '#FF9800', 'draft' => '#9E9E9E', default => '#000' } }};">{{ $assignment->status }}</strong>
                    @if($assignment->status === 'acc' && $assignment->reviewed_by)
                        <br><small style="color: var(--text-secondary); font-size: 10px;">di-ACC oleh: {{ optional($assignment->reviewer)->name }}</small>
                    @endif
                </div>
            </div>
        </div>

        @if($assignment->status === 'revisi')
        <div style="background: #FFEBEE; color: #C62828; padding: 12px; border-radius: 4px; margin-bottom: 16px; border: 1px solid #FFCDD2;">
            <strong>Catatan Revisi:</strong><br>
            <p style="font-size: 14px; margin-top: 4px;">Harap periksa komentar terbaru dari Admin/Superadmin di bawah.</p>
        </div>
        @endif
        
        <a href="{{ route('user-tasks.index') }}" class="btn btn-flat" style="width: 100%;">Kembali ke Daftar</a>
    </div>

    <!-- Form Submit -->
    <div class="card" style="flex: 2;">
        <h3 class="card-title">Laporan Pengerjaan</h3>



        <form action="{{ route('user-tasks.submit', $assignment->id) }}" method="POST" enctype="multipart/form-data" id="submitTaskForm">
            @csrf
            
            @php
                $isDisabled = in_array($assignment->status, ['acc', 'submitted']);
            @endphp

            <div class="md-input-container">
                <input type="text" name="nomor_surat" value="{{ old('nomor_surat', $assignment->submission?->nomor_surat ?? '') }}" class="md-input" placeholder=" " {{ $isDisabled ? 'readonly' : '' }}>
                <label class="md-label">Nomor Surat <span style="color: #F44336">*</span></label>
                <div class="md-bar"></div>
            </div>

            <div style="margin-bottom: 24px;">
                <div class="d-flex justify-between align-center" style="margin-bottom: 8px;">
                    @if(!$isDisabled)
                    <button type="button" class="btn btn-flat ripple-surface" style="height: 24px; padding: 0 8px; font-size: 11px; margin-left: auto;" onclick="document.getElementById('tanggal_surat').valueAsDate = new Date()">Set Hari Ini</button>
                    @endif
                </div>
                <div class="md-input-container" style="margin-bottom: 0;">
                    <input type="date" name="tanggal_surat" id="tanggal_surat" value="{{ old('tanggal_surat', $assignment->submission?->tanggal_surat ? $assignment->submission->tanggal_surat->format('Y-m-d') : '') }}" class="md-input" placeholder=" " {{ $isDisabled ? 'readonly' : '' }}>
                    <label class="md-label">Tanggal Surat <span style="color: #F44336">*</span></label>
                    <div class="md-bar"></div>
                </div>
            </div>

            <div style="margin-bottom: 24px;">
                <label style="display: block; font-weight: 500; margin-bottom: 8px; font-size: 14px; color: var(--text-secondary);">Lampiran / Dokumen Pendukung (Opsional)</label>
                @if($assignment->submission && $assignment->submission->attachment_path)
                    <div style="margin-bottom: 8px; font-size: 14px; padding: 8px; background: #e3f2fd; border-radius: 4px;">
                        <i class="material-icons" style="font-size: 16px; vertical-align: middle; color: var(--primary);">attachment</i>
                        <a href="{{ asset('storage/' . $assignment->submission->attachment_path) }}" target="_blank" style="color: var(--primary); text-decoration: none; font-weight: 500;">Lihat Lampiran Saat Ini</a>
                    </div>
                @endif
                @if(!$isDisabled)
                <div class="md-input-container" style="margin-bottom: 0; background: var(--input-bg);">
                    <input type="file" name="attachment" class="md-input" style="padding-top: 14px;">
                    <div class="md-bar"></div>
                </div>
                <small style="color: var(--text-secondary); display: block; margin-top: 4px;">Maks 10MB (PDF, JPG, PNG, DOCX)</small>
                @endif
            </div>

            <div class="md-input-container">
                <textarea name="notes" rows="3" class="md-input" placeholder=" " {{ $isDisabled ? 'readonly' : '' }}>{{ old('notes', $assignment->submission?->notes ?? '') }}</textarea>
                <label class="md-label">Catatan (Opsional)</label>
                <div class="md-bar"></div>
            </div>

            @if(!$isDisabled)
            <div class="d-flex" style="gap: 12px; justify-content: flex-end; padding-top: 16px; border-top: 1px solid var(--divider);">
                <button type="button" class="btn btn-flat ripple-surface" style="border: 1px solid var(--divider);" onclick="submitAjax(this, 'draft')">Simpan Draft</button>
                <button type="button" class="btn btn-primary ripple-surface" onclick="showMDModal('Konfirmasi Submit', 'Anda yakin ingin submit laporan ini ke admin? Setelah disubmit data tidak bisa diubah sebelum direview.', () => submitAjax(this, 'submit'))">Submit Laporan</button>
            </div>
            @else
                <div style="padding: 16px; background: #E3F2FD; color: #1565C0; border-radius: 4px; text-align: center;">
                    Tugas ini sudah dalam status <strong>{{ strtoupper($assignment->status) }}</strong> dan tidak dapat diubah lagi.
                </div>
            @endif
        </form>

        @if(!$isDisabled && $assignment->status === 'draft')
        <div style="margin-top: 16px; text-align: right;">
            <button type="button" class="btn btn-flat ripple-surface" style="color: #F44336;" onclick="showMDModal('Batalkan Pengisian', 'Yakin ingin membatalkan pengisian (menghapus draft) ini?', () => submitDeleteDraft('{{ route('user-tasks.delete-draft', $assignment->id) }}'))">
                <i class="material-icons" style="font-size: 18px; margin-right: 4px;">cancel</i> Batalkan Pengisian
            </button>
        </div>
        @endif

        @if(auth()->user()->role === 'superadmin')
        <div style="margin-top: 16px; text-align: right;">
            <button type="button" class="btn btn-flat ripple-surface" style="color: #F44336; background: #FFEBEE;" onclick="showMDModal('Hapus Tugas Permanen', 'Yakin ingin menghapus penugasan tugas ini dari daftar tugas PIC? (Aksi ini juga menghapus laporan yang bersangkutan)', () => submitDeleteDraft('{{ route('user-tasks.destroy', $assignment->id) }}'), true)">
                <i class="material-icons" style="font-size: 18px; margin-right: 4px;">delete_forever</i> Hapus Tugas
            </button>
        </div>
        @endif
    </div>
</div>

<!-- Komentar Section -->
<div class="card" style="max-width: 1000px; margin: 24px auto;">
    <h3 class="card-title">Komentar & Diskusi</h3>
    
    <div style="margin-bottom: 24px;">
        @forelse($assignment->comments as $comment)
            <div style="margin-bottom: 16px; padding: 16px; background: #F5F5F5; border-radius: 4px; border-left: 4px solid {{ $comment->user_id == auth()->id() ? 'var(--primary)' : '#9E9E9E' }};">
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

    <form action="{{ route('user-tasks.comment', $assignment->id) }}" method="POST">
        @csrf
        <div class="d-flex" style="gap: 16px; align-items: flex-start;">
            <div class="md-input-container" style="flex: 1; margin-bottom: 0;">
                <input type="text" name="body" required class="md-input" placeholder=" ">
                <label class="md-label">Tulis komentar...</label>
                <div class="md-bar"></div>
            </div>
            <button type="submit" class="btn btn-primary ripple-surface" style="height: 48px;"><i class="material-icons">send</i></button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
async function submitAjax(btnOrModal, actionType) {
    const form = document.getElementById('submitTaskForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const formData = new FormData(form);
    formData.append('action', actionType);
    
    // Show spinner if initiated by button directly (draft)
    let originalText = '';
    let btn = null;
    if (btnOrModal instanceof HTMLElement && btnOrModal.tagName === 'BUTTON') {
        btn = btnOrModal;
        originalText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner" style="width: 16px; height: 16px; border-width: 2px;"></span> Menyimpan...';
        btn.disabled = true;
    }
    
    try {
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
            showToast(data.message || 'Berhasil disimpan', 'success');
            setTimeout(() => {
                window.location.href = data.redirect || window.location.href;
            }, 1000);
        } else {
            // Handle validation errors
            if (data.errors) {
                const errorMsg = Object.values(data.errors).flat().join('\n');
                showToast(errorMsg, 'error');
            } else {
                showToast(data.message || 'Terjadi kesalahan', 'error');
            }
            if (btn) {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        }
    } catch (e) {
        showToast('Terjadi kesalahan jaringan', 'error');
        if (btn) {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    }
}

async function submitDeleteDraft(url) {
    showToast('Menghapus draft...', 'info');
    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                _method: 'DELETE'
            })
        });
        
        const data = await response.json();
        
        if (response.ok) {
            showToast(data.message || 'Draft berhasil dibatalkan', 'success');
            setTimeout(() => {
                window.location.href = data.redirect || window.location.href;
            }, 1000);
        } else {
            showToast(data.message || 'Terjadi kesalahan', 'error');
        }
    } catch (e) {
        showToast('Terjadi kesalahan jaringan', 'error');
    }
}
</script>
@endpush

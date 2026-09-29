@auth
<dialog id="modalMonlapRoleHelp" class="role-help-dialog" aria-labelledby="modalMonlapRoleHelpTitle">
    <header class="role-help-header">
        <div class="d-flex align-items-center gap-2">
            <span class="role-help-icon-badge"><i class="material-icons" style="font-size: 20px;">assignment_turned_in</i></span>
            <div>
                <h2 id="modalMonlapRoleHelpTitle" class="m-0 fs-5 fw-bold text-dark">Panduan MonLap</h2>
                <span class="text-secondary small">KPKNL Palembang · Tata Kelola &amp; Pengawasan Kepatuhan Laporan</span>
            </div>
        </div>
        <button type="button" class="role-help-close-btn" autofocus aria-label="Tutup panduan" onclick="this.closest('dialog').close()">
            <i class="material-icons" style="font-size: 16px; vertical-align: middle;">close</i> Tutup
        </button>
    </header>

    <div class="role-help-body">
        <!-- Active User Badge -->
        <div class="role-info-card mb-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="text-secondary small fw-medium">Peran Anda:</span>
                    <strong class="text-dark">{{ auth()->user()->role }}</strong>
                    <span class="badge bg-primary px-2.5 py-1 text-uppercase fw-bold" style="font-size: 0.72rem;">
                        {{ auth()->user()->role }}
                    </span>
                    @if(auth()->user()->role === 'maintenance')
                        <span class="badge bg-danger text-white px-2 py-1 rounded-pill" style="font-size: 0.7rem;">
                            <i class="material-icons" style="font-size: 12px; vertical-align: middle;">build</i> Maintenance aktif.
                        </span>
                    @endif
                </div>
                <div class="text-muted small">
                    <i class="material-icons" style="font-size: 14px; vertical-align: middle;">person</i> {{ auth()->user()->name }}
                </div>
            </div>
        </div>

        @if(auth()->user()->role === 'maintenance')
            <!-- SECTION KHUSUS: TIM MAINTENANCE -->
            <div class="guide-section mb-4">
                <div class="guide-section-header text-danger">
                    <i class="material-icons me-2" style="font-size: 18px;">build</i>
                    <span>Maintenance aktif.</span>
                </div>
                <div class="guide-card p-3 rounded-3 bg-light border border-danger-subtle">
                    <p class="small text-secondary mb-2">
                        Anda dapat mengelola tugas, agenda, laporan seluruh PIC, serta tindakan pemulihan seperti pembatalan ACC/pengisian. Periksa tugas dan catatan perubahan sebelum menjalankan tindakan tersebut.
                    </p>
                </div>
            </div>
        @endif

        <!-- SECTION: MENGISI LAPORAN TUGAS (SEMUA ROLE) -->
        <div class="guide-section mb-4">
            <div class="guide-section-header text-success">
                <i class="material-icons me-2" style="font-size: 18px;">task_alt</i>
                <span>Mengisi laporan tugas</span>
            </div>
            <div class="guide-card p-3 rounded-3 bg-light border border-success-subtle">
                <div class="step-item mb-2">
                    <div class="step-num bg-success text-white">1</div>
                    <div class="step-content">
                        <p class="small text-muted mb-0">
                            Buka <a href="{{ route('user-tasks.index') }}" class="fw-semibold">Todo-List Saya</a> dan pilih tugas yang diberikan kepada Anda.
                        </p>
                    </div>
                </div>
                <div class="step-item mb-2">
                    <div class="step-num bg-success text-white">2</div>
                    <div class="step-content">
                        <p class="small text-muted mb-0">
                            Periksa tenggat dan petunjuk tugas. Isi nomor surat, tanggal surat, serta catatan ringkas.
                        </p>
                    </div>
                </div>
                <div class="step-item mb-2">
                    <div class="step-num bg-success text-white">3</div>
                    <div class="step-content">
                        <p class="small text-muted mb-0">
                            Lampirkan bukti bila diperlukan: berkas PDF, JPG/JPEG, PNG, DOC, atau DOCX maksimum 10 MB.
                        </p>
                    </div>
                </div>
                <div class="step-item mb-2">
                    <div class="step-num bg-success text-white">4</div>
                    <div class="step-content">
                        <p class="small text-muted mb-0">
                            Pilih <strong>Simpan Draft</strong> untuk melanjutkan nanti, atau <strong>Submit Laporan</strong> untuk mengirim ke peninjau.
                        </p>
                    </div>
                </div>
                <div class="step-item mb-0">
                    <div class="step-num bg-success text-white">5</div>
                    <div class="step-content">
                        <p class="small text-muted mb-0">
                            Periksa notifikasi. Jika berstatus Revisi, baca komentar perbaikan, perbaiki laporan, lalu kirim ulang. Status ACC menandakan laporan telah disetujui.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        @if(in_array(auth()->user()->role, ['admin', 'superadmin', 'maintenance'], true))
            <!-- SECTION KHUSUS: MEMBUAT DAN MENINJAU TUGAS -->
            <div class="guide-section mb-4">
                <div class="guide-section-header text-primary">
                    <i class="material-icons me-2" style="font-size: 18px;">manage_accounts</i>
                    <span>Membuat dan meninjau tugas</span>
                </div>
                <div class="guide-card p-3 rounded-3 bg-light border border-primary-subtle">
                    <div class="step-item mb-2">
                        <div class="step-num bg-primary text-white">1</div>
                        <div class="step-content">
                            <p class="small text-muted mb-0">
                                Buka <a href="{{ route('tasks.index') }}" class="fw-semibold">Tugas Induk</a>, buat tugas baru, tentukan jadwal/tenggat waktu, dan pilih PIC penerima.
                            </p>
                        </div>
                    </div>
                    <div class="step-item mb-2">
                        <div class="step-num bg-primary text-white">2</div>
                        <div class="step-content">
                            <p class="small text-muted mb-0">
                                Pantau pekerjaan melalui dashboard utama dan <a href="{{ route('reviews.index') }}" class="fw-semibold">Daftar Laporan</a>.
                            </p>
                        </div>
                    </div>
                    <div class="step-item mb-2">
                        <div class="step-num bg-primary text-white">3</div>
                        <div class="step-content">
                            <p class="small text-muted mb-0">
                                Buka laporan yang sudah dikirim (Submitted), periksa isian dan berkas lampiran, lalu pilih ACC Laporan atau Kembalikan untuk Revisi beserta catatan wajib.
                            </p>
                        </div>
                    </div>
                    <div class="step-item mb-0">
                        <div class="step-num bg-primary text-white">4</div>
                        <div class="step-content">
                            <p class="small text-muted mb-0">
                                Kelola agenda kedinasan kantor melalui tampilan <a href="{{ route('calendar.index') }}">Kalender</a>.
                            </p>
                        </div>
                    </div>

                    @if(in_array(auth()->user()->role, ['superadmin', 'maintenance'], true))
                        <hr class="my-2 border-danger-subtle">
                        <p class="small text-danger mb-0">
                            <strong>Superadmin dan Maintenance dapat membatalkan ACC</strong> atau pengisian laporan. Periksa laporan dan alasan perubahan sebelum memakai tindakan tersebut.
                        </p>
                    @endif
                </div>
            </div>
        @else
            <div class="guide-section mb-4">
                <div class="p-3 rounded-3 bg-light border">
                    <p class="small text-muted mb-0">
                        Penugasan PIC dikelola admin. Jika penerima tugas keliru, hubungi admin untuk memperbaiki penugasan.
                    </p>
                </div>
            </div>
        @endif

        <!-- SECTION: FAQ / TROUBLESHOOTING -->
        <div class="guide-section mb-2">
            <div class="guide-section-header text-secondary">
                <i class="material-icons me-2" style="font-size: 18px;">help_outline</i>
                <span>Kendala umum</span>
            </div>
            <div class="p-3 rounded-3 bg-white border">
                <ul class="mb-0 ps-3 small text-secondary">
                    <li class="mb-2"><strong>Tugas tidak terlihat:</strong> Periksa akun, filter, dan penugasan bersama admin.</li>
                    <li class="mb-2"><strong>Unggah gagal:</strong> Periksa format dan ukuran berkas (maks 10 MB), lalu coba kembali.</li>
                    <li class="mb-0"><strong>Submit gagal:</strong> Pastikan kolom nomor surat dan tanggal surat wajib telah terisi.</li>
                </ul>
            </div>
        </div>
    </div>
</dialog>

<style>
.role-help-dialog { width: min(840px, calc(100% - 32px)); max-height: 88dvh; padding: 0; border: 1px solid #cbd5e1; border-radius: 20px; background: #ffffff; color: #1e293b; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); }
.role-help-dialog::backdrop { background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px); }
.role-help-header { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 18px 24px; border-bottom: 1px solid #e2e8f0; background: #f8fafc; border-top-left-radius: 20px; border-top-right-radius: 20px; }
.role-help-icon-badge { width: 38px; height: 38px; border-radius: 10px; background: #e0e7ff; color: #4338ca; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; }
.role-help-close-btn { border: 1px solid #cbd5e1; border-radius: 10px; padding: 6px 14px; background: #ffffff; color: #475569; font-weight: 500; font-size: 0.85rem; cursor: pointer; transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 4px; }
.role-help-close-btn:hover { background: #f1f5f9; color: #0f172a; }
.role-help-body { padding: 20px 24px; font-size: 0.92rem; line-height: 1.6; overflow-y: auto; max-height: calc(88dvh - 80px); }
.role-info-card { padding: 12px 18px; border-radius: 12px; background: #f1f5f9; border: 1px solid #e2e8f0; }
.guide-section-header { font-size: 0.95rem; font-weight: 700; display: flex; align-items: center; margin-bottom: 10px; letter-spacing: 0.2px; }
.step-item { display: flex; align-items: flex-start; gap: 12px; }
.step-num { width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700; flex-shrink: 0; margin-top: 2px; }
.step-content { flex: 1; }
.role-help-dialog a { color: #2563eb; text-decoration: underline; text-underline-offset: 2px; }
.role-help-dialog a:hover { color: #1d4ed8; }
.role-help-dialog :focus-visible { outline: 3px solid #3b82f6; outline-offset: 2px; }
[data-bs-theme="dark"] .role-help-dialog, [data-theme="dark"] .role-help-dialog, body.dark-mode .role-help-dialog { background: #0f172a; color: #e2e8f0; border-color: #334155; }
[data-bs-theme="dark"] .role-help-header, [data-theme="dark"] .role-help-header, body.dark-mode .role-help-header { background: #1e293b; border-color: #334155; }
[data-bs-theme="dark"] .role-help-header h2, [data-theme="dark"] .role-help-header h2, body.dark-mode .role-help-header h2 { color: #f8fafc !important; }
[data-bs-theme="dark"] .role-info-card, [data-theme="dark"] .role-info-card, body.dark-mode .role-info-card { background: #1e293b; border-color: #334155; }
[data-bs-theme="dark"] .guide-card, [data-theme="dark"] .guide-card, body.dark-mode .guide-card { background: #1e293b !important; border-color: #334155 !important; }
[data-bs-theme="dark"] .guide-card h6, [data-theme="dark"] .guide-card h6, body.dark-mode .guide-card h6 { color: #f1f5f9 !important; }
[data-bs-theme="dark"] .guide-section .bg-white, [data-theme="dark"] .guide-section .bg-white, body.dark-mode .guide-section .bg-white { background: #1e293b !important; border-color: #334155 !important; }
[data-bs-theme="dark"] .role-help-close-btn, [data-theme="dark"] .role-help-close-btn, body.dark-mode .role-help-close-btn { background: #334155; border-color: #475569; color: #f8fafc; }
[data-bs-theme="dark"] .role-help-body a, [data-theme="dark"] .role-help-body a, body.dark-mode .role-help-body a { color: #60a5fa; }
</style>
@endauth

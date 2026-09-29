@auth
<dialog id="modalSsoRoleHelp" class="role-help-dialog" aria-labelledby="modalSsoRoleHelpTitle">
    <header class="role-help-header">
        <div class="d-flex align-items-center gap-2">
            <span class="role-help-icon-badge"><i class="fas fa-shield-alt"></i></span>
            <div>
                <h2 id="modalSsoRoleHelpTitle" class="m-0 fs-5 fw-bold text-dark">Panduan Portal SSO</h2>
                <span class="text-secondary small">KPKNL Palembang · Gerbang Autentikasi &amp; Akses Layanan Terpadu</span>
            </div>
        </div>
        <button type="button" class="role-help-close-btn" autofocus aria-label="Tutup panduan" onclick="this.closest('dialog').close()">
            <i class="fas fa-times me-1"></i> Tutup
        </button>
    </header>

    <div class="role-help-body">
        <!-- Active User Badge -->
        <div class="role-info-card mb-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="text-secondary small fw-medium">Peran Portal Anda:</span>
                    <span class="badge bg-primary px-3 py-1.5 text-uppercase fw-bold" style="font-size: 0.78rem; letter-spacing: 0.5px;">
                        <i class="fas fa-id-badge me-1"></i> {{ auth()->user()->roles->pluck('name')->join(', ') ?: 'User' }}
                    </span>
                    @if(auth()->user()->isMaintenance())
                        <span class="badge bg-danger text-white px-2 py-1 rounded-pill" style="font-size: 0.7rem;">
                            <i class="fas fa-tools me-1"></i> Tim Maintenance Sistem
                        </span>
                    @endif
                </div>
                <div class="text-muted small">
                    <i class="fas fa-user-circle me-1"></i> {{ auth()->user()->name }} ({{ auth()->user()->username }})
                </div>
            </div>
        </div>

        <p class="small text-secondary mb-3">
            Akses aplikasi menggunakan akun kantor. Peran portal dan peran dalam setiap aplikasi dapat berbeda.
        </p>

        <!-- SECTION: MULAI MENGGUNAKAN PORTAL (SEMUA ROLE) -->
        <div class="guide-section mb-4">
            <div class="guide-section-header text-success">
                <i class="fas fa-laptop me-2"></i>
                <span>Mulai menggunakan portal</span>
            </div>
            <div class="guide-card p-3 rounded-3 bg-light border border-success-subtle">
                <div class="step-item mb-2">
                    <div class="step-num bg-success text-white">1</div>
                    <div class="step-content">
                        <p class="small text-muted mb-0">
                            Buka <a href="{{ route('dashboard') }}">Portal Aplikasi</a>, cari nama aplikasi di kolom <em>Cari aplikasi</em>, lalu pilih kartunya. Aplikasi terbuka di tab baru.
                        </p>
                    </div>
                </div>
                <div class="step-item mb-2">
                    <div class="step-num bg-success text-white">2</div>
                    <div class="step-content">
                        <p class="small text-muted mb-0">
                            Jika aplikasi belum tersedia di kartu Anda, hubungi pengelola akses dengan menyebutkan nama aplikasi dan kebutuhan tugas dinas Anda.
                        </p>
                    </div>
                </div>
                <div class="step-item mb-2">
                    <div class="step-num bg-success text-white">3</div>
                    <div class="step-content">
                        <p class="small text-muted mb-0">
                            Buka <a href="{{ route('profile.show') }}">Profil Saya</a> untuk memperbarui data biodata, foto avatar, atau memperbarui kata sandi kedinasan.
                        </p>
                    </div>
                </div>
                <div class="step-item mb-2">
                    <div class="step-num bg-success text-white">4</div>
                    <div class="step-content">
                        <p class="small text-muted mb-0">
                            Periksa <a href="{{ route('login-sessions.index') }}">Sesi Login</a> untuk melihat dan mencabut sesi perangkat yang tidak digunakan.
                        </p>
                    </div>
                </div>
                <div class="step-item mb-0">
                    <div class="step-num bg-success text-white">5</div>
                    <div class="step-content">
                        <p class="small text-muted mb-0">
                            Pilih Keluar setelah selesai bekerja. Aplikasi terintegrasi memeriksa status sesi SSO secara berkala (Single Sign-Out).
                        </p>
                    </div>
                </div>
            </div>
        </div>

        @if(auth()->user()->isSuperadmin() || auth()->user()->isMaintenance())
            <!-- SECTION KHUSUS: KELOLA PENGGUNA -->
            <div class="guide-section mb-4">
                <div class="guide-section-header text-primary">
                    <i class="fas fa-users-cog me-2"></i>
                    <span>Kelola pengguna</span>
                </div>
                <div class="guide-card p-3 rounded-3 bg-light border border-primary-subtle">
                    <div class="step-item mb-2">
                        <div class="step-num bg-primary text-white">1</div>
                        <div class="step-content">
                            <p class="small text-muted mb-0">
                                Buka <a href="{{ route('admin.users.index') }}">Manajemen Pengguna</a>.
                            </p>
                        </div>
                    </div>
                    <div class="step-item mb-2">
                        <div class="step-num bg-primary text-white">2</div>
                        <div class="step-content">
                            <p class="small text-muted mb-0">
                                Tambahkan pengguna baru atau pilih akun pengguna, lalu atur data kredensial dan perannya.
                            </p>
                        </div>
                    </div>
                    <div class="step-item mb-0">
                        <div class="step-num bg-primary text-white">3</div>
                        <div class="step-content">
                            <p class="small text-muted mb-0">
                                Periksa kembali akses aplikasi yang diperlukan sebelum menyimpan perubahan akun.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if(auth()->user()->isMaintenance())
            <!-- SECTION KHUSUS: MAINTENANCE (KELOLA APLIKASI & KELOLA AKSES APLIKASI) -->
            <div class="guide-section mb-4">
                <div class="guide-section-header text-danger">
                    <i class="fas fa-screwdriver-wrench me-2"></i>
                    <span>Kelola aplikasi</span>
                </div>
                <div class="guide-card p-3 rounded-3 bg-light border border-danger-subtle">
                    <p class="small text-muted mb-2">
                        Buka <a href="{{ route('admin.applications.index') }}">Manajemen Aplikasi</a> dan pilih aplikasi untuk mengatur endpoint, redirect URI, serta status mode pemeliharaan.
                    </p>

                    <h6 class="fw-bold text-dark mt-3 mb-2" style="font-size: 0.9rem;">Kelola akses aplikasi</h6>
                    <div class="step-item mb-2">
                        <div class="step-num bg-danger text-white">1</div>
                        <div class="step-content">
                            <p class="small text-muted mb-0">
                                Tambahkan pengguna dan atur peran aplikasi (pivot role) sesuai tugasnya: <code>admin</code>, <code>pelelang</code>, atau <code>peminjam</code>.
                            </p>
                        </div>
                    </div>
                    <div class="step-item mb-0">
                        <div class="step-num bg-danger text-white">2</div>
                        <div class="step-content">
                            <p class="small text-muted mb-0">
                                Minta pengguna masuk ulang ke aplikasi agar peran terbaru dari SSO diterapkan secara instan.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @elseif(auth()->user()->isAdmin())
            <div class="guide-section mb-4">
                <div class="guide-section-header text-info text-dark">
                    <i class="fas fa-user-shield me-2 text-info"></i>
                    <span>Peran admin aplikasi</span>
                </div>
                <div class="p-3 rounded-3 bg-light border">
                    <p class="small text-muted mb-0">
                        Kartu aplikasi menampilkan aplikasi yang diberikan kepada Anda. Kewenangan di dalamnya mengikuti peran aplikasi; menu pengaturan teknis portal dikelola Maintenance.
                    </p>
                </div>
            </div>
        @endif

        <!-- SECTION: FAQ / TROUBLESHOOTING -->
        <div class="guide-section mb-2">
            <div class="guide-section-header text-secondary">
                <i class="fas fa-circle-question me-2"></i>
                <span>Kendala umum</span>
            </div>
            <div class="p-3 rounded-3 bg-white border">
                <ul class="mb-0 ps-3 small text-secondary">
                    <li class="mb-2"><strong>Akses ditolak:</strong> Pastikan akun yang digunakan benar, lalu minta pengelola memeriksa penugasan aplikasi.</li>
                    <li class="mb-2"><strong>Dalam pemeliharaan:</strong> Tunggu pemberitahuan pengelola sebelum membuka kembali aplikasi yang sedang di-maintenance.</li>
                    <li class="mb-0"><strong>Sesi berakhir:</strong> Masuk kembali melalui portal SSO dan buka ulang kartu aplikasi.</li>
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
.role-help-close-btn { border: 1px solid #cbd5e1; border-radius: 10px; padding: 6px 14px; background: #ffffff; color: #475569; font-weight: 500; font-size: 0.85rem; cursor: pointer; transition: all 0.2s ease; }
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

@auth
<dialog id="modalHelpRole" class="role-help-dialog" aria-labelledby="modalHelpRoleTitle">
    <header class="role-help-header">
        <div class="d-flex align-items-center gap-2">
            <span class="role-help-icon-badge"><i class="fas fa-book-bookmark"></i></span>
            <div>
                <h2 id="modalHelpRoleTitle" class="m-0 fs-5 fw-bold text-dark">Panduan Peminjaman Risalah Lelang</h2>
                <span class="text-secondary small">KPKNL Palembang · Alur Tata Kelola &amp; Sirkulasi Berkas Fisik</span>
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
                    <span class="text-secondary small fw-medium">Peran Anda:</span>
                    <strong class="text-dark">{{ auth()->user()->role }}</strong>
                    <span class="badge {{ auth()->user()->getRoleBadgeClass() }} px-2.5 py-1 text-uppercase fw-bold" style="font-size: 0.72rem;">
                        {{ auth()->user()->getRoleLabel() }}
                    </span>
                    @if(session('sso_role') === 'maintenance' || auth()->user()->role === 'maintenance')
                        <span class="badge bg-danger text-white px-2 py-1 rounded-pill" style="font-size: 0.7rem;">
                            <i class="fas fa-wrench me-1"></i> Maintenance SSO aktif.
                        </span>
                    @endif
                </div>
                <div class="text-muted small">
                    <i class="fas fa-user-circle me-1"></i> {{ auth()->user()->name }}
                </div>
            </div>
        </div>

        @if(session('sso_role') === 'maintenance' || auth()->user()->role === 'maintenance')
            <!-- SECTION KHUSUS: TIM MAINTENANCE -->
            <div class="guide-section mb-4">
                <div class="guide-section-header text-danger">
                    <i class="fas fa-screwdriver-wrench me-2"></i>
                    <span>Maintenance SSO aktif.</span>
                </div>
                <div class="guide-card p-3 rounded-3 bg-light border border-danger-subtle">
                    <p class="small text-secondary mb-2">
                        Akses pengelolaan aplikasi menggunakan peran <strong>Administrator Arsip</strong>: validasi risalah, persetujuan pinjaman, pendaftaran/revisi, dan verifikasi pengembalian.
                    </p>
                    <p class="small text-muted mb-0">
                        Anda memiliki hak bypass penuh untuk memantau sirkulasi berkas fisik, menindaklanjuti permohonan pinjam yang terkendala, serta menjaga konsistensi database nomor risalah.
                    </p>
                </div>
            </div>
        @endif

        <!-- SECTION: MENCARI BERKAS (SEMUA ROLE) -->
        <div class="guide-section mb-4">
            <div class="guide-section-header text-dark">
                <i class="fas fa-search me-2 text-primary"></i>
                <span>Mencari berkas</span>
            </div>
            <div class="guide-card p-3 rounded-3 bg-light border">
                <div class="step-item mb-2">
                    <div class="step-num bg-primary text-white">1</div>
                    <div class="step-content">
                        <p class="small text-muted mb-0">
                            Buka <a href="{{ route('katalog.index') }}">Katalog Risalah</a>, cari nomor risalah atau gunakan filter yang tersedia.
                        </p>
                    </div>
                </div>
                <div class="step-item mb-0">
                    <div class="step-num bg-primary text-white">2</div>
                    <div class="step-content">
                        <p class="small text-muted mb-0">
                            Periksa detail dan ketersediaan berkas sebelum mengajukan peminjaman.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        @if(auth()->user()->role === 'admin')
            <!-- SECTION: ADMIN ARSIP SEKSI HI -->
            <div class="guide-section mb-4">
                <div class="guide-section-header text-primary">
                    <i class="fas fa-boxes-packing me-2"></i>
                    <span>Memvalidasi dan menyerahkan berkas</span>
                </div>
                <div class="guide-card p-3 rounded-3 bg-light border border-primary-subtle">
                    <div class="step-item mb-2">
                        <div class="step-num bg-primary text-white">1</div>
                        <div class="step-content">
                            <p class="small text-muted mb-0">
                                Buka <a href="{{ route('validasi.index') }}">Validasi Risalah</a>, cocokkan data dengan berkas fisik, lalu setujui atau kembalikan untuk revisi. Tentukan nomor lemari dan box penyimpanan fisik.
                            </p>
                        </div>
                    </div>
                    <div class="step-item mb-2">
                        <div class="step-num bg-primary text-white">2</div>
                        <div class="step-content">
                            <p class="small text-muted mb-0">
                                Buka <a href="{{ route('peminjaman.index') }}">Peminjaman</a>, periksa permohonan berstatus <em>Proses Peminjaman</em>, lalu setujui jika berkas siap.
                            </p>
                        </div>
                    </div>
                    <div class="step-item mb-2">
                        <div class="step-num bg-primary text-white">3</div>
                        <div class="step-content">
                            <p class="small text-muted mb-0">
                                Serahkan berkas kepada peminjam. Peminjam melakukan konfirmasi penerimaan agar status menjadi <em>Sedang Dipinjam</em>.
                            </p>
                        </div>
                    </div>
                    <div class="step-item mb-0">
                        <div class="step-num bg-primary text-white">4</div>
                        <div class="step-content">
                            <p class="small text-muted mb-0">
                                Setelah peminjam mengajukan pengembalian dan menyerahkan berkas, periksa fisiknya lalu verifikasi pengembalian. Status menjadi <em>Sudah Dikembalikan</em> dan berkas tersedia kembali di katalog.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @elseif(auth()->user()->role === 'peminjam')
            <!-- SECTION: PEMINJAM BERKAS -->
            <div class="guide-section mb-4">
                <div class="guide-section-header text-info text-dark">
                    <i class="fas fa-handshake-angle me-2 text-info"></i>
                    <span>Mengajukan dan mengembalikan pinjaman</span>
                </div>
                <div class="guide-card p-3 rounded-3 bg-light border border-info-subtle">
                    <div class="step-item mb-2">
                        <div class="step-num bg-info text-white">1</div>
                        <div class="step-content">
                            <p class="small text-muted mb-0">
                                Pilih berkas tersedia di Katalog, masukkan ke keranjang, isi formulir peminjaman dan keperluan kedinasan, lalu kirim.
                            </p>
                        </div>
                    </div>
                    <div class="step-item mb-2">
                        <div class="step-num bg-info text-white">2</div>
                        <div class="step-content">
                            <p class="small text-muted mb-0">
                                Pantau permohonan di <a href="{{ route('peminjaman.index') }}">Peminjaman</a>.
                            </p>
                        </div>
                    </div>
                    <div class="step-item mb-2">
                        <div class="step-num bg-info text-white">3</div>
                        <div class="step-content">
                            <p class="small text-muted mb-0">
                                Setelah disetujui dan berkas fisik diterima di Seksi HI, lakukan konfirmasi penerimaan. Status menjadi <em>Sedang Dipinjam</em>.
                            </p>
                        </div>
                    </div>
                    <div class="step-item mb-0">
                        <div class="step-num bg-info text-white">4</div>
                        <div class="step-content">
                            <p class="small text-muted mb-0">
                                Ajukan pengembalian dari daftar pinjaman, serahkan berkas fisik kepada petugas, dan pastikan petugas memverifikasi pengembalian hingga status <em>Sudah Dikembalikan</em>.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if(auth()->user()->role === 'admin' || auth()->user()->role === 'pelelang')
            <!-- SECTION: PENDAFTARAN & REVISI (PELELANG & ADMIN) -->
            <div class="guide-section mb-4">
                <div class="guide-section-header text-warning text-dark">
                    <i class="fas fa-file-signature me-2 text-warning"></i>
                    <span>Mendaftarkan dan merevisi risalah</span>
                </div>
                <div class="guide-card p-3 rounded-3 bg-light border border-warning-subtle">
                    <div class="step-item mb-2">
                        <div class="step-num bg-warning text-dark">1</div>
                        <div class="step-content">
                            <p class="small text-muted mb-0">
                                Buka <a href="{{ route('pendaftaran.index') }}">Pendaftaran</a>, isi identitas risalah serta jenis Minuta, TAP, atau Batal, lalu kirim untuk divalidasi petugas arsip.
                            </p>
                        </div>
                    </div>
                    <div class="step-item mb-0">
                        <div class="step-num bg-warning text-dark">2</div>
                        <div class="step-content">
                            <p class="small text-muted mb-0">
                                Jika perlu perbaikan, buka <a href="{{ route('revisi.index') }}">Revisi</a>, baca catatan kekurangan, perbaiki data atau tautan e-Risalah, lalu kirim ulang.
                            </p>
                        </div>
                    </div>
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
                    <li class="mb-2"><strong>Berkas tidak tersedia:</strong> Periksa status peminjaman dan hubungi petugas arsip Seksi HI.</li>
                    <li class="mb-2"><strong>Status belum berubah:</strong> Periksa tahap berikutnya; konfirmasi penerimaan dilakukan peminjam, verifikasi pengembalian dilakukan admin.</li>
                    <li class="mb-0"><strong>Data risalah keliru:</strong> Sampaikan nomor risalah dan koreksinya kepada petugas arsip.</li>
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

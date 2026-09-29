@auth
<dialog id="modalRoleHelp" class="role-help-dialog" aria-labelledby="modalRoleHelpTitle">
    <header class="role-help-header">
        <div class="d-flex align-items-center gap-2">
            <span class="role-help-icon-badge"><i class="fas fa-book-open-reader"></i></span>
            <div>
                <h2 id="modalRoleHelpTitle" class="m-0 fs-5 fw-bold text-dark">Panduan SI-KEP</h2>
                <span class="text-secondary small">Sistem Informasi Kepegawaian KPKNL Palembang · Terintegrasi SSO</span>
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
                    <span class="text-secondary small fw-medium">Peran Aktif Anda:</span>
                    <span class="badge bg-primary px-3 py-1.5 text-uppercase fw-bold" style="font-size: 0.78rem; letter-spacing: 0.5px;">
                        <i class="fas fa-shield-halved me-1"></i> {{ auth()->user()->role }}
                    </span>
                    @if(auth()->user()->role === 'maintenance')
                        <span class="badge bg-danger text-white px-2 py-1 rounded-pill" style="font-size: 0.7rem;">
                            <i class="fas fa-wrench me-1"></i> Tim Maintenance Sistem
                        </span>
                    @endif
                </div>
                <div class="text-muted small">
                    <i class="fas fa-user-circle me-1"></i> {{ auth()->user()->name }} ({{ auth()->user()->username }})
                </div>
            </div>
        </div>

        <!-- SECTION UMUM: MENELUSURI DATA PEGAWAI (UNTUK SEMUA ROLE) -->
        <div class="guide-section mb-4">
            <div class="guide-section-header text-dark">
                <i class="fas fa-compass me-2 text-primary"></i>
                <span>Menelusuri data pegawai</span>
            </div>
            <div class="guide-card p-3 rounded-3 bg-light border">
                <div class="step-item mb-2">
                    <div class="step-num bg-primary text-white">1</div>
                    <div class="step-content">
                        <p class="small text-muted mb-0">
                            Buka <a href="{{ route('pegawai.index') }}">Data Kepegawaian</a>, gunakan pencarian cepat nama/NIP dan filter unit kerja atau golongan untuk menemukan pegawai.
                        </p>
                    </div>
                </div>
                <div class="step-item mb-2">
                    <div class="step-num bg-primary text-white">2</div>
                    <div class="step-content">
                        <p class="small text-muted mb-0">
                            Pilih tombol <strong>Detail</strong> pegawai untuk melihat informasi lengkap jabatan, unit kerja, pangkat, dan kualifikasi pendidikan.
                        </p>
                    </div>
                </div>
                <div class="step-item mb-0">
                    <div class="step-num bg-primary text-white">3</div>
                    <div class="step-content">
                        <p class="small text-muted mb-0">
                            Akses menu <a href="{{ route('jabatan.index') }}">Struktur &amp; Jabatan</a>, <a href="{{ route('unit_kerja.index') }}">Formasi Unit Kerja</a>, atau <a href="{{ route('diagram.index') }}">Diagram &amp; Analitika</a> untuk melihat grafik rekapitulasi formasi.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        @if(auth()->user()->role === 'maintenance')
            <!-- SECTION KHUSUS: TIM MAINTENANCE -->
            <div class="guide-section mb-4">
                <div class="guide-section-header text-danger">
                    <i class="fas fa-screwdriver-wrench me-2"></i>
                    <span>Panduan Khusus Tim Maintenance (Dual-Write &amp; Sinkronisasi)</span>
                </div>
                <div class="guide-card p-3 rounded-3 bg-light border border-danger-subtle">
                    <div class="step-item mb-2">
                        <div class="step-num bg-danger text-white">1</div>
                        <div class="step-content">
                            <h6 class="fw-bold mb-1 text-dark">Pengaturan Webhook &amp; Uji Izin Spreadsheet</h6>
                            <p class="small text-muted mb-0">
                                Buka modal <strong>Pengaturan</strong> pada sidebar navigasi. Pastikan URL Google Apps Script Webhook (<code>/exec</code>) sesuai. Klik <strong>Uji Izin Spreadsheet</strong> untuk memastikan token dan hak tulis spreadsheet kantor aktif.
                            </p>
                        </div>
                    </div>
                    <div class="step-item mb-2">
                        <div class="step-num bg-danger text-white">2</div>
                        <div class="step-content">
                            <h6 class="fw-bold mb-1 text-dark">Sinkronisasi Ulang Antrean Tertunda (Sync-Pending)</h6>
                            <p class="small text-muted mb-0">
                                Buka menu <a href="{{ route('change_log.index') }}">Log Perubahan</a>. Jika ada transaksi berstatus pending, periksa diagnosa error, lalu klik tombol <strong>Sinkronkan Ulang Perubahan Tertunda</strong> di pojok kanan atas untuk memproses ulang pengiriman payload dengan aman.
                            </p>
                        </div>
                    </div>
                    <div class="step-item mb-0">
                        <div class="step-num bg-danger text-white">3</div>
                        <div class="step-content">
                            <h6 class="fw-bold mb-1 text-dark">Pemulihan Rollback &amp; Wipe Data Darurat</h6>
                            <p class="small text-muted mb-0">
                                Batalkan mutasi yang keliru menggunakan tombol <strong>Rollback</strong> di Log Perubahan. Untuk kondisi darurat desinkronisasi lokal total, gunakan tombol <strong>Wipe Data Pegawai</strong> pada modal Pengaturan lalu sinkronkan ulang dari spreadsheet master.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if(in_array(auth()->user()->role, ['superadmin', 'maintenance', 'administrator']))
            <!-- SECTION KHUSUS: PENGELOLA KEPEGAWAIAN -->
            <div class="guide-section mb-4">
                <div class="guide-section-header text-primary">
                    <i class="fas fa-user-pen me-2"></i>
                    <span>Mengubah data dan status HRIS</span>
                </div>
                <div class="guide-card p-3 rounded-3 bg-light border border-primary-subtle">
                    <div class="step-item mb-2">
                        <div class="step-num bg-primary text-white">1</div>
                        <div class="step-content">
                            <p class="small text-muted mb-0">
                                Pada Data Kepegawaian, pilih tombol <strong>Tambah</strong> untuk pegawai baru atau tombol <strong>Edit</strong> pada baris pegawai yang mengalami mutasi jabatan/pangkat.
                            </p>
                        </div>
                    </div>
                    <div class="step-item mb-2">
                        <div class="step-num bg-primary text-white">2</div>
                        <div class="step-content">
                            <p class="small text-muted mb-0">
                                Periksa NIP, nama lengkap, jabatan, unit kerja, pangkat/golongan, dan status pendidikan/gelar HRIS sesuai daftar validasi baku.
                            </p>
                        </div>
                    </div>
                    <div class="step-item mb-2">
                        <div class="step-num bg-primary text-white">3</div>
                        <div class="step-content">
                            <p class="small text-muted mb-0">
                                Simpan data, lalu periksa notifikasi layar. Basis data lokal tersimpan seketika sementara payload dual-write dikirim ke Google Spreadsheet secara asinkron.
                            </p>
                        </div>
                    </div>
                    <div class="step-item mb-0">
                        <div class="step-num bg-primary text-white">4</div>
                        <div class="step-content">
                            <p class="small text-muted mb-0">
                                Buka <a href="{{ route('change_log.index') }}">Log Perubahan</a> dan pastikan status transaksi menjadi <strong>synced</strong>. Jika berstatus pending, periksa pesan kesalahan dan gunakan tombol sinkronkan perubahan tertunda setelah memperbaiki format dropdown.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- SECTION KHUSUS: PEGAWAI / USER BIASA -->
            <div class="guide-section mb-4">
                <div class="guide-section-header text-success">
                    <i class="fas fa-user-shield me-2"></i>
                    <span>Mengajukan koreksi data pribadi</span>
                </div>
                <div class="guide-card p-3 rounded-3 bg-light border border-success-subtle">
                    <p class="small text-muted mb-0">
                        Sebagai akun pegawai biasa, Anda memiliki hak akses baca (read-only). Apabila terdapat data diri, jabatan, atau pangkat yang belum sesuai, catat NIP Anda dan kolom yang keliru, lalu sampaikan kepada Administrator Kepegawaian (Subbagian Umum).
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
                    <li class="mb-2"><strong>Data belum terbaru:</strong> Konfirmasikan waktu sinkronisasi terakhir kepada pengelola kepegawaian.</li>
                    <li class="mb-2"><strong>Perubahan belum masuk spreadsheet:</strong> Pengelola memeriksa status pada Log Perubahan sebelum menarik ulang data spreadsheet.</li>
                    <li class="mb-0"><strong>Foto atau profil tidak sesuai:</strong> Laporkan perbaikan data foto dan biodata kepada Subbagian Umum.</li>
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

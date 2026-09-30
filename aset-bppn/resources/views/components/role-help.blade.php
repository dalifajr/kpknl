@auth
<div id="modalAsetRoleHelp" class="modal modal-fixed-footer" style="max-width: 750px; border-radius: 12px; height: 85%;">
    <div class="modal-content" style="padding: 0;">
        <!-- Header -->
        <div class="blue darken-3 white-text" style="padding: 20px 24px; border-top-left-radius: 12px; border-top-right-radius: 12px; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 15px;">
                <div style="background: rgba(255,255,255,0.2); border-radius: 8px; width: 42px; height: 42px; display: flex; align-items: center; justify-content: center;">
                    <i class="material-icons">help_outline</i>
                </div>
                <div>
                    <h5 style="margin: 0; font-weight: 700; font-size: 1.25rem;">Panduan Manajemen Aset Eks BPPN</h5>
                    <span style="font-size: 0.85rem; opacity: 0.9;">KPKNL Palembang</span>
                </div>
            </div>
            <a href="#!" class="modal-close white-text waves-effect waves-light" style="padding: 4px; border-radius: 50%;"><i class="material-icons">close</i></a>
        </div>

        <!-- Body -->
        <div style="padding: 24px;">
            <!-- Info Role User -->
            <div style="background: #e3f2fd; padding: 16px; border-radius: 8px; margin-bottom: 24px; border: 1px solid #bbdefb; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 0.9rem; color: #1565c0; font-weight: 500;">Peran Anda:</span>
                    <strong style="font-size: 1rem; color: #0d47a1; text-transform: uppercase;">{{ auth()->user()->role ?? 'USER' }}</strong>
                    @if(in_array(auth()->user()->role, ['maintenance']))
                        <span style="background: #d32f2f; color: white; padding: 2px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: bold;"><i class="material-icons" style="font-size: 11px; vertical-align: middle;">build</i> Maintenance Aktif</span>
                    @endif
                </div>
                <div style="font-size: 0.9rem; color: #424242;">
                    <i class="material-icons" style="font-size: 16px; vertical-align: middle; color: #757575;">person</i> {{ auth()->user()->name }}
                </div>
            </div>

            <!-- Panduan UMUM (SEMUA ROLE) -->
            <div style="margin-bottom: 24px;">
                <h6 style="font-weight: 700; color: #0277bd; margin-bottom: 12px; display: flex; align-items: center;">
                    <i class="material-icons" style="font-size: 20px; margin-right: 8px;">inventory_2</i> Melihat & Mencari Aset
                </h6>
                <div style="border-left: 3px solid #0288d1; padding-left: 16px; margin-bottom: 16px;">
                    <p style="margin: 0 0 8px 0; font-size: 0.95rem; color: #424242;">Semua pengguna yang memiliki akses dapat menelusuri Gudang Aset.</p>
                    <ul style="margin: 0; padding-left: 18px; color: #616161; font-size: 0.9rem;">
                        <li style="margin-bottom: 4px;">Buka menu <strong>Gudang Aset</strong> dari bilah navigasi samping.</li>
                        <li style="margin-bottom: 4px;">Gunakan kotak pencarian atau saringan filter untuk mencari nama, jenis, atau lokasi aset tertentu.</li>
                        <li style="margin-bottom: 4px;">Klik pada salah satu baris aset untuk melihat detail, koordinat lokasi, dan dokumen kelengkapannya.</li>
                        <li style="margin-bottom: 0;">Klik <strong>Buku Profil PDF</strong> pada aset yang didukung untuk mengekspor data menjadi laporan terformat.</li>
                    </ul>
                </div>
            </div>

            @if(in_array(auth()->user()->role, ['admin', 'superadmin', 'maintenance']))
            <!-- Panduan ADMIN / MAINTENANCE -->
            <div style="margin-bottom: 24px;">
                <h6 style="font-weight: 700; color: #2e7d32; margin-bottom: 12px; display: flex; align-items: center;">
                    <i class="material-icons" style="font-size: 20px; margin-right: 8px;">edit_document</i> Mengelola Data & Import Aset
                </h6>
                <div style="border-left: 3px solid #388e3c; padding-left: 16px; margin-bottom: 16px;">
                    <p style="margin: 0 0 8px 0; font-size: 0.95rem; color: #424242;">Akses Administratif (Admin, Superadmin, Maintenance) dapat mengubah basis data.</p>
                    <ul style="margin: 0; padding-left: 18px; color: #616161; font-size: 0.9rem;">
                        <li style="margin-bottom: 4px;">Pada halaman Gudang Aset, gunakan tombol <strong>Tambah Aset Baru</strong> untuk mencatat entri secara manual.</li>
                        <li style="margin-bottom: 4px;">Pilih <strong>Impor Aset</strong> untuk memperbarui atau menambahkan daftar aset dalam jumlah besar (bulk) melalui berkas <code>.xlsx</code> / <code>.csv</code>. Pastikan format kolom sesuai dengan templat (template) bawaan sistem.</li>
                        <li style="margin-bottom: 4px;">Anda juga dapat mengubah foto, menghapus data yang tidak relevan, atau memutakhirkan nilai dan status aset secara satuan di rincian (detail) aset.</li>
                        <li style="margin-bottom: 0;">Seluruh aksi akan terekam. Buka <strong>Log Aktifitas</strong> untuk memeriksa rekam jejak siapa dan kapan melakukan perubahan data.</li>
                    </ul>
                </div>
            </div>
            @endif

            @if(in_array(auth()->user()->role, ['maintenance']))
            <!-- Panduan KHUSUS MAINTENANCE -->
            <div style="margin-bottom: 16px;">
                <h6 style="font-weight: 700; color: #c62828; margin-bottom: 12px; display: flex; align-items: center;">
                    <i class="material-icons" style="font-size: 20px; margin-right: 8px;">security</i> Akses Khusus
                </h6>
                <div style="border-left: 3px solid #d32f2f; background: #ffebee; padding: 12px 16px; border-radius: 0 8px 8px 0;">
                    <p style="margin: 0; font-size: 0.9rem; color: #b71c1c;">
                        Sebagai akun level-sistem (Maintenance), Anda dapat mengakses Log Aktifitas lengkap lintas waktu dan melakukan diagnosis bila fungsi importir bermasalah.
                    </p>
                </div>
            </div>
            @endif
            
            <div style="padding-top: 16px; border-top: 1px solid #e0e0e0; margin-top: 24px;">
                <p style="margin: 0; font-size: 0.85rem; color: #757575; text-align: center;">
                    Hubungi tim TIK atau administrator SSO jika menemukan galat pada aplikasi.
                </p>
            </div>
        </div>
    </div>
    <div class="modal-footer" style="padding: 0 24px; border-top: 1px solid #e0e0e0; background: #fafafa;">
        <a href="#!" class="modal-close waves-effect waves-dark btn-flat" style="font-weight: 600;">Mengerti & Tutup</a>
    </div>
</div>
@endauth

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tabel BMN Potensi Idle (Sheet: potensi idle)
        Schema::create('bmn_potensi_idle', function (Blueprint $table) {
            $table->id();
            $table->integer('row_no')->nullable()->index();
            $table->string('hasil_skor', 50)->nullable();
            $table->string('kanwil')->nullable()->index();
            $table->string('kpknl')->nullable()->index();
            $table->string('kode_satker', 100)->nullable()->index();
            $table->string('kementerian_lembaga')->nullable()->index();
            $table->string('nama_satker')->nullable()->index();
            $table->string('kode_barang', 50)->nullable()->index();
            $table->integer('nup')->nullable()->index();
            $table->string('kelompok_barang')->nullable()->index();
            $table->string('nama_barang')->nullable();
            $table->string('hasil_pengukuran_sbsk')->nullable();
            $table->string('surat_klarifikasi')->nullable();
            $table->date('tanggal_klarifikasi')->nullable();
            $table->string('surat_jawaban')->nullable();
            $table->date('tanggal_jawaban')->nullable();
            $table->text('hasil_jawaban')->nullable();
            $table->string('tujuan_surat')->nullable();
            $table->string('validasi_kanwil', 50)->nullable();
            $table->decimal('bobot_nilai', 8, 2)->nullable();
            $table->string('status_klarifikasi', 100)->nullable()->index();
            $table->string('pemetaan_jawaban', 150)->nullable()->index();
            $table->string('status_tindak_lanjut', 100)->nullable()->index(); // Pemantauan, Penelusuran, Penelitian
            $table->string('hasil_penelitian')->nullable()->index();
            $table->string('status_bmn_idle', 100)->nullable()->index(); // Bukan BMN Idle, BMN Idle
            $table->string('klasterisasi', 50)->nullable()->index(); // K1, K2, K3, K4
            $table->string('cek_tanggal')->nullable();
            $table->string('cek_kpknl')->nullable();
            
            // Helper fields
            $table->boolean('is_kemenkeu')->default(false)->index();
            $table->string('eselon1_kemenkeu', 50)->nullable()->index(); // DJP, DJBC, DJPb, DJKN
            $table->decimal('luas', 14, 2)->nullable();
            $table->decimal('nilai_perolehan', 18, 2)->nullable();
            $table->text('alamat')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            
            $table->timestamps();
        });

        // 2. Tabel BMN Eks Idle (Sheet: eks bmn idle)
        Schema::create('bmn_eks_idle', function (Blueprint $table) {
            $table->id();
            $table->integer('row_no')->nullable()->index();
            $table->string('nama_kanwil')->nullable()->index();
            $table->string('nama_kpknl')->nullable()->index();
            $table->string('kode_barang', 50)->nullable()->index();
            $table->string('uraian_barang')->nullable();
            $table->integer('nup')->nullable()->index();
            $table->decimal('luas', 14, 2)->nullable();
            $table->decimal('nilai_perolehan', 18, 2)->nullable();
            $table->text('alamat')->nullable();
            $table->string('jenis_tindak_lanjut')->nullable()->index();
            $table->string('jenis_pengelolaan')->nullable()->index(); // Penggunaan PSP, Hibah, Penghapusan, Pemanfaatan, Koreksi, Masih dalam kajian
            $table->text('no_surat')->nullable();
            $table->date('tanggal_surat')->nullable();
            $table->string('verifikasi_kanwil', 50)->nullable();
            $table->string('link_bukti_dokumen')->nullable();
            $table->string('tipe')->nullable();
            $table->decimal('bobot_nilai', 8, 2)->nullable();
            $table->decimal('nilai', 8, 2)->nullable();
            
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            
            $table->timestamps();
        });

        // 3. Tabel Sync Logs
        Schema::create('sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('source_type')->default('GOOGLE_SHEET');
            $table->string('status', 50); // SUCCESS, FAILED, RUNNING
            $table->integer('potensi_synced')->default(0);
            $table->integer('eks_idle_synced')->default(0);
            $table->decimal('duration_seconds', 8, 2)->default(0);
            $table->text('error_message')->nullable();
            $table->string('triggered_by')->default('AUTO_SCHEDULER'); // AUTO_SCHEDULER, MANUAL_BUTTON, ARTISAN_CLI
            $table->timestamps();
        });

        // 4. Tabel Executive Notes
        Schema::create('executive_notes', function (Blueprint $table) {
            $table->id();
            $table->string('section_key', 100)->index(); // dashboard_eks_idle, dashboard_terindikasi, pemetaan_kemenkeu, potensi_kemenkeu, matriks_capaian
            $table->text('note_content');
            $table->string('author_name')->default('Pimpinan / Pengelola');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('executive_notes');
        Schema::dropIfExists('sync_logs');
        Schema::dropIfExists('bmn_eks_idle');
        Schema::dropIfExists('bmn_potensi_idle');
    }
};

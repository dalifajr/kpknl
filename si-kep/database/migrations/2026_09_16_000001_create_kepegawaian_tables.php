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
        Schema::create('unit_kerja', function (Blueprint $table) {
            $table->id();
            $table->string('kode_unit', 50)->nullable()->unique();
            $table->string('nama_unit', 150);
            $table->string('singkatan', 50)->nullable();
            $table->unsignedBigInteger('kepala_pegawai_id')->nullable();
            $table->integer('urutan')->default(0);
            $table->timestamps();
        });

        Schema::create('jabatan', function (Blueprint $table) {
            $table->id();
            $table->string('nama_jabatan', 150);
            $table->enum('jenis_jabatan', ['struktural', 'fungsional', 'pelaksana', 'ppnpn'])->default('pelaksana');
            $table->string('level_eselon', 50)->nullable();
            $table->integer('standar_grade')->nullable();
            $table->timestamps();
        });

        Schema::create('pangkat_golongan', function (Blueprint $table) {
            $table->id();
            $table->string('nama_pangkat', 100);
            $table->string('golongan_ruang', 20);
            $table->integer('hirarki_level')->default(0);
            $table->timestamps();
        });

        Schema::create('pegawai', function (Blueprint $table) {
            $table->id();
            $table->integer('no_urut')->nullable();
            $table->string('nip', 25)->nullable()->index();
            $table->string('nik', 25)->nullable();
            $table->string('nama', 150);
            $table->string('nama_lengkap_gelar', 200)->nullable();
            $table->enum('tipe_pegawai', ['pns', 'ppnpn'])->default('pns');
            
            $table->foreignId('unit_kerja_id')->nullable()->constrained('unit_kerja')->nullOnDelete();
            $table->foreignId('jabatan_id')->nullable()->constrained('jabatan')->nullOnDelete();
            $table->foreignId('pangkat_golongan_id')->nullable()->constrained('pangkat_golongan')->nullOnDelete();
            
            $table->string('nama_jabatan_raw', 200)->nullable();
            $table->string('per_jabatan', 200)->nullable();
            $table->integer('job_grade')->nullable();
            
            $table->date('tmt_nip')->nullable();
            $table->date('tmt_eselon')->nullable();
            $table->date('tmt_palembang')->nullable();
            
            $table->string('masa_kerja_raw', 50)->nullable();
            $table->integer('masa_kerja_tahun')->default(0);
            $table->integer('masa_kerja_bulan')->default(0);
            
            $table->string('lama_palembang_raw', 50)->nullable();
            $table->integer('lama_palembang_tahun')->default(0);
            $table->integer('lama_palembang_bulan')->default(0);
            
            $table->string('pangkat_golongan_raw', 100)->nullable();
            $table->date('tmt_golongan')->nullable();
            $table->date('tmt_kgb')->nullable();
            $table->date('tmt_grading')->nullable();
            
            $table->string('tempat_lahir', 100)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('usia_raw', 50)->nullable();
            $table->integer('usia_tahun')->default(0);
            $table->integer('usia_bulan')->default(0);
            $table->enum('jenis_kelamin', ['L', 'P'])->default('L');
            
            $table->string('pendidikan_terakhir', 50)->nullable();
            $table->string('fakultas', 100)->nullable();
            $table->string('jurusan', 100)->nullable();
            $table->integer('tahun_lulus')->nullable();
            $table->string('nama_universitas', 150)->nullable();
            
            $table->string('tmt_ue_iv', 50)->nullable();
            $table->string('lama_bertugas_ue_iv', 50)->nullable();
            $table->string('status_gelar', 150)->nullable();
            
            $table->boolean('validasi_jabatan')->default(true);
            $table->boolean('validasi_pangkat')->default(true);
            $table->boolean('validasi_pendidikan')->default(true);
            
            $table->string('avatar_url', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('sync_logs', function (Blueprint $table) {
            $table->id();
            $table->text('source_url')->nullable();
            $table->integer('total_rows_imported')->default(0);
            $table->integer('total_pns')->default(0);
            $table->integer('total_ppnpn')->default(0);
            $table->enum('status', ['success', 'failed'])->default('success');
            $table->text('message')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });

        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            $table->string('description', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_settings');
        Schema::dropIfExists('sync_logs');
        Schema::dropIfExists('pegawai');
        Schema::dropIfExists('pangkat_golongan');
        Schema::dropIfExists('jabatan');
        Schema::dropIfExists('unit_kerja');
    }
};

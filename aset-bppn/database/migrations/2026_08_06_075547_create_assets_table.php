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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('kode_aset')->unique();
            $table->enum('jenis_aset', ['Tanah', 'Bangunan', 'Tanah dan Bangunan']);
            
            // Alamat Lama
            $table->string('alamatlama_namajalan')->nullable();
            $table->string('alamatlama_rt_rw')->nullable();
            $table->char('alamatlama_provinsi_id', 2)->nullable();
            $table->char('alamatlama_kota_kab_id', 4)->nullable();
            $table->char('alamatlama_kecamatan_id', 7)->nullable();
            $table->char('alamatlama_kelurahan_id', 10)->nullable();

            // Alamat Baru (Sesuai Master Wilayah)
            $table->string('alamat_namajalan')->nullable();
            $table->string('alamat_rt_rw')->nullable();
            $table->char('alamat_provinsi_id', 2)->nullable();
            $table->char('alamat_kota_kab_id', 4)->nullable();
            $table->char('alamat_kecamatan_id', 7)->nullable();
            $table->char('alamat_kelurahan_id', 10)->nullable();
            $table->string('kodepos', 10)->nullable();
            
            // Batas
            $table->string('batas_utara')->nullable();
            $table->string('batas_selatan')->nullable();
            $table->string('batas_timur')->nullable();
            $table->string('batas_barat')->nullable();
            
            // Koordinat Peta
            $table->decimal('koordinat_latitude', 10, 8)->nullable();
            $table->decimal('koordinat_longitude', 11, 8)->nullable();
            $table->string('koordinat_link')->nullable();
            
            // Legalitas & Nilai
            $table->string('jenis_bukti_kepemilikan')->nullable();
            $table->string('nomor_bukti_kepemilikan')->nullable();
            $table->decimal('luas_tanah', 12, 2)->nullable();
            $table->decimal('luas_bangunan', 12, 2)->nullable();
            $table->decimal('njop', 15, 2)->nullable();
            
            // Kondisi Fisik
            $table->string('kondisi_aset')->nullable(); // DIHUNI, DIGUNAKAN PIHAK KETIGA, KOSONG, LAIN-LAIN
            $table->string('kondisi_aset_lainlain')->nullable();
            $table->text('potensi_aset')->nullable();
            $table->boolean('papan_nama')->default(false);
            $table->string('wakil_kerja')->nullable();
            $table->text('keterangan_kondisi_aset')->nullable();
            $table->date('tanggal_update_kondisi')->nullable();
            
            // Dokumen dan Foto
            $table->string('foto_path')->nullable();
            $table->string('dokumen_path')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign Keys
            $table->foreign('alamat_provinsi_id')->references('id')->on('provinces')->nullOnDelete();
            $table->foreign('alamat_kota_kab_id')->references('id')->on('regencies')->nullOnDelete();
            $table->foreign('alamat_kecamatan_id')->references('id')->on('districts')->nullOnDelete();
            $table->foreign('alamat_kelurahan_id')->references('id')->on('villages')->nullOnDelete();

            $table->foreign('alamatlama_provinsi_id')->references('id')->on('provinces')->nullOnDelete();
            $table->foreign('alamatlama_kota_kab_id')->references('id')->on('regencies')->nullOnDelete();
            $table->foreign('alamatlama_kecamatan_id')->references('id')->on('districts')->nullOnDelete();
            $table->foreign('alamatlama_kelurahan_id')->references('id')->on('villages')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};

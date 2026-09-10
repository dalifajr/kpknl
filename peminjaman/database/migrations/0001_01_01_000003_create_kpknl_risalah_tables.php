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
        Schema::create('risalah_pending', function (Blueprint $table) {
            $table->increments('id');
            $table->string('no_risalah', 50);
            $table->enum('jenis', ['minuta', 'tap', 'batal']);
            $table->date('tgl_risalah');
            $table->date('tgl_validasi')->nullable();
            $table->string('nama_pelelang', 100);
            $table->string('pemohon_lelang', 100);
            $table->text('link_erisalah')->nullable();
            $table->string('box', 50)->nullable();
            $table->string('lemari', 50)->nullable();
            $table->text('keterangan')->nullable();
            $table->text('catatan')->nullable();
            $table->enum('status', ['belum_validasi', 'validasi'])->default('belum_validasi');
        });

        Schema::create('risalah_minuta', function (Blueprint $table) {
            $table->increments('id');
            $table->string('no_risalah', 50)->nullable();
            $table->date('tgl_risalah')->nullable();
            $table->date('tgl_validasi')->nullable();
            $table->string('nama_pelelang', 100)->nullable();
            $table->string('pemohon_lelang', 100)->nullable();
            $table->text('link_erisalah')->nullable();
            $table->string('box', 50)->nullable();
            $table->string('lemari', 50)->nullable();
            $table->enum('status', ['tersedia', 'sedang_dipinjam'])->default('tersedia');
        });

        Schema::create('risalah_tap', function (Blueprint $table) {
            $table->increments('id');
            $table->string('no_risalah', 50)->nullable();
            $table->date('tgl_risalah')->nullable();
            $table->date('tgl_validasi')->nullable();
            $table->string('nama_pelelang', 100)->nullable();
            $table->string('pemohon_lelang', 100)->nullable();
            $table->text('link_erisalah')->nullable();
            $table->string('box', 50)->nullable();
            $table->string('lemari', 50)->nullable();
            $table->enum('status', ['tersedia', 'sedang_dipinjam'])->default('tersedia');
        });

        Schema::create('risalah_batal', function (Blueprint $table) {
            $table->increments('id');
            $table->string('no_risalah', 50)->nullable();
            $table->date('tgl_risalah')->nullable();
            $table->date('tgl_validasi')->nullable();
            $table->string('nama_pelelang', 100)->nullable();
            $table->string('pemohon_lelang', 100)->nullable();
            $table->text('link_erisalah')->nullable();
            $table->string('box', 50)->nullable();
            $table->string('lemari', 50)->nullable();
            $table->enum('status', ['tersedia', 'sedang_dipinjam'])->default('tersedia');
        });

        Schema::create('risalah_revisi', function (Blueprint $table) {
            $table->increments('id');
            $table->string('no_risalah', 50);
            $table->enum('jenis', ['minuta', 'tap', 'batal']);
            $table->date('tgl_risalah');
            $table->date('tgl_revisi');
            $table->string('nama_pelelang', 100);
            $table->string('pemohon_lelang', 100);
            $table->text('catatan')->nullable();
            $table->enum('status', ['revisi', 'dikirim'])->default('revisi');
            $table->text('catatan_no')->nullable();
            $table->text('catatan_jenis')->nullable();
            $table->text('catatan_tgl')->nullable();
            $table->text('catatan_pelelang')->nullable();
            $table->text('catatan_pemohon')->nullable();
        });

        Schema::create('peminjaman', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nama_peminjam', 100)->nullable();
            $table->string('no_risalah', 50)->nullable();
            $table->date('tgl_risalah')->nullable();
            $table->string('nama_pelelang', 100)->nullable();
            $table->string('pemohon_lelang', 150)->nullable();
            $table->string('box', 10)->nullable();
            $table->string('lemari', 10)->nullable();
            $table->date('tgl_peminjaman')->nullable();
            $table->date('tgl_pengembalian')->nullable();
            $table->string('status', 50)->nullable();
            $table->text('alasan_peminjaman')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peminjaman');
        Schema::dropIfExists('risalah_revisi');
        Schema::dropIfExists('risalah_batal');
        Schema::dropIfExists('risalah_tap');
        Schema::dropIfExists('risalah_minuta');
        Schema::dropIfExists('risalah_pending');
    }
};

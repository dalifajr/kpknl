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
        Schema::table('assets', function (Blueprint $table) {
            $table->string('sewa_lelang_nama_pihak')->nullable()->after('kondisi_aset_lainlain');
            $table->date('sewa_lelang_tgl_mulai')->nullable()->after('sewa_lelang_nama_pihak');
            $table->date('sewa_lelang_tgl_selesai')->nullable()->after('sewa_lelang_tgl_mulai');
            $table->decimal('sewa_lelang_nilai', 15, 2)->nullable()->after('sewa_lelang_tgl_selesai');
            $table->string('sewa_lelang_no_surat')->nullable()->after('sewa_lelang_nilai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn([
                'sewa_lelang_nama_pihak',
                'sewa_lelang_tgl_mulai',
                'sewa_lelang_tgl_selesai',
                'sewa_lelang_nilai',
                'sewa_lelang_no_surat',
            ]);
        });
    }
};

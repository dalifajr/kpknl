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
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->after('name');
            $table->string('role')->default('pegawai')->after('email'); // superadmin, admin, pegawai, eksekutif
            $table->unsignedBigInteger('sso_id')->nullable()->after('role');
            $table->string('avatar_url')->nullable()->after('sso_id');
            $table->string('nip', 50)->nullable()->after('avatar_url');
            $table->string('jabatan')->nullable()->after('nip');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['username', 'role', 'sso_id', 'avatar_url', 'nip', 'jabatan']);
        });
    }
};

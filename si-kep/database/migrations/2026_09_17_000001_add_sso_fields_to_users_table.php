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
            $table->unsignedBigInteger('sso_user_id')->nullable()->unique()->after('id');
            $table->string('username', 100)->nullable()->after('name');
            $table->string('role', 50)->default('user')->after('password');
            $table->string('avatar_url', 255)->nullable()->after('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['sso_user_id', 'username', 'role', 'avatar_url']);
        });
    }
};

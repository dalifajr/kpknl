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
        Schema::table('applications', function (Blueprint $table) {
            $table->string('folder_path')->nullable()->after('redirect_uri');
            $table->string('database_name')->nullable()->after('folder_path');
            $table->string('git_branch')->default('main')->after('database_name');
            $table->string('current_version')->nullable()->after('git_branch');
            $table->string('current_commit', 40)->nullable()->after('current_version');
            $table->boolean('maintenance_mode')->default(false)->after('current_commit');
            $table->string('maintenance_bypass_token')->nullable()->after('maintenance_mode');
            $table->enum('health_status', ['healthy', 'degraded', 'down', 'maintenance'])->default('healthy')->after('maintenance_bypass_token');
            $table->timestamp('last_health_check_at')->nullable()->after('health_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn([
                'folder_path',
                'database_name',
                'git_branch',
                'current_version',
                'current_commit',
                'maintenance_mode',
                'maintenance_bypass_token',
                'health_status',
                'last_health_check_at',
            ]);
        });
    }
};

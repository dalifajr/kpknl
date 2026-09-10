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
        Schema::table('tasks', function (Blueprint $table) {
            $table->date('custom_start_date')->nullable()->after('deadline_next_month');
            $table->date('custom_end_date')->nullable()->after('custom_start_date');
            $table->boolean('is_recurring')->default(false)->after('custom_end_date');
            $table->string('recurring_interval')->nullable()->after('is_recurring'); // daily, weekly, monthly, yearly
        });

        Schema::table('task_assignments', function (Blueprint $table) {
            $table->date('open_date')->nullable()->after('period');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['custom_start_date', 'custom_end_date', 'is_recurring', 'recurring_interval']);
        });

        Schema::table('task_assignments', function (Blueprint $table) {
            $table->dropColumn('open_date');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('app_settings')) {
            Schema::create('app_settings', function (Blueprint $table) {
                $table->id();
                $table->string('setting_key', 100)->unique();
                $table->text('setting_value')->nullable();
                $table->string('setting_group', 50)->default('general');
                $table->text('description')->nullable();
                $table->timestamps();
            });

            // Insert initial default Google Spreadsheet settings
            DB::table('app_settings')->insert([
                [
                    'setting_key' => 'spreadsheet_potensi_url',
                    'setting_value' => 'https://docs.google.com/spreadsheets/d/1t1SdKB0VAbkvQ8k2iOCXTSWc5qhm6T1hDYjnUyqwM3M/edit?gid=155799981#gid=155799981',
                    'setting_group' => 'spreadsheet',
                    'description' => 'Link Google Spreadsheet sumber data BMN Terindikasi Idle & Potensi Idle (Sheet: potensi idle)',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'setting_key' => 'spreadsheet_eks_url',
                    'setting_value' => 'https://docs.google.com/spreadsheets/d/1aInDFtFG7vHIsK6qa472ONeaH9OKmVQYatr93mfGRjA/edit?gid=698305896#gid=698305896',
                    'setting_group' => 'spreadsheet',
                    'description' => 'Link Google Spreadsheet sumber data BMN Eks BMN Idle (Sheet: eks bmn idle)',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};

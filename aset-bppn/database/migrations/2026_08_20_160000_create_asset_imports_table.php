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
        Schema::create('asset_imports', function (Blueprint $table) {
            $table->id();
            $table->string('file_name');
            $table->string('file_path')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('total_rows')->default(0);
            $table->integer('success_rows')->default(0);
            $table->integer('failed_rows')->default(0);
            $table->string('status')->default('completed'); // completed, partial, failed, pending
            $table->text('error_summary')->nullable();
            $table->json('selected_columns')->nullable();
            $table->timestamps();
        });

        Schema::create('asset_import_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_import_id')->constrained('asset_imports')->cascadeOnDelete();
            $table->integer('row_number');
            $table->string('kode_aset')->nullable();
            $table->json('raw_data');
            $table->string('status')->default('success'); // success, failed, skipped
            $table->text('error_message')->nullable();
            $table->foreignId('created_asset_id')->nullable()->constrained('assets')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_import_items');
        Schema::dropIfExists('asset_imports');
    }
};

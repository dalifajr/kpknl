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
        // 1. Application Releases (Version catalog & tags)
        Schema::create('application_releases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications')->onDelete('cascade');
            $table->string('version_tag', 50);
            $table->string('commit_hash', 40);
            $table->string('release_title');
            $table->text('release_notes')->nullable();
            $table->boolean('is_stable')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['application_id', 'version_tag']);
            $table->index(['application_id', 'commit_hash']);
        });

        // 2. Application Backups (Pre-deployment and manual database dumps)
        Schema::create('application_backups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications')->onDelete('cascade');
            $table->enum('backup_type', ['auto_pre_deploy', 'manual', 'scheduled'])->default('manual');
            $table->string('filename');
            $table->string('db_dump_path');
            $table->unsignedBigInteger('file_size_bytes')->default(0);
            $table->string('commit_hash', 40)->nullable();
            $table->boolean('is_restorable')->default(true);
            $table->string('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['application_id', 'created_at']);
        });

        // 3. Application Deployments (Execution history & rollback records)
        Schema::create('application_deployments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications')->onDelete('cascade');
            $table->foreignId('release_id')->nullable()->constrained('application_releases')->onDelete('set null');
            $table->foreignId('backup_id')->nullable()->constrained('application_backups')->onDelete('set null');
            $table->enum('deployment_type', ['update', 'downgrade', 'hotfix', 'schema_migration'])->default('update');
            $table->string('source_commit', 40)->nullable();
            $table->string('target_commit', 40);
            $table->enum('status', ['pending', 'in_progress', 'success', 'failed', 'rolled_back'])->default('pending');
            $table->string('current_stage')->nullable();
            $table->longText('pipeline_logs')->nullable();
            $table->foreignId('deployed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['application_id', 'status']);
            $table->index(['application_id', 'created_at']);
        });

        // 4. Maintenance Events (Maintenance calendar & notifications)
        Schema::create('maintenance_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications')->onDelete('cascade');
            $table->enum('event_type', ['scheduled', 'emergency', 'deployment', 'incident'])->default('deployment');
            $table->enum('status', ['upcoming', 'active', 'completed', 'cancelled'])->default('active');
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamp('start_time')->useCurrent();
            $table->timestamp('end_time')->nullable();
            $table->string('bypass_secret')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['application_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_events');
        Schema::dropIfExists('application_deployments');
        Schema::dropIfExists('application_backups');
        Schema::dropIfExists('application_releases');
    }
};

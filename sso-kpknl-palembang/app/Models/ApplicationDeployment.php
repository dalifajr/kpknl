<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationDeployment extends Model
{
    protected $fillable = [
        'application_id',
        'release_id',
        'backup_id',
        'deployment_type',
        'source_commit',
        'target_commit',
        'status',
        'current_stage',
        'pipeline_logs',
        'deployed_by',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function release(): BelongsTo
    {
        return $this->belongsTo(ApplicationRelease::class, 'release_id');
    }

    public function backup(): BelongsTo
    {
        return $this->belongsTo(ApplicationBackup::class, 'backup_id');
    }

    public function deployer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deployed_by');
    }

    /**
     * Helper to append log line
     */
    public function appendLog(string $stage, string $message): void
    {
        $timestamp = now()->toDateTimeString();
        $logEntry = "[{$timestamp}] [{$stage}] {$message}\n";
        $this->current_stage = $stage;
        $this->pipeline_logs = ($this->pipeline_logs ?? '') . $logEntry;
        $this->save();
    }
}

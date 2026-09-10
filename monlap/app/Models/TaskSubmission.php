<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskSubmission extends Model
{
    protected $fillable = [
        'task_assignment_id',
        'nomor_surat',
        'tanggal_surat',
        'attachment_path',
        'notes',
        'submitted_by',
    ];

    protected $casts = [
        'tanggal_surat' => 'date',
    ];

    public function assignment()
    {
        return $this->belongsTo(TaskAssignment::class, 'task_assignment_id');
    }

    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskAssignment extends Model
{
    protected $fillable = [
        'task_id',
        'user_id',
        'period',
        'open_date',
        'deadline_date',
        'status',
        'reviewed_by',
    ];

    protected $casts = [
        'open_date' => 'date',
        'deadline_date' => 'date',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class)->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function submission()
    {
        return $this->hasOne(TaskSubmission::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}

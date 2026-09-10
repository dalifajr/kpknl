<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'title',
        'description',
        'period_type',
        'deadline_type',
        'deadline_rule',
        'deadline_next_month',
        'custom_start_date',
        'custom_end_date',
        'is_recurring',
        'recurring_interval',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'deadline_next_month' => 'boolean',
        'is_recurring' => 'boolean',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function assignments()
    {
        return $this->hasMany(TaskAssignment::class);
    }
}

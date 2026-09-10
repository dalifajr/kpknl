<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subbidang extends Model
{
    protected $fillable = ['name', 'description'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}

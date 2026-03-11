<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'worker_id',
        'job_id',
        'date',
        'start_time',
        'end_time',
        'break_minutes',
        'hours',
        'status',
    ];

    public function worker()
    {
        return $this->belongsTo(User::class, 'worker_id');
    }

    public function job()
    {
        return $this->belongsTo(Job::class);
    }
}

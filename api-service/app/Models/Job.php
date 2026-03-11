<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\Skill;

class Job extends Model
{
    protected $fillable = ['title', 'description', 'hourly_rate'];

    public function skills()
    {
        return $this->belongsToMany(Skill::class);
    }
}
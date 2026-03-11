<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'worker_id',
        'period_start',
        'period_end',
        'gross_amount',
        'net_amount',
        'taxes',
        'status',
    ];

    public function worker()
    {
        return $this->belongsTo(User::class, 'worker_id');
    }

    public function paystubs()
    {
        return $this->hasMany(Paystub::class);
    }
}

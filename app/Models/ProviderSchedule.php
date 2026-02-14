<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProviderSchedule extends Model
{
    protected $fillable = [
        'provider_id',
        'day_of_week',
        'start_time',
        'end_time',
        'slot_duration'
    ];

    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id');
    }
}

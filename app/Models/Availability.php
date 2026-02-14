<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Availability extends Model
{

    protected $fillable = [
        'provider_id',
       
        'start_time',
        'end_time',
        'date',
        'is_available'
    ];

    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id');
    }
}

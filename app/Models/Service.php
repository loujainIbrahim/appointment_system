<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{

    protected $fillable = ['name'];

   public function providers()
{
    return $this->belongsToMany(
        User::class,
        'provider_service',
        'service_id',
        'provider_id'
    )->withPivot('price', 'duration');
}

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}

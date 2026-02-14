<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [

        'name',
        'email',
        'password',
        'role_id',
        'location_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function schedules()
    {
        return $this->hasMany(ProviderSchedule::class, 'provider_id');
    }

    public function availabilities()
    {
        return $this->hasMany(Availability::class, 'provider_id');
    }
    public function services()
    {
        return $this->belongsToMany(Service::class,
        'provider_service',
        'provider_id',
        'service_id',

    )->withPivot('price', 'duration');
    }

    public function providerAppointments()
    {
        return $this->hasMany(Appointment::class, 'provider_id');
    }

    public function customerAppointments()
    {
        return $this->hasMany(Appointment::class, 'customer_id');
    }
    public function isAdmin(): bool
    {
        return $this->role?->name === 'admin';
    }

    public function isProvider(): bool
    {
        return $this->role?->name === 'provider';
    }

    public function isCustomer(): bool
    {
        return $this->role?->name === 'customer';
    }
}

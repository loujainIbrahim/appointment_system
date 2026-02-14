<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    protected $policies = [
        \App\Models\Appointment::class => \App\Policies\AppointmentPolicy::class,
    ];
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('manage-system', function ($user) {
            return $user->isAdmin();
        });

        Gate::define('manage-appointments', function ($user) {
            return $user->isProvider();
        });

        Gate::define('book-appointment', function ($user) {
            return $user->isCustomer();
        });
    }
}

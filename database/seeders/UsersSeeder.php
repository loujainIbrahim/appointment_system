<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Location;
use Illuminate\Support\Facades\Hash;
use App\Models\Service;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $adminRole = Role::where('name', 'admin')->first();
        $providerRole = Role::where('name', 'provider')->first();
        $customerRole = Role::where('name', 'customer')->first();

        $location = Location::first();

        User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'role_id' => $adminRole->id,
        ]);
        User::create([
            'name' => 'Provider User',
            'email' => 'provider@test.com',
            'password' => Hash::make('password'),
            'role_id' => $providerRole->id,
            'location_id' => $location->id,
        ]);

        User::create([
            'name' => 'Customer User',
            'email' => 'customer@test.com',
            'password' => Hash::make('password'),
            'role_id' => $customerRole->id,
        ]);
        $provider = User::where('email', 'provider@test.com')->first();
        $services = Service::all();

        foreach ($services as $service) {
            $provider->services()->attach($service->id, [
                'price' => rand(50, 150),
                'duration' => 30,
            ]);
        }
    }
}

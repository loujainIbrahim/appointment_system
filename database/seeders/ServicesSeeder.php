<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Service;
class ServicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            'Consultation',
            'Checkup',
            'Therapy Session',
        ];

        foreach ($services as $service) {
            Service::create(['name' => $service]);
        }
    }
}

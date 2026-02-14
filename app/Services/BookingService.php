<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Service as ServiceModel;
use App\Models\Availability;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookingService
{
    public  function create(array $data, $user): Appointment
    {
        return DB::transaction(function () use ($data, $user) {


            $service = ServiceModel::findOrFail($data['service_id']);
            $providerId = $data['provider_id'];

            $pivot = $service->providers()
                ->where('user_id', $providerId)
                ->firstOrFail()
                ->pivot;

            $start = Carbon::parse($data['date'] . ' ' . $data['start_time']);
            $end = $start->copy()->addMinutes($pivot->duration);

            $available = Availability::where('provider_id', $providerId)
                ->where('date', $data['date'])
                ->where('start_time', '<=', $start)
                ->where('end_time', '>=', $end)
                ->exists();

            if (!$available) {
                throw new \Exception('Provider not available');
            }

            $conflict = Appointment::where('provider_id', $providerId)
                ->where('date', $data['date'])
                ->where(function ($q) use ($start, $end) {
                    $q->whereBetween('start_time', [$start, $end])
                        ->orWhereBetween('end_time', [$start, $end]);
                })->exists();

            if ($conflict) {
                throw new \Exception('Time already booked');
            }

            return Appointment::create([
                'customer_id' => $user->id,
                'provider_id' => $providerId,
                'service_id' => $service->id,
                'date' => $data['date'],
                'start_time' => $start,
                'end_time' => $end,
                'price' => $pivot->price,
            ]);
        });
    }
}

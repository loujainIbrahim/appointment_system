<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Service as ServiceModel;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookingService
{
    public function create(array $data, $user): Appointment
    {
        return DB::transaction(function () use ($data, $user) {

            $provider = User::lockForUpdate()->findOrFail($data['provider_id']);

            $service = $provider->services()
                ->where('services.id', $data['service_id'])
                ->firstOrFail();

            $duration = $service->pivot->duration;

            $start = Carbon::parse($data['date'] . ' ' . $data['start_time']);
            $end = $start->copy()->addMinutes($duration);
            $availability = $provider->availabilities()
                ->whereDate('date', $data['date'])
                ->first();

            if ($availability) {

                if ($availability->is_available == false) {
                    throw new \Exception('Provider is on leave this day.');
                }

                if (
                    $availability->start_time &&
                    $availability->end_time &&
                    (
                        $start->format('H:i:s') < $availability->start_time ||
                        $end->format('H:i:s') > $availability->end_time
                    )
                ) {
                    throw new \Exception('Time outside special availability hours.');
                }
            } else {

                $schedule = $provider->schedules()
                    ->where('day_of_week', $start->dayOfWeek)
                    ->first();

                if (! $schedule) {
                    throw new \Exception('Provider does not work this day.');
                }

                if (
                    $start->format('H:i:s') < $schedule->start_time ||
                    $end->format('H:i:s') > $schedule->end_time
                ) {
                    throw new \Exception('Time outside working hours.');
                }
            }

            $conflict = Appointment::where('provider_id', $provider->id)
                ->whereDate('date', $data['date'])
                ->where(function ($q) use ($start, $end) {
                    $q->where('start_time', '<', $end->format('H:i:s'))
                        ->where('end_time', '>', $start->format('H:i:s'));
                })
                ->lockForUpdate()
                ->exists();

            if ($conflict) {
                throw new \Exception('Time already booked.');
            }

            return Appointment::create([
                'customer_id' => $user->id,
                'provider_id' => $provider->id,
                'service_id' => $service->id,
                'date' => $data['date'],
                'start_time' => $start->format('H:i:s'),
                'end_time' => $end->format('H:i:s'),
                'price' => $service->pivot->price,
            ]);
        });
    }
}

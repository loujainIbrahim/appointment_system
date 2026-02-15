<?php

namespace App\Services;

use App\Models\User;
use App\Models\Service;
use App\Models\Appointment;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class ProviderAvailabilityService
{
    /**
     * Return available dates for next X days
     */
    public function getAvailableDates(User $provider, int $daysAhead = 30): array
    {
        $today = Carbon::today();
        $endDate = $today->copy()->addDays($daysAhead);

        $period = CarbonPeriod::create($today, $endDate);

        $schedules = $provider->schedules()->get();

        $availableDates = [];

        foreach ($period as $date) {

            foreach ($schedules as $schedule) {

                if ($date->dayOfWeek == $schedule->day_of_week) {
                    $availableDates[] = $date->toDateString();
                }
            }
        }

        return array_values(array_unique($availableDates));
    }

    /**
     * Return available time slots for a specific date
     */
    public function getAvailableSlots(User $provider, string $date, int $serviceId): array
    {
        $date = Carbon::parse($date);

        $service = $provider->services()
            ->where('services.id', $serviceId)
            ->firstOrFail();

        $duration = $service->pivot->duration;

        $schedules = $provider->schedules()
            ->where('day_of_week', $date->dayOfWeek)
            ->get();

        if ($schedules->isEmpty()) {
            return [];
        }

        $slots = [];

        foreach ($schedules as $schedule) {

            $start = Carbon::parse($date->toDateString() . ' ' . $schedule->start_time);
            $end   = Carbon::parse($date->toDateString() . ' ' . $schedule->end_time);

            while ($start->copy()->addMinutes($duration) <= $end) {

                $slotStart = $start->copy();
                $slotEnd   = $start->copy()->addMinutes($duration);

                $isBooked = Appointment::where('provider_id', $provider->id)
                    ->whereDate('date', $date->toDateString())
                    ->where(function ($q) use ($slotStart, $slotEnd) {
                        $q->whereBetween('start_time', [$slotStart->format('H:i:s'), $slotEnd->format('H:i:s')])
                          ->orWhereBetween('end_time', [$slotStart->format('H:i:s'), $slotEnd->format('H:i:s')]);
                    })
                    ->exists();

                if (! $isBooked) {
                    $slots[] = $slotStart->format('H:i');
                }

                $start->addMinutes($duration);
            }
        }

        return $slots;
    }
}

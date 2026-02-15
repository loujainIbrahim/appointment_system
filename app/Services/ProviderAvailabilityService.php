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

            $hasSchedule = $schedules
                ->where('day_of_week', $date->dayOfWeek)
                ->isNotEmpty();

            if (! $hasSchedule) {
                continue;
            }

            $availability = $provider->availabilities()
                ->whereDate('date', $date->toDateString())
                ->first();

            if ($availability && $availability->is_available == false) {
                continue;
            }

            $availableDates[] = $date->toDateString();
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

        $availability = $provider->availabilities()
            ->whereDate('date', $date->toDateString())
            ->first();

        if ($availability && $availability->is_available == false) {
            return [];
        }

        if ($availability && $availability->start_time && $availability->end_time) {

            $workPeriods = [
                [
                    'start' => $availability->start_time,
                    'end'   => $availability->end_time
                ]
            ];
        } else {

            $schedules = $provider->schedules()
                ->where('day_of_week', $date->dayOfWeek)
                ->get();

            if ($schedules->isEmpty()) {
                return [];
            }

            $workPeriods = $schedules->map(function ($schedule) {
                return [
                    'start' => $schedule->start_time,
                    'end'   => $schedule->end_time
                ];
            })->toArray();
        }

        $slots = [];

        foreach ($workPeriods as $period) {

            $start = Carbon::parse($date->toDateString() . ' ' . $period['start']);
            $end   = Carbon::parse($date->toDateString() . ' ' . $period['end']);

            while ($start->copy()->addMinutes($duration) <= $end) {

                $slotStart = $start->copy();
                $slotEnd   = $start->copy()->addMinutes($duration);

                $isBooked = Appointment::where('provider_id', $provider->id)
                    ->whereDate('date', $date->toDateString())
                    ->where(function ($q) use ($slotStart, $slotEnd) {

                        $q->where(function ($query) use ($slotStart, $slotEnd) {
                            $query->where('start_time', '<', $slotEnd->format('H:i:s'))
                                ->where('end_time', '>', $slotStart->format('H:i:s'));
                        });
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

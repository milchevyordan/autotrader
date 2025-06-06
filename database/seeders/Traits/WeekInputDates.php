<?php

declare(strict_types=1);

namespace Database\Seeders\Traits;

use Carbon\Carbon;

trait WeekInputDates
{
    /**
     * Generate a random from and to weeks and return the first and last day of the week in ISO 8601 format.
     *
     * @return string an array with two dates: the start and end of the week
     */
    public function getWeekInputDates(): string
    {
        // Generate a random timestamp for the current time
        $randomTimestamp = mt_rand(0, time());
        $randomDate = Carbon::createFromTimestamp($randomTimestamp);

        // Get the start of the week (Sunday)
        $weekStart = $randomDate->copy()->startOfWeek(Carbon::SUNDAY);

        // Get the end of the week (Saturday)
        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SATURDAY);

        // Set time for the end of the day
        $weekEnd->setTime(23, 59, 59);

        $toWeekStart = $weekStart->copy()->addWeeks(mt_rand(1, 10));  // Adjust "4" as needed
        $toWeekEnd = $toWeekStart->copy()->endOfWeek(Carbon::SATURDAY)->setTime(23, 59, 59);

        // Create the structure with "from" and "to"
        $dates = [
            'from' => [$weekStart->toIso8601String(), $weekEnd->toIso8601String()],
            'to'   => [$toWeekStart->toIso8601String(), $toWeekEnd->toIso8601String()],
        ];

        // Return as JSON
        return json_encode($dates);
    }
}

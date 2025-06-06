<?php

declare(strict_types=1);

namespace Database\Seeders\Traits;

use DateTime;
use DateTimeInterface;
use DateTimeZone;
use RuntimeException;

trait FakerDateTimeTrait
{
    private const MAX_ATTEMPTS = 50;

    private const PROBLEMATIC_HOURS = [
        '02',
        '03',  // DST time
    ];

    /**
     * Generate a datetime avoiding problematic hours like '03:xx:xx'.
     *
     * @param  string            $startDate
     * @param  string            $endDate
     * @param  string            $dateTimezone
     * @param  int               $depth
     * @return DateTimeInterface
     */
    public function safeDateTimeBetween(
        string $startDate = '-2 years',
        string $endDate = 'now',
        string $dateTimezone = 'Europe/Sofia',
        int $depth = 0
    ): DateTimeInterface {
        if ($depth > self::MAX_ATTEMPTS) {
            throw new RuntimeException('Could not generate a valid datetime after self::MAX_ATTEMPTS attempts');
        }

        $datetime = $this->faker->dateTimeBetween($startDate, $endDate);
        $datetime->setTimezone(new DateTimeZone($dateTimezone));

        $hour = $datetime->format('h');

        if (in_array($hour, self::PROBLEMATIC_HOURS, true)) {
            // If the hour is 03, recursively call the function again
            return $this->safeDateTimeBetween($startDate, $endDate, $dateTimezone, $depth + 1);
        }

        // If the hour is not 03, return the valid datetime
        return $datetime;
    }
}

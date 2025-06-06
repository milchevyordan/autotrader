<?php

declare(strict_types=1);

namespace App\Support;

use DateMalformedStringException;
use DateTime;

class DateHelper
{
    /**
     * Autocomplete vehicle's expected_date_of_availability_from_supplier.
     *
     * @param  array                        $ranges
     * @param  null|int                     $weeksToAddFrom
     * @param  null|int                     $weeksToAddTo
     * @return array[]
     * @throws DateMalformedStringException
     */
    public static function adjustDateRanges(array $ranges, ?int $weeksToAddFrom, ?int $weeksToAddTo): array
    {
        $fromDate = null;
        if (isset($ranges['from'])) {
            $fromDate = [
                self::addWeeksToDate($ranges['from'][0], $weeksToAddFrom),
                self::addWeeksToDate($ranges['from'][1], $weeksToAddFrom),
            ];
        }

        $toDate = null;
        if (isset($ranges['to'])) {
            $toDate = [
                self::addWeeksToDate($ranges['to'][0], $weeksToAddTo),
                self::addWeeksToDate($ranges['to'][1], $weeksToAddTo),
            ];
        }

        return [
            'from' => $fromDate,
            'to'   => $toDate,
        ];
    }

    /**
     * Return modified date with added weeks.
     *
     * @param  string                       $date
     * @param  null|int                     $weeks
     * @return null|string
     * @throws DateMalformedStringException
     */
    private static function addWeeksToDate(string $date, ?int $weeks): ?string
    {
        if (! $weeks) {
            return $date;
        }

        return (new DateTime($date))->modify("+{$weeks} weeks")->format('Y-m-d\TH:i:s.v\Z');
    }
}

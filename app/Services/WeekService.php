<?php

declare(strict_types=1);

namespace App\Services;

use Carbon\Carbon;

class WeekService
{
    /**
     * @param  null|array $weekField
     * @return string
     */
    public static function generateWeekInputString(?array $weekField): string
    {
        if (empty($weekField) || (is_null($weekField['from']) && is_null($weekField['to']))) {
            return '';
        }

        $weekInputString = 'wk ';
        if (! is_null($weekField['from'])) {
            $startDateFrom = Carbon::parse($weekField['from'][1]);
            $weekInputString .= "{$startDateFrom->week()}/{$startDateFrom->year} ";
        }

        if (! is_null($weekField['to'])) {
            $startDateTo = Carbon::parse($weekField['to'][1]);
            $weekInputString .= "- {$startDateTo->week()}/{$startDateTo->year}";
        }

        return $weekInputString;
    }

    /**
     * @param  null|int $from
     * @param  null|int $to
     * @return string
     */
    public static function generateLeadtimeInputString(?int $from, ?int $to): string
    {
        $leadtimeInputString = '';
        if ($from) {
            $leadtimeInputString = __('From').' '.$from.' ';
        }

        if ($to) {
            $leadtimeInputString .= __('To').' '.$to;
        }

        return $leadtimeInputString;
    }
}

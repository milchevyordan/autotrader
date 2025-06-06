<?php

declare(strict_types=1);

namespace App\Rules;

use Carbon\Carbon;
use Closure;
use Exception;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidWeek implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_string($value)) {
            $value = json_decode($value, true);
        }

        // Check if $value is an array and contains the keys 'from' and 'to' (nullable)
        if (! is_array($value) || ! array_key_exists('from', $value) || ! array_key_exists('to', $value)) {
            $fail(__('The provided data must contain "from" and "to" keys.'));

            return;
        }

        // If 'from' is not null, validate its structure and content
        if (! is_null($value['from'])) {
            if (! is_array($value['from']) || count($value['from']) !== 2) {
                $fail(__('The "from" field must contain exactly two dates.'));

                return;
            }

            foreach ($value['from'] as $date) {
                if (! $this->isValidISO8601($date)) {
                    $fail(__('Invalid date provided in the "from" field.'));

                    return;
                }
            }

            $fromStart = Carbon::parse($value['from'][0]);
            $fromEnd = Carbon::parse($value['from'][1]);

            if ($fromEnd->lessThanOrEqualTo($fromStart)) {
                $fail(__('The "from" start date must be before the end date.'));

                return;
            }
        }

        // If 'to' is not null, validate its structure and content
        if (! is_null($value['to'])) {
            if (! is_array($value['to']) || count($value['to']) !== 2) {
                $fail(__('The "to" field must contain exactly two dates.'));

                return;
            }

            foreach ($value['to'] as $date) {
                if (! $this->isValidISO8601($date)) {
                    $fail(__('Invalid date provided in the "to" field.'));

                    return;
                }
            }

            $toStart = Carbon::parse($value['to'][0]);
            $toEnd = Carbon::parse($value['to'][1]);

            if ($toEnd->lessThanOrEqualTo($toStart)) {
                $fail(__('The "to" start date must be before the end date.'));

                return;
            }

            // Check if 'to' start date is not earlier than 'from' start date if 'from' is also provided
            if (! is_null($value['from']) && $toStart->lessThan($fromStart)) {
                $fail(__('The "from" date must be before the "to" date.'));

                return;
            }
        }
    }

    /**
     * Determine if the given date is a valid ISO 8601 datetime string.
     *
     * @param  string $date
     * @return bool
     */
    private function isValidISO8601(string $date): bool
    {
        try {
            Carbon::parse($date);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'The :attribute must be an array of exactly two valid ISO 8601 datetime strings, where the first date is before or equal to the second date.';
    }
}

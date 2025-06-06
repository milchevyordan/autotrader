<?php

declare(strict_types=1);

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Support\Str;

class StringHelper
{
    /**
     * Replace underscores with spaces.
     *
     * @param  null|string $string
     * @return null|string
     */
    public static function replaceUnderscores(?string $string): string|null
    {
        if ($string === null) {
            return null;
        }

        return str_replace('_', ' ', $string);
    }

    /**
     * Return email ending in jny from given name.
     *
     * @param         $name
     * @return string
     */
    public static function generateEmailFromName($name): string
    {
        $email = str_replace([' ', '-'], '.', strtolower($name));
        // Replace consecutive dots with a single dot
        $email = preg_replace('/\.{2,}/', '.', $email);
        // Replace ",." or ".,"
        $email = str_replace([',.', '.,'], '.', $email);
        // Trim leading and trailing dots
        $email = trim($email, '.');
        // Append the domain
        $email .= '@jny.nl';

        return $email;
    }

    /**
     * Return string representation of boolean.
     *
     * @param         $value
     * @return string
     */
    public static function booleanRepresentation($value): string
    {
        return $value ? __('Yes') : __('No');
    }

    /**
     * Return title case from given camel case
     * For example given 'openSalesOrders' returns 'Open Sales Orders'.
     *
     * @param  string $string
     * @return string
     */
    public static function camelCaseToTitle(string $string): string
    {
        return Str::title(Str::snake($string, ' '));
    }

    /**
     * Return week string in more readable format.
     *
     * @param         $weekString
     * @return string
     */
    public static function formatWeekString($weekString): string
    {
        if (! $weekString) {
            return '';
        }

        $date = Carbon::parse($weekString);

        // Extract the week number and year
        $weekNumber = $date->isoWeek();
        $year = $date->year;

        return "Week {$weekNumber}, {$year}";
    }

    /**
     * Convert a string to a numeric value.
     *
     * @param  string $value
     * @return float
     */
    public static function strToNum(string $value): float
    {
        return (float) str_replace(',', '', $value);
    }
}

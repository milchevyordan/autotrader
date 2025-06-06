<?php

declare(strict_types=1);

namespace App\Support;

class CurrencyHelper
{
    /**
     * Return for example 100,21 by given 10021.
     *
     * @param         $value
     * @return string
     */
    public static function convertUnitsToCurrency($value): string
    {
        if ($value === null) {
            return '';
        }

        return number_format($value / 100, 2, ',', ' ');
    }

    /**
     * Return for example 10021 by given 100,21.
     *
     * @param           $value
     * @return null|int
     */
    public static function convertCurrencyToUnits($value): null|int
    {
        if ($value === null) {
            return null;
        }

        return (int) preg_replace('/(?!^-)[^0-9]/', '', $value);
    }

    /**
     * Return for example - by given ''.
     *
     * @param         $value
     * @return string
     */
    public static function showMinusIfEmpty($value): string
    {
        if ($value === '') {
            return '-';
        }

        return '€ '.$value;
    }
}

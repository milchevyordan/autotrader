<?php

declare(strict_types=1);

namespace App\Support;

class EnumHelper
{
    /**
     * Return Enum name without underscores when given value.
     *
     * @param         $enum
     * @param         $value
     * @return string
     */
    public static function getEnumName($enum, $value): string
    {
        if (! $enum || ! $value) {
            return '';
        }

        $obj = $enum::getCaseByValue((int) $value);

        if (! $obj) {
            return '';
        }

        return str_replace('_', ' ', $obj->name);
    }
}

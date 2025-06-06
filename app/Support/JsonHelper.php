<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Collection;

class JsonHelper
{
    /**
     * Convert stored in database week json to usable array.
     *
     * @param             $value
     * @return null|array
     */
    public static function convertJsonToArray($value): ?array
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);

            if (json_last_error() === \JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        return null;
    }

    /**
     * Convert week array to json to be stored in database.
     *
     * @param         $value
     * @return string
     */
    public static function convertArrayToJson($value): string
    {
        return (new Collection($value))->toJson();
    }
}

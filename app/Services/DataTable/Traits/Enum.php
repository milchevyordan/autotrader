<?php

declare(strict_types=1);

namespace App\Services\DataTable\Traits;

use App\Support\StringHelper;

trait Enum
{
    public function translatedName(): string
    {
        return __(StringHelper::replaceUnderscores($this->name));
    }

    /**
     * Return enum's options count.
     *
     * @return int
     */
    public static function count(): int
    {
        return count(self::cases());
    }

    /**
     * Return array of enum's names.
     *
     * @return array
     */
    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }

    /**
     * Return array of enum's values.
     *
     * @return array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Return array of enum's values excluding the provided one
     *
     * @param $withoutCase
     * @return array
     */
    public static function valuesWithout($withoutCase): array
    {
        return array_column(
            array_filter(self::cases(), fn ($case) => $case !== $withoutCase),
            'value'
        );
    }

    /**
     * Return enum's case by the given value.
     *
     * @param  mixed       $value  the value to search for
     * @param  bool        $strict whether to use strict comparison
     * @return null|static the matching enum case or null if not found
     */
    public static function getCaseByValue(mixed $value, bool $strict = true): null|static
    {
        foreach (self::cases() as $case) {
            if ($strict ? $value === $case->value : $value == $case->value) {
                return $case;
            }
        }

        return null;
    }

    /**
     * Return enum's case by given name.
     *
     * @param  string      $name
     * @param  bool        $partialMatch
     * @return null|static
     */
    public static function getCaseByName(string $name, bool $partialMatch = false): null|static
    {
        foreach (self::cases() as $case) {
            if ($partialMatch && str_contains($case->name, $name)) {
                return $case;
            }
            if (! $partialMatch && $name === $case->name) {
                return $case;
            }
        }

        return null;
    }

    /**
     * Return enum cases by given name.
     *
     * @param  string $searchName
     * @param  bool   $partialMatch
     * @return array
     */
    public static function getCasesByName(string $searchName, bool $partialMatch = false): array
    {
        $cases = [];

        $caseName = strtolower($searchName);

        foreach (self::cases() as $case) {
            $enumCaseName = strtolower($case->name);

            if ($partialMatch && str_contains($enumCaseName, $caseName)) {
                $cases[] = $case;
            } elseif (! $partialMatch && $caseName === $enumCaseName) {
                $cases[] = $case;
            }
        }

        return $cases;
    }

    public static function toArray(): array
    {
        return array_reduce(self::cases(), fn ($carry, self $case) => $carry + [$case->name => $case->value], []);
    }

    /**
     * Convert php enums to ts enums.
     *
     * @return string
     */
    public static function toTS(): string
    {
        $class = str_replace('App\\Enums\\', '', static::class);
        $ts = "export enum {$class} {\n";

        foreach (self::cases() as $case) {
            $value = 'string' == gettype($case->value) ? "'{$case->value}'" : $case->value;
            $ts .= "    {$case->name} = {$value},\n";
        }

        $ts .= '}';

        return $ts;
    }
}

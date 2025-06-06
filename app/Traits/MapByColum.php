<?php

declare(strict_types=1);

namespace App\Traits;

trait MapByColum
{
    /**
     * Used in seeders to map resource by the key provided.
     *
     * @param        $key
     * @return array
     */
    public static function mapByColumn($key): array
    {
        $mappedArray = [];

        $data = self::select($key, 'id')
            ->get();

        foreach ($data as $row) {
            $keyId = $row->{$key};
            $valueId = $row->id;

            if (! isset($mappedArray[$keyId])) {
                $mappedArray[$keyId] = [];
            }

            $mappedArray[$keyId][] = $valueId;
        }

        return $mappedArray;
    }
}

<?php

declare(strict_types=1);

namespace App\Casts;

use App\Support\JsonHelper;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class Week implements CastsAttributes
{
    /**
     * Cast the attribute from the underlying model data to a native PHP data type.
     *
     * @param  Model      $model
     * @param  string     $key
     * @param  mixed      $value
     * @param  array      $attributes
     * @return null|array
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?array
    {
        return JsonHelper::convertJsonToArray($value);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  Model  $model
     * @param  string $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return string
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        return JsonHelper::convertArrayToJson($value);
    }
}

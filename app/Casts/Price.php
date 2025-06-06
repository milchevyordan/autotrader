<?php

declare(strict_types=1);

namespace App\Casts;

use App\Support\CurrencyHelper;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class Price implements CastsAttributes
{
    /**
     * Cast the attribute from the underlying model data to a native PHP data type.
     *
     * @param  Model  $model
     * @param  string $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return string
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): string
    {
        return CurrencyHelper::convertUnitsToCurrency($value);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  Model    $model
     * @param  string   $key
     * @param  mixed    $value
     * @param  array    $attributes
     * @return null|int
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): int|null
    {
        return CurrencyHelper::convertCurrencyToUnits($value);
    }
}

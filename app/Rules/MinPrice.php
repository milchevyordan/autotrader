<?php

declare(strict_types=1);

namespace App\Rules;

use App\Support\StringHelper;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class MinPrice implements ValidationRule
{
    /**
     * The minimum value allowed.
     *
     * @var int
     */
    protected int $minValue;

    /**
     * Construct validation rule with these params.
     *
     * @param int $minValue
     */
    public function __construct(int $minValue)
    {
        $this->minValue = $minValue;
    }

    /**
     * Run the validation rule.
     *
     * @param          $attribute
     * @param          $value
     * @param  Closure $fail
     * @return void
     */
    public function validate($attribute, $value, Closure $fail): void
    {
        $numericValue = StringHelper::strToNum($value);

        // Check if the numeric value is less than the minimum value
        if ($numericValue < $this->minValue) {
            $fail("The {$attribute} must be greater than or equal to {$this->minValue}.");
        }
    }
}

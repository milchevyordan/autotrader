<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class FieldRelativeToOther implements ValidationRule
{
    protected string $otherField;

    protected string $operator;

    protected string $type;

    /**
     * Construct validation rule with these params.
     *
     * @param string $otherField
     * @param string $operator
     * @param string $type
     */
    public function __construct(string $otherField, string $operator, string $type)
    {
        $this->otherField = $otherField;
        $this->operator = $operator;
        $this->type = $type;
    }

    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed   $value
     * @param  Closure $fail
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $otherValue = request()->input($this->otherField);

        if (! $otherValue) {
            return;
        }

        switch ($this->type) {
            case 'date':
                $valueReadyForCheck = strtotime($value);
                $otherValueReadyForCheck = strtotime($otherValue);

                break;
            case 'number':
                $valueReadyForCheck = $value;
                $otherValueReadyForCheck = $otherValue;

                break;
            default:
                return;
        }

        if (($this->operator === '<=' && $valueReadyForCheck > $otherValueReadyForCheck) ||
            ($this->operator === '>=' && $valueReadyForCheck < $otherValueReadyForCheck)) {
            $fail("The {$attribute} must be {$this->operator} than {$this->otherField}");
        }
    }
}

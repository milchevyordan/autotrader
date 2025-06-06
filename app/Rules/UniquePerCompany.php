<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UniquePerCompany implements ValidationRule
{
    /**
     * Model that we search unique for.
     *
     * @var string
     */
    protected string $model;

    /**
     * Field that we search unique for.
     *
     * @var string
     */
    protected string $field;

    /**
     * The id of the record that needs to be ignored (this record).
     *
     * @var null|int
     */
    protected ?int $ignoreId;

    /**
     * Construct validation rule with these params.
     *
     * @param string   $model
     * @param string   $field
     * @param null|int $ignoreId
     */
    public function __construct(string $model, string $field, ?int $ignoreId = null)
    {
        $this->model = $model;
        $this->field = $field;
        $this->ignoreId = $ignoreId;
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
        $instance = new $this->model();
        $query = $instance->newQuery()->inThisCompany()->where($this->field, $value);

        if ($this->ignoreId) {
            $query->where('id', '!=', $this->ignoreId);
        }

        if (method_exists($instance, 'scopeInThisCompany')) {
            $query->inThisCompany();
        }

        if ($query->count()) {
            $fail("The {$attribute} must be unique.");
        }
    }
}

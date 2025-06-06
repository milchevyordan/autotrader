<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\Locale;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQuoteInvitationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'quote_id'     => ['nullable', 'integer'],
            'customer_ids' => [
                'nullable',
                'array',
                'required_without:user_group_id',
                Rule::requiredIf(function () {
                    return ! request()->has('user_group_id');
                }),
            ],
            'user_group_id' => [
                'nullable',
                'integer',
                'required_without:customer_ids',
                Rule::requiredIf(function () {
                    return ! request()->has('customer_ids');
                }),
                'max:'.config('app.validation.rules.maxIntegerValue'),
            ],
            'locale' => ['required', Rule::in(Locale::values())],
        ];
    }
}

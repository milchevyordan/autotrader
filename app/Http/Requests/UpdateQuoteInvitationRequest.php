<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateQuoteInvitationRequest extends FormRequest
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
            'quote_id'      => ['required', 'integer', 'max:'.config('app.validation.rules.maxIntegerValue')],
            'customer_ids'  => ['array'],
            'user_group_id' => ['required', 'integer', 'max:'.config('app.validation.rules.maxIntegerValue')],
        ];
    }
}

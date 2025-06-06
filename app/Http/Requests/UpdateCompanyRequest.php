<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\Country;
use App\Enums\Currency;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCompanyRequest extends FormRequest
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
            'default_currency'       => ['required', Rule::in(Currency::values())],
            'country'                => ['required', Rule::in(Country::values())],
            'vat_percentage'         => ['nullable', 'integer', 'between:17,27'],
            'billing_contact_id'     => ['nullable', 'integer'],
            'logistics_contact_id'   => ['nullable', 'integer'],
            'name'                   => ['required', 'string', 'max:'.config('app.validation.rules.maxStringLength')],
            'postal_code'            => ['required', 'string', 'max:25'],
            'city'                   => ['required', 'string', 'max:'.config('app.validation.rules.maxStringLength')],
            'address'                => ['required', 'string', 'max:'.config('app.validation.rules.maxStringLength')],
            'vat_number'             => ['nullable', 'string', 'max:'.config('app.validation.rules.maxStringLength'), Rule::unique('companies')->ignore($this->company->id)],
            'email'                  => ['nullable', 'string', 'email', Rule::unique('companies')->ignore($this->company->id), 'max:'.config('app.validation.rules.maxStringLength')],
            'phone'                  => ['required', 'string', 'max:25'],
            'iban'                   => ['nullable', 'string', 'min:15', 'max:34', Rule::unique('companies')->ignore($this->company->id)],
            'swift_or_bic'           => ['nullable', 'string', 'min:8', 'max:11'],
            'bank_name'              => ['nullable', 'string', 'min:2'],
            'kvk_number'             => ['nullable', 'digits_between:8,255', Rule::unique('companies')->ignore($this->company->id)],
            'billing_remarks'        => ['nullable', 'string'],
            'logistics_times'        => ['nullable', 'string', 'max:'.config('app.validation.rules.maxStringLength')],
            'pdf_footer_text'        => ['nullable', 'string', 'max:'.config('app.validation.rules.maxStringLength')],
            'logistics_remarks'      => ['nullable', 'string'],
            'addresses'              => ['array'],
            'addresses.*.id'         => ['nullable'],
            'addresses.*.company_id' => ['sometimes', 'required_with:addresses', 'integer'],
            'addresses.*.type'       => ['sometimes', 'required_with:addresses', 'integer'],
            'addresses.*.address'    => ['sometimes', 'required_with:addresses', 'string', 'max:500'],
            'addresses.*.remarks'    => ['sometimes', 'nullable', 'string', 'max:'.config('app.validation.rules.maxStringLength')],
        ];
    }
}

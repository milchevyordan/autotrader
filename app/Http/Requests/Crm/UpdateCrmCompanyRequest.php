<?php

declare(strict_types=1);

namespace App\Http\Requests\Crm;

use App\Enums\CompanyType;
use App\Enums\Country;
use App\Enums\Currency;
use App\Enums\Locale;
use App\Enums\NationalEuOrWorldType;
use App\Rules\FileOrFileable;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCrmCompanyRequest extends FormRequest
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
            'company_group_id'                  => ['nullable', 'integer'],
            'main_user_id'                      => ['nullable', 'integer'],
            'billing_contact_id'                => ['nullable', 'integer'],
            'type'                              => ['required', Rule::in(CompanyType::valuesWithout(CompanyType::Base))],
            'logistics_contact_id'              => ['nullable', 'integer'],
            'default_currency'                  => ['required', Rule::in(Currency::values())],
            'country'                           => ['required', Rule::in(Country::values())],
            'purchase_type'                     => ['nullable', Rule::in(NationalEuOrWorldType::values())],
            'locale'                            => ['nullable', Rule::in(Locale::values())],
            'name'                              => ['required', 'string', 'max:'.config('app.validation.rules.maxStringLength')],
            'number'                            => ['nullable', 'string', 'max:'.config('app.validation.rules.maxStringLength')],
            'number_addition'                   => ['nullable', 'string', 'max:'.config('app.validation.rules.maxStringLength')],
            'postal_code'                       => ['required', 'string', 'max:25'],
            'city'                              => ['required', 'string', 'max:'.config('app.validation.rules.maxStringLength')],
            'address'                           => ['required', 'string', 'max:'.config('app.validation.rules.maxStringLength')],
            'street'                            => ['nullable', 'string', 'max:'.config('app.validation.rules.maxStringLength')],
            'address_number'                    => ['nullable', 'string', 'max:'.config('app.validation.rules.maxStringLength')],
            'address_number_addition'           => ['nullable', 'string', 'max:'.config('app.validation.rules.maxStringLength')],
            'province'                          => ['nullable', 'string', 'max:'.config('app.validation.rules.maxStringLength')],
            'vat_number'                        => ['nullable', 'string', 'max:'.config('app.validation.rules.maxStringLength'), Rule::unique('companies')->ignore($this->company->id)],
            'company_number_accounting_system'  => ['nullable', 'string', 'max:'.config('app.validation.rules.maxStringLength')],
            'debtor_number_accounting_system'   => ['nullable', 'string', 'max:'.config('app.validation.rules.maxStringLength')],
            'creditor_number_accounting_system' => ['nullable', 'string', 'max:'.config('app.validation.rules.maxStringLength')],
            'website'                           => ['nullable', 'string', 'max:550'],
            'email'                             => ['nullable', 'string', 'email', Rule::unique('companies')->ignore($this->company->id)],
            'phone'                             => ['nullable', 'string', 'max:25'],
            'iban'                              => ['nullable', 'string', 'min:15', 'max:34', Rule::unique('companies')->ignore($this->company->id)],
            'swift_or_bic'                      => ['nullable', 'string', 'min:8', 'max:11'],
            'bank_name'                         => ['nullable', 'string', 'min:2'],
            'kvk_number'                        => ['nullable', 'digits_between:8,255', Rule::unique('companies')->ignore($this->company->id)],
            'billing_remarks'                   => ['nullable', 'string'],
            'logistics_times'                   => ['nullable', 'string', 'max:'.config('app.validation.rules.maxStringLength')],
            'logistics_remarks'                 => ['nullable', 'string'],
            'kvk_expiry_date'                   => ['nullable', 'date'],
            'vat_expiry_date'                   => ['nullable', 'date'],
            'addresses'                         => ['array'],
            'addresses.*.id'                    => ['nullable'],
            'addresses.*.company_id'            => ['sometimes', 'required_with:addresses', 'integer'],
            'addresses.*.type'                  => ['sometimes', 'required_with:addresses', 'integer'],
            'addresses.*.address'               => ['sometimes', 'required_with:addresses', 'string', 'max:500'],
            'addresses.*.remarks'               => ['sometimes', 'nullable', 'string', 'max:'.config('app.validation.rules.maxStringLength')],
            'kvk_files.*'                       => [
                new FileOrFileable(),
            ],
            'vat_files.*' => [
                new FileOrFileable(),
            ],
        ];
    }
}

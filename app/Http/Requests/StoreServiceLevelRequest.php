<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\Damage;
use App\Enums\ImportOrOriginType;
use App\Enums\PaymentCondition;
use App\Enums\ServiceLevelType;
use App\Rules\MinPrice;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServiceLevelRequest extends FormRequest
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
            'name'      => ['required', 'string', 'max:'.config('app.validation.rules.maxStringLength')],
            'type'      => ['required', Rule::in(ServiceLevelType::values())],
            'companies' => [
                'nullable',
                'array',
                Rule::requiredIf(function () {
                    return $this->type === ServiceLevelType::Client->value;
                }),
            ],
            'type_of_sale'                           => ['required', Rule::in(ImportOrOriginType::values())],
            'transport_included'                     => ['required', 'boolean'],
            'damage'                                 => ['required', Rule::in(Damage::values())],
            'payment_condition'                      => ['required', Rule::in(PaymentCondition::values())],
            'payment_condition_free_text'            => ['nullable', 'string'],
            'discount'                               => ['nullable', 'string'],
            'discount_in_output'                     => ['required', 'boolean'],
            'rdw_company_number'                     => ['nullable', 'string'],
            'login_autotelex'                        => ['nullable', 'string'],
            'api_japie'                              => ['nullable', 'string'],
            'bidder_name_autobid'                    => ['nullable', 'string'],
            'bidder_number_autobid'                  => ['nullable', 'string'],
            'api_vwe'                                => ['nullable', 'string'],
            'items'                                  => ['array'],
            'items_in_output'                        => ['array'],
            'additional_services'                    => ['nullable', 'array'],
            'additional_services.*.id'               => ['sometimes', 'integer', 'nullable'],
            'additional_services.*.name'             => ['sometimes', 'required_with:additional_services', 'string', 'max:'.config('app.validation.rules.maxStringLength')],
            'additional_services.*.sale_price'       => ['nullable', 'string'],
            'additional_services.*.purchase_price'   => ['nullable', 'string', new MinPrice(0)],
            'additional_services.*.in_output'        => ['sometimes', 'required_with:additional_services', 'boolean'],
            'additional_services.*.is_service_level' => ['sometimes', 'required_with:additional_services', 'boolean'],
        ];
    }
}

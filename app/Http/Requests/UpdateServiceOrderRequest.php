<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\Damage;
use App\Enums\ImportOrOriginType;
use App\Enums\PaymentCondition;
use App\Rules\FileOrFileable;
use App\Rules\ImageOrImageable;
use App\Rules\MinPrice;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class UpdateServiceOrderRequest extends StoreServiceOrderRequest
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
            'owner_id'                               => ['required', 'integer'],
            'service_vehicle_id'                     => ['nullable', 'integer'],
            'customer_company_id'                    => ['required', 'integer'],
            'customer_id'                            => ['nullable', 'integer'],
            'seller_id'                              => ['required', 'integer'],
            'service_level_id'                       => ['nullable', 'integer'],
            'type_of_service'                        => ['required', Rule::in(ImportOrOriginType::values())],
            'payment_condition'                      => ['required', Rule::in(PaymentCondition::values())],
            'payment_condition_free_text'            => ['nullable', 'string'],
            'items'                                  => ['array'],
            'items.*.id'                             => ['sometimes', 'required_with:items', 'integer'],
            'items.*.sale_price'                     => ['sometimes', 'required_with:items', 'string'],
            'items.*.in_output'                      => ['sometimes', 'required_with:items', 'boolean', 'max:'.config('app.validation.rules.maxIntegerValue')],
            'items.*.shouldBeAdded'                  => ['sometimes', 'required_with:items', 'boolean'],
            'additional_services'                    => ['array'],
            'additional_services.*.id'               => ['sometimes', 'integer', 'nullable'],
            'additional_services.*.name'             => ['sometimes', 'required_with:additional_services', 'string', 'max:'.config('app.validation.rules.maxStringLength')],
            'additional_services.*.sale_price'       => ['nullable', 'string'],
            'additional_services.*.purchase_price'   => ['nullable', 'string', new MinPrice(0)],
            'additional_services.*.in_output'        => ['sometimes', 'required_with:additional_services', 'boolean'],
            'additional_services.*.is_service_level' => ['sometimes', 'required_with:additional_services', 'boolean'],
            'internal_remark_user_ids'               => ['nullable', 'array'],
            'internal_remark_role_ids'               => ['nullable', 'array'],
            'internal_remark'                        => ['nullable', 'required_with:internal_remark_user_ids,internal_remark_role_ids', 'string'],
            'images.*'                               => [
                new ImageOrImageable(),
            ],
            'files.*' => [
                new FileOrFileable(),
            ],
            'vehicleDocuments.*' => [
                new FileOrFileable(),
            ],
        ];
    }
}

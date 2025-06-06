<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\Currency;
use App\Enums\Damage;
use App\Enums\ImportOrOriginType;
use App\Enums\PaymentCondition;
use App\Rules\FileOrFileable;
use App\Rules\MinPrice;
use App\Rules\ValidWeek;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class UpdateSalesOrderRequest extends StoreSalesOrderRequest
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
            'reference'                              => ['nullable', 'string', 'max:'.config('app.validation.rules.maxStringLength')],
            'owner_id'                               => ['required', 'integer'],
            'customer_company_id'                    => ['nullable', 'integer'],
            'customer_id'                            => ['nullable', 'integer'],
            'seller_id'                              => ['nullable', 'integer'],
            'type_of_sale'                           => ['nullable', Rule::in(ImportOrOriginType::values())],
            'transport_included'                     => ['required', 'boolean'],
            'vat_deposit'                            => ['required', 'boolean'],
            'down_payment'                           => ['required', 'boolean'],
            'down_payment_amount'                    => ['required_if_accepted:down_payment', 'string', 'nullable'],
            'service_level_id'                       => ['nullable', 'integer'],
            'vat_percentage'                         => ['nullable', 'integer', 'between:17,27'],
            'total_vehicles_purchase_price'          => ['nullable', 'string', new MinPrice(0)],
            'total_costs'                            => ['nullable', 'string', new MinPrice(0)],
            'total_sales_price_service_items'        => ['nullable', 'string'],
            'total_sales_margin'                     => ['nullable', 'string', new MinPrice(0)],
            'total_fee_intermediate_supplier'        => ['nullable', 'string'],
            'total_sales_price_exclude_vat'          => ['nullable', 'string'],
            'total_sales_excl_vat_with_items'        => ['nullable', 'string'],
            'total_vat'                              => ['nullable', 'string'],
            'total_registration_fees'                => ['nullable', 'string'],
            'total_bpm'                              => ['nullable', 'string'],
            'total_sales_price_include_vat'          => ['nullable', 'string'],
            'total_sales_price'                      => ['nullable', 'string', new MinPrice(0)],
            'is_brutto'                              => ['required', 'boolean'],
            'calculation_on_sales_order'             => ['required', 'boolean'],
            'currency_rate'                          => ['nullable', 'numeric', 'min:0'],
            'delivery_week'                          => ['nullable', new ValidWeek()],
            'damage'                                 => ['nullable', Rule::in(Damage::values())],
            'payment_condition'                      => ['nullable', Rule::in(PaymentCondition::values())],
            'payment_condition_free_text'            => ['nullable', 'string'],
            'additional_info_conditions'             => ['nullable', 'string'],
            'discount'                               => ['nullable', 'string'],
            'discount_in_output'                     => ['required', 'boolean'],
            'currency'                               => ['nullable', Rule::in(Currency::values())],
            'vehicles'                               => ['array'],
            'vehicles.*.vehicle_id'                  => ['sometimes', 'required_with:vehicles', 'integer'],
            'vehicles.*.delivery_week'               => ['nullable', 'array', new ValidWeek()],
            'items'                                  => ['array'],
            'items.*.id'                             => ['sometimes', 'required_with:items', 'integer'],
            'items.*.sale_price'                     => ['nullable', 'string'],
            'items.*.in_output'                      => ['sometimes', 'required_with:items', 'boolean'],
            'items.*.shouldBeAdded'                  => ['sometimes', 'required_with:items', 'boolean'],
            'additional_services'                    => ['array'],
            'additional_services.*.id'               => ['sometimes', 'integer', 'nullable'],
            'additional_services.*.name'             => ['sometimes', 'required_with:additional_services', 'string', 'max:'.config('app.validation.rules.maxStringLength')],
            'additional_services.*.purchase_price'   => ['nullable', 'string', new MinPrice(0)],
            'additional_services.*.sale_price'       => ['nullable', 'string'],
            'additional_services.*.in_output'        => ['sometimes', 'required_with:additional_services', 'boolean'],
            'additional_services.*.is_service_level' => ['sometimes', 'required_with:additional_services', 'boolean'],
            'internal_remark_user_ids'               => ['nullable', 'array'],
            'internal_remark_role_ids'               => ['nullable', 'array'],
            'internal_remark'                        => ['nullable', 'required_with:internal_remark_user_ids,internal_remark_role_ids', 'string'],
            'files.*'                                => [
                new FileOrFileable(),
            ],
            'contractSignedFiles.*' => [
                new FileOrFileable(),
            ],
            'creditCheckFiles.*' => [
                new FileOrFileable(),
            ],
            'viesFiles.*' => [
                new FileOrFileable(),
            ],
        ];
    }
}

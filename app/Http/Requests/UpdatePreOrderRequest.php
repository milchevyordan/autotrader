<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\Currency;
use App\Enums\ImportEuOrWorldType;
use App\Enums\SupplierOrIntermediary;
use App\Rules\FileOrFileable;
use App\Rules\MinPrice;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class UpdatePreOrderRequest extends StorePreOrderRequest
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
            'owner_id'                         => ['required', 'integer'],
            'custom_reference'                 => ['nullable', 'string', 'max:'.config('app.validation.rules.maxStringLength')],
            'supplier_company_id'              => ['required', 'integer'],
            'supplier_id'                      => ['nullable', 'integer'],
            'intermediary_company_id'          => ['nullable', 'integer'],
            'intermediary_id'                  => ['nullable', 'integer'],
            'purchaser_id'                     => ['required', 'integer'],
            'pre_order_vehicle_id'             => ['nullable', 'integer'],
            'type'                             => ['required', Rule::in(ImportEuOrWorldType::values())],
            'transport_included'               => ['required', 'boolean'],
            'vat_deposit'                      => ['required', 'boolean'],
            'vat'                              => ['nullable', 'integer'],
            'amount_of_vehicles'               => ['required', 'integer', 'max:'.config('app.validation.rules.maxIntegerValue')],
            'down_payment'                     => ['required', 'boolean'],
            'down_payment_amount'              => ['required_if_accepted:down_payment', 'string', 'nullable'],
            'vat_percentage'                   => ['nullable', 'integer', 'between:17,27'],
            'total_purchase_price'             => ['nullable', 'string', new MinPrice(0)],
            'total_purchase_price_eur'         => ['nullable', 'string', new MinPrice(0)],
            'total_fee_intermediate_supplier'  => ['nullable', 'string'],
            'total_purchase_price_exclude_vat' => ['nullable', 'string', new MinPrice(0)],
            'total_vat'                        => ['nullable', 'string'],
            'total_bpm'                        => ['nullable', 'string'],
            'total_purchase_price_include_vat' => ['nullable', 'string', new MinPrice(0)],
            'currency_rate'                    => ['nullable', 'numeric', 'min:0'],
            'bpm'                              => ['nullable', 'integer', 'max:'.config('app.validation.rules.maxIntegerValue')],
            'currency_po'                      => ['required', Rule::in(Currency::values())],
            'document_from_type'               => ['required', Rule::in(SupplierOrIntermediary::values())],
            'contact_notes'                    => ['nullable', 'string'],
            'vehicleIds'                       => ['array'],
            'internal_remark_user_ids'         => ['nullable', 'array'],
            'internal_remark_role_ids'         => ['nullable', 'array'],
            'internal_remark'                  => ['nullable', 'required_with:internal_remark_user_ids,internal_remark_role_ids', 'string'],
            'files.*'                          => [
                new FileOrFileable(),
            ],
            'contractUnsignedFiles.*' => [
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

<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\TransportableType;
use App\Enums\TransportType;
use App\Rules\FieldRelativeToOther;
use App\Rules\FileOrFileable;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTransportOrderRequest extends FormRequest
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
            'owner_id'                            => ['required', 'integer'],
            'transport_company_id'                => ['required_if_accepted:transport_company_use', 'integer', 'nullable'],
            'transporter_id'                      => ['nullable', 'integer'],
            'vehicle_type'                        => ['required', Rule::in(TransportableType::values())],
            'transport_type'                      => ['required', Rule::in(TransportType::values())],
            'transport_company_use'               => ['required', 'boolean'],
            'pick_up_company_id'                  => ['nullable', 'integer'],
            'pick_up_location_id'                 => ['nullable', 'integer'],
            'pick_up_location_free_text'          => ['nullable', 'string', 'max:500'],
            'pick_up_notes'                       => ['nullable', 'string', 'max:'.config('app.validation.rules.maxStringLength')],
            'pick_up_after_date'                  => ['nullable', 'date', new FieldRelativeToOther('deliver_before_date', '<=', 'date')],
            'delivery_company_id'                 => ['nullable', 'integer'],
            'delivery_location_id'                => ['nullable', 'integer'],
            'delivery_location_free_text'         => ['nullable', 'string', 'max:500'],
            'delivery_notes'                      => ['nullable', 'string', 'max:'.config('app.validation.rules.maxStringLength')],
            'deliver_before_date'                 => ['nullable', 'date', new FieldRelativeToOther('pick_up_after_date', '>=', 'date')],
            'planned_delivery_date'               => ['nullable', 'date', new FieldRelativeToOther('pick_up_after_date', '>=', 'date'), new FieldRelativeToOther('deliver_before_date', '<=', 'date')],
            'total_transport_price'               => ['nullable', 'string'],
            'transportableIds'                    => ['array'],
            'transportables'                      => ['array'],
            'transportables.*.transportable_id'   => ['nullable', 'integer'],
            'transportables.*.location_id'        => ['nullable', 'integer'],
            'transportables.*.location_free_text' => ['nullable', 'string', 'max:500'],
            'transportables.*.price'              => ['nullable', 'string'],
            'internal_remark_user_ids'            => ['nullable', 'array'],
            'internal_remark_role_ids'            => ['nullable', 'array'],
            'internal_remark'                     => ['nullable', 'required_with:internal_remark_user_ids,internal_remark_role_ids', 'string'],
            'files.*'                             => [
                new FileOrFileable(),
            ],
            'transportInvoiceFiles.*' => [
                new FileOrFileable(),
            ], 'cmrWaybillFiles.*' => [
                new FileOrFileable(),
            ],
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\DocumentableType;
use App\Enums\DocumentLineType;
use App\Enums\PaymentCondition;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDocumentRequest extends FormRequest
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
            'owner_id'                    => ['required', 'integer'],
            'customer_company_id'         => ['required', 'integer'],
            'customer_id'                 => ['nullable', 'integer'],
            'payment_condition'           => ['required', Rule::in(PaymentCondition::values())],
            'payment_condition_free_text' => ['nullable', 'string'],
            'documentable_type'           => ['required', Rule::in(DocumentableType::values())],
            'paid_at'                     => ['nullable', 'date'],
            'number'                      => ['nullable', 'string', 'max:'.config('app.validation.rules.maxStringLength')],
            'notes'                       => ['nullable', 'string'],
            'total_price_exclude_vat'     => ['nullable', 'string'],
            'total_price_include_vat'     => ['nullable', 'string'],
            'total_vat'                   => ['nullable', 'string'],
            'date'                        => ['nullable', 'date'],
            'internal_remark_user_ids'    => ['nullable', 'array'],
            'internal_remark_role_ids'    => ['nullable', 'array'],
            'internal_remark'             => ['nullable', 'required_with:internal_remark_user_ids,internal_remark_role_ids', 'string'],
            'documentableIds'             => ['array'],
            'lines'                       => ['array'],
            'lines.*.id'                  => ['nullable', 'integer'],
            'lines.*.name'                => ['sometimes', 'required_with:lines', 'string', 'max:500'],
            'lines.*.documentable_id'     => ['nullable', 'integer'],
            'lines.*.type'                => ['sometimes', 'required_with:lines', Rule::in(DocumentLineType::values())],
            'lines.*.vat_percentage'      => ['nullable', 'integer', 'between:17,27'],
            'lines.*.price_exclude_vat'   => ['nullable', 'string'],
            'lines.*.vat'                 => ['nullable', 'string'],
            'lines.*.price_include_vat'   => ['sometimes', 'required_with:lines', 'string'],
            'lines.*.in_outputable_id'    => ['nullable', 'integer', 'max:'.config('app.validation.rules.maxIntegerValue')],
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\ItemType;
use App\Enums\UnitType;
use App\Rules\MinPrice;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateItemRequest extends FormRequest
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
            'unit_type'      => ['required', Rule::in(UnitType::values())],
            'type'           => ['required', Rule::in(ItemType::values())],
            'is_vat'         => ['required', 'boolean'],
            'vat_percentage' => ['nullable', 'integer', 'between:17,27'],
            'shortcode'      => ['required', 'string', 'max:'.config('app.validation.rules.maxStringLength')],
            'description'    => ['nullable', 'string', 'max:'.config('app.validation.rules.maxStringLength')],
            'purchase_price' => ['nullable', 'string', new MinPrice(0)],
            'sale_price'     => ['nullable', 'string'],
        ];
    }
}

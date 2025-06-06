<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSettingRequest extends FormRequest
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
            'costs_of_damages'   => ['nullable', 'string'],
            'transport_inbound'  => ['nullable', 'string'],
            'transport_outbound' => ['nullable', 'string'],
            'costs_of_taxation'  => ['nullable', 'string'],
            'recycling_fee'      => ['nullable', 'string'],
            'sales_margin'       => ['nullable', 'string'],
            'leges_vat'          => ['nullable', 'string'],
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreVehicleDuplicationRequest extends FormRequest
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
            'id'                      => ['required', 'integer'],
            'duplications'            => ['required', 'integer'],
            'first_registration_date' => ['required', 'boolean'],
            'kilometers'              => ['required', 'boolean'],
            'specific_exterior_color' => ['required', 'boolean'],
            'sales_price_total'       => ['required', 'boolean'],
            'option'                  => ['required', 'boolean'],
        ];
    }
}

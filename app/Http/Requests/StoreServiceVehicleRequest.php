<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\Co2Type;
use App\Enums\Country;
use App\Enums\VehicleType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServiceVehicleRequest extends FormRequest
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
            'owner_id'                => ['required', 'integer'],
            'vehicle_type'            => ['required', Rule::in(VehicleType::values())],
            'make_id'                 => ['required', 'integer'],
            'vehicle_model_id'        => ['required', 'integer'],
            'variant_id'              => ['nullable', 'integer'],
            'co2_type'                => ['required', Rule::in(Co2Type::values())],
            'co2_value'               => ['required', 'integer', 'max:'.config('app.validation.rules.maxIntegerValue')],
            'kilometers'              => ['required', 'integer', 'max:'.config('app.validation.rules.maxIntegerValue')],
            'nl_registration_number'  => ['nullable', 'string', 'max:'.config('app.validation.rules.maxStringLength')],
            'current_registration'    => ['required', Rule::in(Country::values())],
            'vin'                     => ['required', 'string', 'size:17'],
            'first_registration_date' => ['required', 'date'],
        ];
    }
}

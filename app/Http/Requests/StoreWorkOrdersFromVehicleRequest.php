<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\WorkOrderType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWorkOrdersFromVehicleRequest extends FormRequest
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
            'vehicleable_id' => ['required', 'integer'],
            'type'           => ['required', Rule::in(WorkOrderType::values())],
        ];
    }
}

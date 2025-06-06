<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\WorkOrderType;
use App\Rules\FileOrFileable;
use App\Rules\MinPrice;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWorkOrderRequest extends FormRequest
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
            'owner_id'                 => ['required', 'integer'],
            'total_price'              => ['nullable', 'string', new MinPrice(0)],
            'vehicleable_id'           => ['required', 'integer'],
            'type'                     => ['required', Rule::in(WorkOrderType::values())],
            'internal_remark_user_ids' => ['nullable', 'array'],
            'internal_remark_role_ids' => ['nullable', 'array'],
            'internal_remark'          => ['nullable', 'required_with:internal_remark_user_ids,internal_remark_role_ids', 'string'],
            'files.*'                  => ['sometimes', 'required', new FileOrFileable()],
        ];
    }
}

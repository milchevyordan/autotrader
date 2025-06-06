<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\WorkOrderTaskStatus;
use App\Enums\WorkOrderTaskType;
use App\Rules\FileOrFileable;
use App\Rules\ImageOrImageable;
use App\Rules\MinPrice;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWorkOrderTaskRequest extends FormRequest
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
            'assigned_to_id'  => ['nullable', 'integer'],
            'work_order_id'   => ['required', 'integer'],
            'name'            => ['required', 'string', 'max:'.config('app.validation.rules.maxStringLength')],
            'description'     => ['nullable', 'string', 'max:'.config('app.validation.rules.maxStringLength')],
            'type'            => ['required', Rule::in(WorkOrderTaskType::values())],
            'status'          => ['required', Rule::in(WorkOrderTaskStatus::values())],
            'estimated_price' => ['required', 'string', new MinPrice(0)],
            'actual_price'    => ['nullable', 'string', new MinPrice(0), 'required_if:status,'.WorkOrderTaskStatus::Completed->value],
            'planned_date'    => ['nullable', 'date'],
            'images.*'        => [
                new ImageOrImageable(),
            ],
            'files.*' => [
                new FileOrFileable(),
            ],
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWorkflowRequest extends FormRequest
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
            'workflow_process_class' => ['required', 'string'],
            'vehicleable_type'       => ['required', 'string'],
            'vehicleable_id'         => ['required', 'integer',
                Rule::unique('workflows')
                    ->where(function ($query) {
                        return $query->where('vehicleable_type', $this->input('vehicleable_type'));
                    }),
            ],
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Rules\FileOrFileable;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreWorkflowFinishedStepDateRequest extends FormRequest
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
            'workflow_id'      => ['required', 'integer'],
            'workflow_step_id' => ['required', 'integer'],
            'additional_value' => ['required'],
            'files.*'          => [
                new FileOrFileable(),
            ],
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Rules\FileOrFileable;
use App\Rules\ImageOrImageable;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreWorkflowFinishedStepRequest extends FormRequest
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
            'class_name'       => ['required', 'string'],
            'additional_value' => ['nullable', 'string'],
            'email_recipient'  => ['nullable', 'string'],
            'email_subject'    => ['nullable', 'string'],
            'finished_at'      => ['nullable', 'date'],
            'files.*'          => [
                new FileOrFileable(),
            ],
            'images.*' => [
                new ImageOrImageable(),
            ],
        ];
    }
}

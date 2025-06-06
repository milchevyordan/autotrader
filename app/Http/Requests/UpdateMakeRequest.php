<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Make;
use App\Rules\UniquePerCompany;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMakeRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'max:'.config('app.validation.rules.maxStringLength'),
                new UniquePerCompany(Make::class, 'name', $this->id),
            ],
        ];
    }
}

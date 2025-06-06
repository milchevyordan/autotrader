<?php

declare(strict_types=1);

namespace App\Http\Requests\Crm;

use App\Enums\Gender;
use App\Enums\Locale;

use App\Rules\FileOrFileable;
use App\Services\RoleService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCrmUserRequest extends FormRequest
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
        $crmRoleIds = RoleService::getCrmRoles()
            ->pluck('id')
            ->toArray();

        return [
            'prefix'              => ['nullable', 'string', 'max:'.config('app.validation.rules.maxStringLength')],
            'first_name'          => ['required', 'string', 'max:'.config('app.validation.rules.maxStringLength')],
            'last_name'           => ['required', 'string', 'max:'.config('app.validation.rules.maxStringLength')],
            'email'               => ['required', 'email', 'max:'.config('app.validation.rules.maxStringLength'), Rule::unique('users')->ignore($this->user->id)],
            'mobile'              => ['required', 'string', 'max:35'],
            'other_phone'         => ['nullable', 'string', 'max:35'],
            'gender'              => ['required', 'integer', Rule::in(Gender::values())],
            'company_id'          => ['required', 'integer'],
            'language'            => ['required', Rule::in(Locale::values())],
            'id_card_expiry_date' => ['nullable', 'date'],
            'id_card_files.*'     => [
                new FileOrFileable(),
            ],
            'roles' => [
                'required',
                'array',
                'in:'.implode(',', $crmRoleIds),
            ],
        ];
    }
}

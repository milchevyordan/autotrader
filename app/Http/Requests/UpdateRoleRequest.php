<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Spatie\Permission\Models\Permission;

class UpdateRoleRequest extends FormRequest
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
        $permissionIds = Permission::pluck('id')->toArray();

        return [
            'name'        => ['required', 'string', 'max:'.config('app.validation.rules.maxStringLength')],
            'permissions' => [
                'nullable',
                'array',
                'in:'.implode(',', $permissionIds),
            ],
        ];
    }
}

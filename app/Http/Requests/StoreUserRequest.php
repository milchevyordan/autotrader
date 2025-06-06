<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\Gender;
use App\Enums\Locale;
use App\Models\User;
use App\Services\RoleService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class StoreUserRequest extends FormRequest
{
    public bool $isRoot;

    /**
     * Create a new StoreUserRequest instance.
     */
    public function __construct()
    {
        parent::__construct();

        /**
         * @var User
         */
        $authUser = Auth::user();

        $this->isRoot = $authUser->hasRole('Root');
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return ! $this->isRoot || ! $this->companyHasAdministrator();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $allowedRoles = $this->isRoot ?
            Role::where('name', 'Company Administrator')->pluck('id')->all() :
            RoleService::getMainCompanyRoles()->pluck('id')->all();

        return [
            'company_id'  => [$this->isRoot ? 'required' : 'nullable'],
            'prefix'      => ['nullable', 'string', 'max:'.config('app.validation.rules.maxStringLength')],
            'first_name'  => ['required', 'string', 'max:'.config('app.validation.rules.maxStringLength')],
            'last_name'   => ['required', 'string', 'max:'.config('app.validation.rules.maxStringLength')],
            'email'       => ['required', 'string', 'max:'.config('app.validation.rules.maxStringLength'), 'unique:'.User::class],
            'mobile'      => ['required', 'string', 'max:25'],
            'other_phone' => ['nullable', 'string', 'max:25'],
            'gender'      => ['required', 'integer', Rule::in(Gender::values())],
            'language'    => ['required', Rule::in(Locale::values())],
            'roles'       => [
                'required',
                'array',
                'in:'.implode(',', $allowedRoles),
            ],
        ];
    }

    private function companyHasAdministrator()
    {
        return User::where('company_id', $this->company_id)->whereHas('roles', function ($roleQuery) {
            $roleQuery->where('name', 'Company Administrator');
        })->count();
    }
}

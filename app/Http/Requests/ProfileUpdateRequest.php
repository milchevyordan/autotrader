<?php

declare(strict_types=1);

/*
 *     This file is part of Test Software.
 *
 *     (c) Person Surname <test@gmail.com>
 *         Person2 Surname2 <test2@gmail.com>
 *
 *     Copyright 2000-2024, Company Ltd
 *     All rights reserved.
 */

namespace App\Http\Requests;

use App\Enums\Country;
use App\Enums\Gender;
use App\Enums\Locale;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'prefix'      => ['nullable', 'string', 'max:'.config('app.validation.rules.maxStringLength')],
            'first_name'  => ['required', 'string', 'max:'.config('app.validation.rules.maxStringLength')],
            'last_name'   => ['required', 'string', 'max:'.config('app.validation.rules.maxStringLength')],
            'email'       => ['required', 'email', 'max:'.config('app.validation.rules.maxStringLength'), Rule::unique('users')->ignore($this->user()->id)],
            'mobile'      => ['required', 'string', 'max:35'],
            'other_phone' => ['nullable', 'string', 'max:35'],
            'gender'      => ['required', 'integer', Rule::in(Gender::values())],
            'language'    => ['required', Rule::in(Locale::values())],
        ];
    }
}

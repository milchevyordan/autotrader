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

use App\Enums\Locale;
use App\Enums\TransportOrderFileType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GenerateTransportOrderFileRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'id'     => ['required', 'integer'],
            'type'   => ['required', Rule::in(TransportOrderFileType::values())],
            'locale' => ['required', Rule::in(Locale::values())],
        ];
    }
}

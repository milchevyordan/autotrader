<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Rules\ImageOrImageable;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanyLogoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('update-company-logo');

    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'logo.*'                                             => [new ImageOrImageable()],
            'pdf_signature_image.*'                              => [new ImageOrImageable()],
            'pdf_header_pre_purchase_sales_order_image.*'        => [new ImageOrImageable()],
            'pdf_header_documents_image.*'                       => [new ImageOrImageable()],
            'pdf_header_quote_transport_and_declaration_image.*' => [new ImageOrImageable()],
            'pdf_sticker_image.*'                                => [new ImageOrImageable()],
            'pdf_footer_image.*'                                 => [new ImageOrImageable()],
        ];
    }
}

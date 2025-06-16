<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\CacheTag;
use App\Enums\CompanyAddressType;
use App\Enums\CompanyType;
use App\Http\Requests\Crm\StoreCrmCompanyRequest;
use App\Http\Requests\Crm\UpdateCrmCompanyRequest;
use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyLogoRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Models\Company;
use App\Models\CompanyAddress;
use App\Models\User;
use App\Services\DataTable\DataTable;
use App\Services\Files\UploadHelper;
use App\Services\Images\Compressor\Compressor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class CompanyService
{
    public Company $company;

    /**
     * Create a new SystemVehicleService instance.
     */
    public function __construct()
    {
        $this->setCompany(new Company());
    }

    /**
     * Get the model of the company.
     *
     * @return Company
     */
    public function getCompany(): Company
    {
        return $this->company;
    }

    /**
     * Set the model of the company.
     *
     * @param  Company $company
     * @return self
     */
    public function setCompany(Company $company): self
    {
        $this->company = $company;

        return $this;
    }

    /**
     * @param  User $user
     * @return void
     */
    public function assignMainUserToCompany(Company $company, User $user)
    {

        $company->update(['main_user_id' => $user->id]);
    }

    /**
     * Return company's defaults in an array from company_id given in the request.
     *
     * @return null|array
     */
    public static function getCompanyDefaults(): array|null
    {
        $companyId = request()->input('company_id');

        if (! $companyId) {
            return null;
        }

        $company = Company::select(
            'purchase_type',
            'default_currency',
        )->find($companyId);

        return [
            'purchase_type'    => $company->purchase_type,
            'default_currency' => $company->default_currency,
        ];
    }

    /**
     * Get the value of crmCompanies.
     *
     * @param int|null $type
     * @return Collection
     */
    public static function getCrmCompanies(?int $type = null): Collection
    {
        return (new MultiSelectService(Company::whereIn('type', [$type, CompanyType::General->value])->inThisCompany()->orderBy('id', 'DESC')))->dataForSelect();
    }

    /**
     * Get the value of crmCompanies.
     *
     * @param  mixed      $companyId
     * @return Collection
     */
    public static function getCompanyLogisticsAddresses(int|string|null $companyId): Collection
    {
        if (! $companyId) {
            return collect();
        }

        return (new MultiSelectService(CompanyAddress::where('company_id', $companyId)?->where('type', CompanyAddressType::Logistics->value)))->setTextColumnName('address')->dataForSelect();
    }

    /**
     * Return datatable of Companies by provided builder.
     *
     * @param  Builder   $builder
     * @return DataTable
     */
    public static function getCompaniesDataTableByBuilder(Builder $builder): DataTable
    {
        return (new DataTable(
            $builder->select(array_merge(Company::$defaultSelectFields, ['main_user_id']))
        ))
            ->setRelation('mainUser', ['id', 'full_name'])
            ->setColumn('name', __('Name'), true, true)
            ->setColumn('email', __('Email'), true, true)
            ->setColumn('kvk_number', __('KVK'), true, true)
            ->setColumn('mainUser.full_name', __('Main Contact'), true, true);
    }

    /**
     * Create the company.
     *
     * @param StoreCompanyRequest|StoreCrmCompanyRequest $request
     * @param CompanyType|null $type
     * @return $this
     */
    public function createCompany(StoreCompanyRequest|StoreCrmCompanyRequest $request, ?CompanyType $type = null): self
    {
        $validatedRequest = $request->validated();

        /**
         * @var User $authUser
         */
        $authUser = Auth::user();

        $company = $this->getCompany();
        $company->fill($validatedRequest);
        $company->creator_id = $authUser->id;

        if ($type) {
            $company->type = $type;
        }

        $company
            ->saveWithFiles(UploadHelper::uploadMultipleFiles($validatedRequest, 'kvk_files'), 'kvk_files')
            ->saveWithFiles(UploadHelper::uploadMultipleFiles($validatedRequest, 'vat_files'), 'vat_files')
            ->save();

        $company->addresses()->createMany($validatedRequest['addresses'] ?? []);

        return $this;
    }

    /**
     * Update company.
     *
     * @param UpdateCompanyRequest|UpdateCrmCompanyRequest $request
     * @return $this
     */
    public function updateCompany(UpdateCompanyRequest|UpdateCrmCompanyRequest $request): static
    {
        $validatedRequest = $request->validated();

        $company = $this->getCompany();
        $changeLoggerService = new ChangeLoggerService($company, ['addresses']);
        $existingAddressIds = collect($validatedRequest['addresses'] ?? [])->pluck('id')->filter();

        $company->addresses()->whereNotIn('id', $existingAddressIds)->delete();

        $company->addresses()->upsert($validatedRequest['addresses'] ?? [], ['id'], ['company_id', 'type', 'address', 'remarks']);

        $company
            ->saveWithFiles(UploadHelper::uploadMultipleFiles($validatedRequest, 'kvk_files'), 'kvk_files')
            ->saveWithFiles(UploadHelper::uploadMultipleFiles($validatedRequest, 'vat_files'), 'vat_files')
            ->update($validatedRequest);

        $changeLoggerService->logChanges($company);
        Cache::forget($company->id.CacheTag::Currency->value);
        Cache::forget($company->id.CacheTag::Vat_percentage->value);

        return $this;
    }

    /**
     * Get the logo for the user's company.
     *
     * @param ?User $user
     * @return string
     */
    public static function getCompanyLogoForUser(?User $user): string
    {
        $imagePath = $user?->company?->image_path;

        return $imagePath
            ? '/storage/'.$imagePath
            : '/images/app-logo-v2.png';
    }

    /**
     * @param  string|null $defaultImagePath
     * @return string
     */
    public static function getPdfSignatureImage(?string $defaultImagePath = null): string
    {
        $user = Auth::user();

        $images = $user?->company->getGroupedImages(['pdf_signature_image']);
        $newPath = $images['pdf_signature_image']->first()?->path ?? null;

        return $newPath ? '/storage/'.$newPath : $defaultImagePath;
    }

    /**
     * Get the company Header image for Pre order, Purchase order, Sales order.
     *
     * @return ?string
     */
    public static function getPdfHeaderImageForPrePurchaseSalesOrder(?string $defaultImagePath = null): string
    {
        $user = Auth::user();

        $images = $user?->company->getGroupedImages(['pdf_header_pre_purchase_sales_order_image']);
        $newPath = $images['pdf_header_pre_purchase_sales_order_image']->first()?->path ?? null;

        return $newPath ? '/storage/'.$newPath : $defaultImagePath;

    }

    /**
     * Get the company image for the header of the Quote, Transport order and Declaration.
     *
     * @return ?string
     */
    public static function getPdfHeaderImageForQuoteTransportDeclaration(?string $defaultImagePath = null): string
    {
        $user = Auth::user();

        $images = $user?->company->getGroupedImages(['pdf_header_quote_transport_and_declaration_image']);
        $newPath = $images['pdf_header_quote_transport_and_declaration_image']->first()?->path ?? null;

        return $newPath ? '/storage/'.$newPath : $defaultImagePath;

    }

    /**
     * Get the header image used in the documents /Pro forma, Invoice, and Credit Invoice/.
     *
     * @return ?string
     */
    public static function getPdfHeaderImageForDocuments(?string $defaultImagePath = null): string
    {
        $user = Auth::user();

        $images = $user?->company->getGroupedImages(['pdf_header_documents_image']);
        $newPath = $images['pdf_header_documents_image']->first()?->path ?? null;

        return $newPath ? '/storage/'.$newPath : $defaultImagePath;

    }

    /**
     * Get the image that is in the sticker`s design
     *
     * @return ?string
     */
    public static function getPdfStickerImage(?string $defaultImagePath = null): string
    {
        $user = Auth::user();

        $images = $user?->company->getGroupedImages(['pdf_sticker_image']);
        $newPath = $images['pdf_sticker_image']->first()?->path ?? null;

        return $newPath ? '/storage/'.$newPath : $defaultImagePath;

    }

    /**
     * Get the company image shown in every page in PDF.
     *
     * @return ?string
     */
    public static function getPdfFooterImage(?string $defaultImagePath = null): string
    {
        $user = Auth::user();

        $images = $user?->company->getGroupedImages(['pdf_footer_image']);
        $newPath = $images['pdf_footer_image']->first()?->path ?? null;

        return $newPath ? '/storage/'.$newPath : $defaultImagePath;

    }

    /**
     * @return array<string>
     */
    public static function getPdfAssets(): array
    {
        $user = Auth::user();

        return Cache::tags([CacheTag::Company_logo->value, $user?->company_id])
            ->remember("pdf_assets_{$user?->id}", now()->addHours(), function () use ($user) {
                $defaultPdfImagePath = self::getCompanyLogoForUser($user);

                return [
                    'logo'                                             => self::getCompanyLogoForUser($user),
                    'pdf_signature_image'                              => self::getPdfSignatureImage($defaultPdfImagePath),
                    'pdf_header_pre_purchase_sales_order_image'        => self::getPdfHeaderImageForPrePurchaseSalesOrder($defaultPdfImagePath),
                    'pdf_header_documents_image'                       => self::getPdfHeaderImageForDocuments($defaultPdfImagePath),
                    'pdf_header_quote_transport_and_declaration_image' => self::getPdfHeaderImageForQuoteTransportDeclaration($defaultPdfImagePath),
                    'pdf_sticker_image'                                => self::getPdfStickerImage($defaultPdfImagePath),
                    'pdf_footer_image'                                 => self::getPdfFooterImage($defaultPdfImagePath),
                ];
            });
    }
}

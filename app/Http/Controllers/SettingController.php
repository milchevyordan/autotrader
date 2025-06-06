<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\CacheTag;
use App\Http\Requests\StoreSettingRequest;
use App\Http\Requests\UpdateCompanyLogoRequest;
use App\Http\Requests\UpdateSettingRequest;
use App\Models\Setting;
use App\Services\EmailTemplateService;
use App\Services\Files\UploadHelper;
use App\Services\Images\Compressor\Compressor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class SettingController extends Controller
{
    /**
     * Handle policy authorization.
     */
    public function __construct()
    {
        $this->authorizeResource(Setting::class);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(): Response
    {
        $user = Auth::user();

        return Inertia::render('settings/Index', [
            'userCompanyId'    => fn () => $user->company_id,
            'setting'          => fn () => $user->company->setting ?? null,
            'dataTable'        => fn () => $user->can('view-any-email-template') ? EmailTemplateService::getIndexMethodTable()->run() : collect(),
            'companyPdfAssets' => fn () => $user->company?->getGroupedImages(['logo', 'pdf_signature_image', 'pdf_header_pre_purchase_sales_order_image', 'pdf_header_documents_image', 'pdf_header_quote_transport_and_declaration_image', 'pdf_sticker_image', 'pdf_footer_image']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreSettingRequest $request
     * @return RedirectResponse
     */
    public function store(StoreSettingRequest $request): RedirectResponse
    {
        $setting = new Setting();
        $setting->fill($request->validated());
        $setting->company_id = auth()->user()->company_id;

        if ($setting->save()) {
            return redirect()->back()->with('success', __('The record has been successfully created.'));
        }

        return redirect()->back()->withErrors([__('Error creating record.')]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateSettingRequest $request
     * @param  Setting              $setting
     * @return RedirectResponse
     */
    public function update(UpdateSettingRequest $request, Setting $setting): RedirectResponse
    {
        if ($setting->update($request->validated())) {
            return redirect()->back()->with('success', __('The record has been successfully updated.'));
        }

        return redirect()->back()->withErrors([__('Error updating record.')]);
    }

    public function updateLogo(UpdateCompanyLogoRequest $request)
    {

        $user = Auth::user();

        $userCompany = $user?->company;

        if (! $userCompany) {
            abort(404, __('Could not find the company'));
        }

        $compressor = (new Compressor())->setResizedImageWidth(500);
        $userCompany
            ->saveWithImages(UploadHelper::uploadMultipleImages($request->validated(), 'logo', $compressor), 'logo')
            ->saveWithImages(UploadHelper::uploadMultipleImages($request->validated(), 'pdf_signature_image', $compressor), 'pdf_signature_image')
            ->saveWithImages(UploadHelper::uploadMultipleImages($request->validated(), 'pdf_header_pre_purchase_sales_order_image', $compressor), 'pdf_header_pre_purchase_sales_order_image')
            ->saveWithImages(UploadHelper::uploadMultipleImages($request->validated(), 'pdf_header_documents_image', $compressor), 'pdf_header_documents_image')
            ->saveWithImages(UploadHelper::uploadMultipleImages($request->validated(), 'pdf_header_quote_transport_and_declaration_image', $compressor), 'pdf_header_quote_transport_and_declaration_image')
            ->saveWithImages(UploadHelper::uploadMultipleImages($request->validated(), 'pdf_sticker_image', $compressor), 'pdf_sticker_image')
            ->saveWithImages(UploadHelper::uploadMultipleImages($request->validated(), 'pdf_footer_image', $compressor), 'pdf_footer_image');

        Cache::tags([CacheTag::Company_logo->value, $userCompany->id])->flush();
    }
}

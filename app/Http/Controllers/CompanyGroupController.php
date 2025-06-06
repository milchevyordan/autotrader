<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompanyGroupRequest;
use App\Http\Requests\UpdateCompanyGroupRequest;
use App\Models\CompanyGroup;
use App\Services\CompanyGroupService;
use App\Services\DataTable\Paginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class CompanyGroupController extends Controller
{
    private CompanyGroupService $service;

    /**
     * Handle policy authorization and service initialization.
     */
    public function __construct()
    {
        $this->authorizeResource(CompanyGroup::class);
        $this->service = new CompanyGroupService();
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(): Response
    {
        $expandGroupService = $this->service->getExpandGroupService();

        return Inertia::render('crm/company-groups/Index', [
            'paginator'     => new Paginator($expandGroupService->getExpandedGroupPaginator()),
            'companyGroups' => $expandGroupService->getExpandedGroupPaginator()->items(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreCompanyGroupRequest $request
     * @return RedirectResponse
     */
    public function store(StoreCompanyGroupRequest $request): RedirectResponse
    {
        $companyGroup = new CompanyGroup();
        $companyGroup->fill($request->validated());
        $companyGroup->creator_id = auth()->id();

        if ($companyGroup->save()) {
            return redirect()->back()->with('success', __('The record has been successfully created.'));
        }

        return redirect()->back()->withErrors([__('Error creating record.')]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateCompanyGroupRequest $request
     * @param  CompanyGroup              $companyGroup
     * @return RedirectResponse
     */
    public function update(UpdateCompanyGroupRequest $request, CompanyGroup $companyGroup): RedirectResponse
    {
        if ($companyGroup->update($request->validated())) {
            return back()->with('success', __('The record has been successfully updated.'));
        }

        return back()->withErrors([__('Error updating record.')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  CompanyGroup     $companyGroup
     * @return RedirectResponse
     */
    public function destroy(CompanyGroup $companyGroup): RedirectResponse
    {
        try {
            $companyGroup->delete();

            return redirect()->back()->with('success', __('The record has been successfully deleted.'));
        } catch (Throwable $th) {
            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error deleting record.')]);
        }
    }
}

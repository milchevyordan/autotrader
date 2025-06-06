<?php

declare(strict_types=1);

namespace App\Http\Controllers\Crm;

use App\Enums\CompanyType;
use App\Enums\Country;
use App\Http\Controllers\Controller;
use App\Http\Requests\Crm\StoreCrmCompanyRequest;
use App\Http\Requests\Crm\StoreCrmUserRequest;
use App\Http\Requests\Crm\UpdateCrmCompanyRequest;
use App\Models\Company;
use App\Models\CompanyGroup;

use App\Models\User;
use App\Services\CompanyService;
use App\Services\CrmUserService;
use App\Services\DataTable\DataTable;
use App\Services\MultiSelectService;
use App\Services\RoleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class CompanyController extends Controller
{
    private CompanyService $service;

    /**
     * Handle policy authorization.
     */
    public function __construct()
    {
        $this->service = new CompanyService();
        $this->authorizeResource(Company::class);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $dataTable = (new DataTable(
            Company::crmCompanies()->inThisCompany()
                ->select(array_merge(Company::$defaultSelectFields, ['main_user_id', 'address', 'postal_code', 'city', 'province', 'country']))
        ))
            ->setRelation('creator')
            ->setRelation('mainUser', ['id', 'full_name'])
            ->setColumn('action', __('Action'))
            ->setColumn('id', '#', true, true)
            ->setColumn('creator.full_name', __('Creator'), true)
            ->setColumn('type', __('type'), true, true)
            ->setColumn('name', __('Name'), true, true)
            ->setColumn('address', __('Address'), true)
            ->setColumn('postal_code', __('Post Code'), true, true)
            ->setColumn('city', __('City'), true, true)
            ->setColumn('province', __('Province'), true, true)
            ->setColumn('country', __('Country'), true, true)
            ->setColumn('email', __('Email'), true, true)
            ->setColumn('kvk_number', __('KVK'), true, true)
            ->setColumn('mainUser.full_name', __('Main Contact'), true, true)
            ->setEnumColumn('country', Country::class)
            ->setEnumColumn('type', CompanyType::class)
            ->run();

        return Inertia::render('crm/companies/Index', compact('dataTable'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create(): Response
    {
        return Inertia::render('crm/companies/Create', [
            'companyGroups' => fn () => (new MultiSelectService(CompanyGroup::inThisCompany()))->dataForSelect(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreCrmCompanyRequest $request
     * @return RedirectResponse
     */
    public function store(StoreCrmCompanyRequest $request): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $this->service->createCompany($request);

            DB::commit();

            return redirect()->route('crm.companies.edit', ['company' => $this->service->getCompany()->id])->with('success', __('The record has been successfully created.'));
        } catch (Throwable $th) {
            DB::rollBack();

            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error creating record.')]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Company $company
     * @param CrmUserService $crmUserService
     * @return Response
     */
    public function edit(Company $company, CrmUserService $crmUserService): Response
    {
        $company->load(['addresses', 'changeLogs', 'users']);

        $dataTable = $crmUserService->getUsersDataTableByCompany($company->id);

        return Inertia::render('crm/companies/Edit', [
            'dataTable'     => fn () => $dataTable->run(),
            'company'       => fn () => $company,
            'companyGroups' => fn () => (new MultiSelectService(CompanyGroup::inThisCompany()))->dataForSelect(),
            'users'         => fn () => User::getCrmCompanyUsersSelect($company->id),
            'companies'     => fn () => CompanyService::getCrmCompanies(),
            'crmRoles'      => fn () => (new MultiSelectService(RoleService::getCrmRoles()))->dataForSelect(),
            'files'         => fn () => $company->getGroupedFiles(['kvk_files', 'vat_files']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateCrmCompanyRequest $request
     * @param  Company                 $company
     * @return RedirectResponse
     */
    public function update(UpdateCrmCompanyRequest $request, Company $company): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $this->service->setCompany($company)->updateCompany($request);

            DB::commit();

            return back()->with('success', __('The record has been successfully updated.'));
        } catch (Throwable $th) {
            DB::rollBack();

            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error updating record.')]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Company          $company
     * @return RedirectResponse
     */
    public function destroy(Company $company): RedirectResponse
    {
        try {
            $company->delete();

            return redirect()->back()->with('success', __('The record has been successfully deleted.'));
        } catch (Throwable $th) {
            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error deleting record.')]);
        }
    }

    public function storeUser(StoreCrmUserRequest $request): RedirectResponse
    {

        $crmUserService = new CrmUserService();

        DB::beginTransaction();
        try {
            $crmUserService->createCrmUser($request);

            DB::commit();

            return back()->with('success', __('The record has been successfully created.'));
        } catch (Throwable $th) {
            DB::rollBack();

            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error creating record.')]);
        }
    }
}

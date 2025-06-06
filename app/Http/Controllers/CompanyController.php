<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\CacheTag;
use App\Enums\CompanyType;
use App\Http\Requests\Crm\StoreCrmUserRequest;
use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyLogoRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Models\Company;
use App\Models\User;
use App\Services\CompanyService;
use App\Services\DataTable\DataTable;
use App\Services\Files\UploadHelper;
use App\Services\Images\Compressor\Compressor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
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
     *
     * @return Response
     */
    public function index(): Response
    {
        $dataTable = (new DataTable(
            Company::baseCompanies()
                ->where('creator_id', auth()->id())
                ->select(Company::$defaultSelectFields)
        ))
            ->setRelation('creator')
            ->setColumn('action', __('Action'))
            ->setColumn('id', '#', true, true)
            ->setColumn('creator.full_name', __('Creator'), true)
            ->setColumn('name', __('Name'), true, true)
            ->setColumn('email', __('Email'), true, true)
            ->setColumn('kvk_number', __('KVK'), true, true)
            ->run();

        return Inertia::render('companies/Index', compact('dataTable'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create(): Response
    {
        return Inertia::render('companies/Create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreCompanyRequest $request
     * @return RedirectResponse
     */
    public function store(StoreCompanyRequest $request): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $this->service->createCompany($request, CompanyType::Base);
            DB::commit();

            return redirect()->route('companies.edit', ['company' => $this->service->getCompany()->id])->with('success', __('The record has been successfully created.'));
        } catch (Throwable $th) {
            DB::rollBack();
            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error creating record.')]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Company  $company
     * @return Response
     */
    public function edit(Company $company): Response
    {
        $company->load(['addresses', 'changeLogs']);

        return Inertia::render('companies/Edit', [
            'company' => $company,
            'users'   => fn () => User::getMainCompanyUsersSelect($company->id),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateCompanyRequest $request
     * @param  Company              $company
     * @return RedirectResponse
     */
    public function update(UpdateCompanyRequest $request, Company $company): RedirectResponse
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
}

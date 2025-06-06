<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\CompanyType;
use App\Http\Requests\StoreServiceLevelRequest;
use App\Http\Requests\UpdateServiceLevelRequest;
use App\Models\ServiceLevel;
use App\Services\CompanyService;
use App\Services\QuoteService;
use App\Services\SalesOrderService;
use App\Services\ServiceLevelService;
use App\Services\ServiceOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class ServiceLevelController extends Controller
{
    public ServiceLevelService $service;

    /**
     * Handle policy authorization.
     */
    public function __construct()
    {
        $this->authorizeResource(ServiceLevel::class);
        $this->service = new ServiceLevelService();
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(): Response
    {
        $dataTable = $this->service->getIndexMethodDataTable()->run();

        return Inertia::render('service-levels/Index', compact('dataTable'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('service-levels/Create', [
            'dataTable'    => fn () => $this->service->getItemsDataTable(),
            'crmCompanies' => fn () => CompanyService::getCrmCompanies(CompanyType::Supplier->value),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreServiceLevelRequest $request
     * @return RedirectResponse
     */
    public function store(StoreServiceLevelRequest $request): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $this->service->createServiceLevel($request);

            DB::commit();

            return redirect()
                ->route('service-levels.edit', ['service_level' => $this->service->getServiceLevel()->id])
                ->with('success', __('The record has been successfully created.'));
        } catch (Throwable $th) {
            DB::rollBack();

            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error creating record.')]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  ServiceLevel $serviceLevel
     * @return Response
     */
    public function show(ServiceLevel $serviceLevel): Response
    {
        $serviceLevel->loadCount(['salesOrders', 'serviceOrders', 'quotes']);

        $this->service->setServiceLevel($serviceLevel);

        return Inertia::render('service-levels/Show', [
            'serviceLevel'  => $serviceLevel,
            'turnoverData'  => $this->service->generateTurnoverData(),
            'salesOrders'   => Inertia::lazy(fn () => SalesOrderService::getSalesOrdersDataTableByBuilder($serviceLevel->salesOrders()->getQuery(), true)->run()),
            'serviceOrders' => Inertia::lazy(fn () => ServiceOrderService::getServiceOrdersDataTableByBuilder($serviceLevel->serviceOrders()->getQuery(), true)->run()),
            'quotes'        => Inertia::lazy(fn () => QuoteService::getQuotesDataTableByBuilder($serviceLevel->quotes()->getQuery(), true)->run()),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  ServiceLevel $serviceLevel
     * @return Response
     */
    public function edit(ServiceLevel $serviceLevel): Response
    {
        $serviceLevel->load([
            'items',
            'additionalServices',
            'companies:id,name',
            'changeLogs',
        ]);

        $this->service->setServiceLevel($serviceLevel);

        return Inertia::render('service-levels/Edit', [
            'serviceLevel'    => fn () => $serviceLevel,
            'selectedItemIds' => fn () => $this->service->getSelectedItemIds(),
            'dataTable'       => fn () => $this->service->getItemsDataTable(),
            'crmCompanies'    => fn () => CompanyService::getCrmCompanies(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateServiceLevelRequest $request
     * @param  ServiceLevel              $serviceLevel
     * @return RedirectResponse
     */
    public function update(UpdateServiceLevelRequest $request, ServiceLevel $serviceLevel): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $this->service
                ->setServiceLevel($serviceLevel)
                ->updateServiceLevel($request);

            DB::commit();

            return redirect()
                ->route('service-levels.edit', ['service_level' => $this->service->getServiceLevel()->id])
                ->with('success', __('The record has been successfully updated.'));
        } catch (Throwable $th) {
            DB::rollBack();

            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error updating record.')]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  ServiceLevel     $serviceLevel
     * @return RedirectResponse
     */
    public function destroy(ServiceLevel $serviceLevel): RedirectResponse
    {
        try {
            $serviceLevel->delete();

            return redirect()->back()->with('success', __('The record has been successfully deleted.'));
        } catch (Throwable $th) {
            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error deleting record.')]);
        }
    }
}

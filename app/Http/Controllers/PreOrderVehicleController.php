<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\CompanyType;
use App\Http\Requests\StorePreOrderVehicleRequest;
use App\Http\Requests\UpdatePreOrderVehicleRequest;
use App\Models\PreOrderVehicle;

use App\Services\BpmService;
use App\Services\CompanyService;
use App\Services\EngineService;
use App\Services\MakeService;
use App\Services\MultiSelectService;
use App\Services\RoleService;
use App\Services\UserService;
use App\Services\VariantService;
use App\Services\VehicleModelService;
use App\Services\Vehicles\PreOrderVehicleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class PreOrderVehicleController extends Controller
{
    private PreOrderVehicleService $service;

    /**
     * Handle policy authorization and service initialization.
     */
    public function __construct()
    {
        $this->authorizeResource(PreOrderVehicle::class);
        $this->service = new PreOrderVehicleService();
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(): Response
    {
        return Inertia::render('pre-order-vehicles/Index', [
            'dataTable' => $this->service->getIndexMethodTable()->run(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create(): Response
    {
        return Inertia::render('pre-order-vehicles/Create', [
            'userCompany'       => fn () => auth()->user()->company,
            'vehicleDefaults'   => fn () => auth()->user()->company->setting ?? null,
            'supplierCompanies' => fn () => CompanyService::getCrmCompanies(CompanyType::Supplier->value),
            'suppliers'         => Inertia::lazy(fn () => UserService::getSuppliers()),
            'make'              => fn () => MakeService::getMakes(),
            'mainCompanyRoles'  => Inertia::lazy(fn () => (new MultiSelectService(RoleService::getMainCompanyRoles()))->dataForSelect()),
            'mainCompanyUsers'  => Inertia::lazy(fn () => (new MultiSelectService(RoleService::getCompanyMainRolesUsers()))->setTextColumnName('full_name')->dataForSelect()),
            'vehicleModel'      => Inertia::lazy(fn () => VehicleModelService::getVehicleModels()),
            'variant'           => Inertia::lazy(fn () => VariantService::getVariants()),
            'engine'            => Inertia::lazy(fn () => EngineService::getEngines()),
            'bpmValues'         => Inertia::lazy(fn () => BpmService::getValues()),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StorePreOrderVehicleRequest $request
     * @return RedirectResponse
     */
    public function store(StorePreOrderVehicleRequest $request): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $this->service->createPreOrderVehicle($request);

            DB::commit();

            return redirect()->route('pre-order-vehicles.edit', ['pre_order_vehicle' => $this->service->getPreOrderVehicle()->id])
                ->with('success', __('The record has been successfully created.'));
        } catch (Throwable $th) {
            DB::rollBack();

            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error creating record.')]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  PreOrderVehicle $preOrderVehicle
     * @return Response
     */
    public function edit(PreOrderVehicle $preOrderVehicle): Response
    {
        $preOrderVehicle->load([
            'creator', 'calculation',
            'preOrder' => function ($query) {
                $query->withTrashed()->select('id', 'pre_order_vehicle_id', 'deleted_at');
            },
            'documents' => function ($query) {
                $query->withTrashed()->select('id', 'deleted_at');
            },
            'transportOrders' => function ($query) {
                $query->withTrashed()->select('id', 'transport_type', 'deleted_at');
            },
            'supplier:id,company_id',
            'internalRemarks',
        ]);

        $this->service->setPreOrderVehicle($preOrderVehicle);

        return Inertia::render('pre-order-vehicles/Edit', [
            'preOrderVehicle'   => $preOrderVehicle,
            'userCompany'       => auth()->user()->company,
            'vehicleDefaults'   => auth()->user()->company->setting ?? null,
            'supplierCompanies' => fn () => CompanyService::getCrmCompanies(CompanyType::Supplier->value),
            'suppliers'         => fn () => UserService::getSuppliers($preOrderVehicle->supplier_company_id),
            'make'              => fn () => MakeService::getMakes(),
            'vehicleModel'      => fn () => VehicleModelService::getVehicleModels($preOrderVehicle->make_id),
            'variant'           => fn () => VariantService::getVariants($preOrderVehicle->make_id),
            'engine'            => fn () => EngineService::getEngines($preOrderVehicle->make_id, $preOrderVehicle->fuel->value),
            'images'            => fn () => $preOrderVehicle->getGroupedImages(['internalImages', 'externalImages']),
            'files'             => fn () => $preOrderVehicle->getGroupedFiles(['internalFiles', 'externalFiles']),
            'bpmValues'         => Inertia::lazy(fn () => BpmService::getValues()),
            'mainCompanyRoles'  => Inertia::lazy(fn () => (new MultiSelectService(RoleService::getMainCompanyRoles()))->dataForSelect()),
            'mainCompanyUsers'  => Inertia::lazy(fn () => (new MultiSelectService(RoleService::getCompanyMainRolesUsers()))->setTextColumnName('full_name')->dataForSelect()),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdatePreOrderVehicleRequest $request
     * @param  PreOrderVehicle              $preOrderVehicle
     * @return RedirectResponse
     */
    public function update(UpdatePreOrderVehicleRequest $request, PreOrderVehicle $preOrderVehicle): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $this->service
                ->setPreOrderVehicle($preOrderVehicle)
                ->updatePreOrderVehicle($request);

            DB::commit();

            return redirect()->route('pre-order-vehicles.edit', ['pre_order_vehicle' => $preOrderVehicle->id])->with('success', __('The record has been successfully updated.'));
        } catch (Throwable $th) {
            DB::rollBack();

            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error updating record.')]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  PreOrderVehicle  $preOrderVehicle
     * @return RedirectResponse
     */
    public function destroy(PreOrderVehicle $preOrderVehicle): RedirectResponse
    {
        try {
            $preOrderVehicle->delete();

            return redirect()->back()->with('success', __('The record has been successfully deleted.'));
        } catch (Throwable $th) {
            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error deleting record.')]);
        }
    }
}

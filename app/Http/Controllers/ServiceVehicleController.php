<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceVehicleRequest;
use App\Http\Requests\UpdateServiceVehicleRequest;
use App\Models\ServiceVehicle;
use App\Services\MakeService;
use App\Services\MultiSelectService;
use App\Services\OwnershipService;
use App\Services\RoleService;
use App\Services\VariantService;
use App\Services\VehicleModelService;
use App\Services\Vehicles\ServiceVehicleService;
use App\Services\Workflow\ProcessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class ServiceVehicleController extends Controller
{
    public ServiceVehicleService $service;

    /**
     * Handle policy authorization and service initialization.
     */
    public function __construct()
    {
        $this->authorizeResource(ServiceVehicle::class);
        $this->service = new ServiceVehicleService();
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        return Inertia::render('service-vehicles/Index', [
            'dataTable'         => fn () => $this->service->getIndexMethodTable()->run(),
            'workflowProcesses' => fn () => MultiSelectService::dataForSelectFromArray(ProcessService::getCompanyProcesses()->toArray(), 'className'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('service-vehicles/Create', [
            'makes'            => fn () => MakeService::getMakes(),
            'vehicleModels'    => Inertia::lazy(fn () => VehicleModelService::getVehicleModels()),
            'variants'         => Inertia::lazy(fn () => VariantService::getVariants()),
            'mainCompanyUsers' => fn () => (new MultiSelectService(RoleService::getCompanyMainRolesUsers()))->setTextColumnName('full_name')->dataForSelect(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreServiceVehicleRequest $request
     * @return RedirectResponse
     */
    public function store(StoreServiceVehicleRequest $request): RedirectResponse
    {
        $serviceVehicle = new ServiceVehicle();
        $serviceVehicle->fill($request->validated());
        $serviceVehicle->creator_id = auth()->id();

        if ($serviceVehicle->save()) {
            return redirect()->route('service-vehicles.edit', ['service_vehicle' => $serviceVehicle->id])
                ->with('success', __('The record has been successfully created.'));
        }

        return redirect()->back()->withErrors([__('Error creating record.')]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  ServiceVehicle $serviceVehicle
     * @return Response
     */
    public function edit(ServiceVehicle $serviceVehicle): Response
    {
        $serviceVehicle->load([
            'make:id,name', 'vehicleModel:id,name', 'creator',
            'ownerships.user:id,full_name',
            'serviceOrder' => function ($query) {
                $query->withTrashed()->select('id', 'service_vehicle_id', 'deleted_at');
            },
            'transportOrders' => function ($query) {
                $query->withTrashed()->select('id', 'deleted_at');
            },
            'workflow:id,vehicleable_id,vehicleable_type',
            'workOrder' => function ($query) {
                $query->withTrashed()->select('id', 'vehicleable_id', 'vehicleable_type', 'deleted_at');
            },
            'documents' => function ($query) {
                $query->withTrashed()->select('id', 'deleted_at');
            },
        ]);

        return Inertia::render('service-vehicles/Edit', [
            'makes'             => fn () => MakeService::getMakes(),
            'vehicleModels'     => fn () => VehicleModelService::getVehicleModels($serviceVehicle->make_id),
            'variants'          => fn () => VariantService::getVariants($serviceVehicle->make_id),
            'serviceVehicle'    => fn () => $serviceVehicle,
            'pendingOwnerships' => fn () => OwnershipService::getPending($serviceVehicle),
            'acceptedOwnership' => fn () => OwnershipService::getAccepted($serviceVehicle),
            'mainCompanyUsers'  => fn () => (new MultiSelectService(RoleService::getCompanyMainRolesUsers()))->setTextColumnName('full_name')->dataForSelect(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateServiceVehicleRequest $request
     * @param  ServiceVehicle              $serviceVehicle
     * @return RedirectResponse
     */
    public function update(UpdateServiceVehicleRequest $request, ServiceVehicle $serviceVehicle): RedirectResponse
    {
        if ($serviceVehicle->update($request->validated())) {
            return redirect()->back()->with('success', __('The record has been successfully updated.'));
        }

        return redirect()->back()->withErrors([__('Error updating record.')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  ServiceVehicle $serviceVehicle
     * @return RedirectResponse
     */
    public function destroy(ServiceVehicle $serviceVehicle): RedirectResponse
    {
        try {
            $serviceVehicle->delete();

            return redirect()->back()->with('success', __('The record has been successfully deleted.'));
        } catch (Throwable $th) {
            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error deleting record.')]);
        }
    }
}

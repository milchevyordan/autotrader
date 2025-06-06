<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\CompanyType;
use App\Exceptions\VehicleException;
use App\Http\Requests\IdLocaleRequest;
use App\Http\Requests\StoreVehicleDuplicationRequest;
use App\Http\Requests\StoreVehicleRequest;
use App\Http\Requests\UpdateVehicleRequest;

use App\Models\SalesOrder;
use App\Models\Vehicle;
use App\Models\VehicleGroup;
use App\Models\WorkOrder;
use App\Services\BpmService;
use App\Services\CompanyService;
use App\Services\EngineService;
use App\Services\MakeService;
use App\Services\MultiSelectService;
use App\Services\OwnershipService;
use App\Services\RoleService;
use App\Services\UserService;
use App\Services\VariantService;
use App\Services\VehicleModelService;
use App\Services\Vehicles\SystemVehicleService;
use App\Services\Workflow\ProcessService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class VehicleController extends Controller
{
    public SystemVehicleService $service;

    /**
     * Handle policy authorization and service initialization.
     */
    public function __construct()
    {
        $this->authorizeResource(Vehicle::class);
        $this->service = new SystemVehicleService();
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(): Response
    {
        $relationsToLoad = ['calculation' => ['vehicleable_type', 'vehicleable_id', 'net_purchase_price', 'total_purchase_price', 'fee_intermediate', 'sales_price_net', 'sales_price_total', 'leges_vat', 'transport_inbound', 'transport_outbound', 'costs_of_damages', 'vat', 'bpm', 'sales_price_incl_vat_or_margin']];

        return Inertia::render('vehicles/Index', [
            'dataTable'            => fn () => $this->service->getIndexMethodTable($relationsToLoad)->run(),
            'workflowProcesses'    => fn () => MultiSelectService::dataForSelectFromArray(ProcessService::getCompanyProcesses()->toArray(), 'className'),
            'vehicleTimelineItems' => Inertia::lazy(fn () => $this->service->getVehicleTimelineItems((int) request(null)->input('vehicle_id'))),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('vehicles/Create', [
            'userCompany'       => fn () => auth()->user()->company,
            'vehicleDefaults'   => fn () => auth()->user()->company->setting ?? null,
            'supplierCompanies' => fn () => CompanyService::getCrmCompanies(CompanyType::Supplier->value),
            'suppliers'         => Inertia::lazy(fn () => UserService::getSuppliers()),
            'vehicleGroup'      => fn () => (new MultiSelectService(VehicleGroup::inThisCompany()))->dataForSelect(),
            'make'              => fn () => MakeService::getMakes(),
            'mainCompanyRoles'  => Inertia::lazy(fn () => (new MultiSelectService(RoleService::getMainCompanyRoles()))->dataForSelect()),
            'mainCompanyUsers'  => fn () => (new MultiSelectService(RoleService::getCompanyMainRolesUsers()))->setTextColumnName('full_name')->dataForSelect(),
            'vehicleModel'      => Inertia::lazy(fn () => VehicleModelService::getVehicleModels()),
            'variant'           => Inertia::lazy(fn () => VariantService::getVariants()),
            'engine'            => Inertia::lazy(fn () => EngineService::getEngines()),
            'bpmValues'         => Inertia::lazy(fn () => BpmService::getValues()),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreVehicleRequest $request
     * @return RedirectResponse
     * @throws Throwable
     */
    public function store(StoreVehicleRequest $request): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $this->service->createSystemVehicle($request);

            DB::commit();

            return redirect()->route('vehicles.edit', ['vehicle' => $this->service->getVehicle()->id])
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
     * @param  Vehicle  $vehicle
     * @return Response
     */
    public function edit(Vehicle $vehicle): Response
    {
        $vehicle->load([
            'creator', 'calculation',
            'ownerships.user:id,full_name',
            'preOrder' => function ($query) {
                $query->withTrashed()->select('id', 'deleted_at');
            },
            'purchaseOrder' => function ($query) {
                $query->withTrashed()->select('id', 'deleted_at');
            },
            'salesOrder' => function ($query) {
                $query->withTrashed()->select('id', 'status', 'customer_id', 'deleted_at');
            },
            'salesOrder.customer:id,email',
            'transportOrders' => function ($query) {
                $query->withTrashed()->select('id', 'transport_type', 'deleted_at');
            },
            'workflow:id,vehicleable_type,vehicleable_id',
            'workOrder' => function ($query) {
                $query->withTrashed()->select('id', 'vehicleable_type', 'vehicleable_id', 'deleted_at');
            },
            'quotes' => function ($query) {
                $query->withTrashed()->select('id', 'deleted_at');
            },
            'documents' => function ($query) {
                $query->withTrashed()->select('id', 'deleted_at');
            },
            'supplier:id,company_id',
            'internalRemarks',
            'changeLogs',
        ]);

        $this->service->setVehicle($vehicle);

        return Inertia::render('vehicles/Edit', [
            'vehicle'              => fn () => $vehicle,
            'workflowProcesses'    => Inertia::lazy(fn () => MultiSelectService::dataForSelectFromArray(ProcessService::getCompanyProcesses()->toArray(), 'className')),
            'pendingOwnerships'    => fn () => OwnershipService::getPending($vehicle),
            'acceptedOwnership'    => fn () => OwnershipService::getAccepted($vehicle),
            'userCompany'          => fn () => auth()->user()->company,
            'vehicleDefaults'      => fn () => auth()->user()->company->setting ?? null,
            'supplierCompanies'    => fn () => CompanyService::getCrmCompanies(CompanyType::Supplier->value),
            'suppliers'            => fn () => UserService::getSuppliers($vehicle->supplier_company_id),
            'vehicleGroup'         => fn () => (new MultiSelectService(VehicleGroup::inThisCompany()))->dataForSelect(),
            'make'                 => fn () => MakeService::getMakes(),
            'vehicleModel'         => fn () => VehicleModelService::getVehicleModels($vehicle->make_id),
            'variant'              => fn () => VariantService::getVariants($vehicle->make_id),
            'engine'               => fn () => EngineService::getEngines($vehicle->make_id, $vehicle->fuel?->value),
            'images'               => fn () => $vehicle->getGroupedImages(['internalImages', 'externalImages']),
            'files'                => fn () => $vehicle->getGroupedFiles(['internalFiles', 'externalFiles']),
            'bpmValues'            => Inertia::lazy(fn () => BpmService::getValues()),
            'transferVehicleToken' => Inertia::lazy(fn () => $this->service->getTransferVehicleLink()),
            'mainCompanyRoles'     => Inertia::lazy(fn () => (new MultiSelectService(RoleService::getMainCompanyRoles()))->dataForSelect()),
            'mainCompanyUsers'     => fn () => (new MultiSelectService(RoleService::getCompanyMainRolesUsers()))->setTextColumnName('full_name')->dataForSelect(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateVehicleRequest $request
     * @param  Vehicle              $vehicle
     * @return RedirectResponse
     * @throws Throwable
     */
    public function update(UpdateVehicleRequest $request, Vehicle $vehicle): RedirectResponse
    {
        DB::beginTransaction();

        $vehicle->load([
            'creator:id,company_id',
            'creator.company:id,name',
        ]);

        try {
            $this->service->setVehicle($vehicle)->updateVehicle($request);

            DB::commit();

            return redirect()->route('vehicles.edit', ['vehicle' => $this->service->getVehicle()->id])->with('success', __('The record has been successfully updated.'));
        } catch (Throwable $th) {
            DB::rollBack();
            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error updating record.')]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Vehicle          $vehicle
     * @return RedirectResponse
     */
    public function destroy(Vehicle $vehicle): RedirectResponse
    {
        try {
            $vehicle->delete();

            return redirect()->back()->with('success', __('The record has been successfully deleted.'));
        } catch (Throwable $th) {
            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error deleting record.')]);
        }
    }

    /**
     * Create vehicle duplications.
     *
     * @param  StoreVehicleDuplicationRequest $request
     * @return RedirectResponse
     * @throws Throwable
     */
    public function duplicate(StoreVehicleDuplicationRequest $request): RedirectResponse
    {
        DB::beginTransaction();

        try {

            $vehicle = Vehicle::with(['calculation'])->findOrFail($request->id);
            $this->authorize('duplicate', $vehicle);

            $this->service->duplicate(
                $vehicle,
                $request,
                [
                    'id',
                    'creator',
                    'calculation',
                    'vin',
                    'nl_registration_number',
                    'supplier_given_damages',
                    'gross_bpm_new',
                    'rest_bpm_as_per_table',
                    'calculation_bpm_in_so',
                    'bpm_declared',
                    'gross_bpm_recalculated_based_on_declaration',
                    'gross_bpm_on_registration',
                    'rest_bpm_to_date',
                    'invoice_bpm',
                    'bpm_post_change_amount',
                    'depreciation_percentage',
                    'damage',
                    'created_at',
                    'updated_at',
                ]
            );

            DB::commit();

            return redirect()->back()->with('success', __('The record has been successfully duplicated.'));
        } catch (Exception $e) {
            DB::rollBack();

            Log::error($e->getMessage(), ['exception' => $e]);

            return redirect()->back()->withErrors([__('Error duplicating record.')]);
        }
    }

    /**
     * Show the vehicle overview page with vehicles and orders connected to them.
     *
     * @return Response
     * @throws AuthorizationException
     */
    public function overview(): Response
    {
        $this->authorize('overview', Vehicle::class);

        $title = __('Overview');

        $dataTable = $this->service->overviewAndFollowing(
            Vehicle::inThisCompany()
        );

        return Inertia::render('vehicles/OverviewFollowing', compact('dataTable', 'title'));
    }

    /**
     * Show vehicle following page with vehicles and orders connected to them.
     *
     * @return Response
     * @throws AuthorizationException
     */
    public function following(): Response
    {
        $this->authorize('following', Vehicle::class);

        $title = __('Following');

        $dataTable = $this->service->overviewAndFollowing(
            Vehicle::inThisCompany()->whereHas('userFollows')
        );

        return Inertia::render('vehicles/OverviewFollowing', compact('dataTable', 'title'));
    }

    /**
     * Show the vehicle management page.
     *
     * @param  string                 $slug
     * @return Response
     * @throws AuthorizationException
     */
    public function management(string $slug): Response
    {
        $this->authorize('management', Vehicle::class);

        $tabs = $this->service->getTabs();
        $method = Str::camel($slug);

        if (! array_key_exists($method, $tabs)) {
            abort(404);
        }

        return Inertia::render('vehicles/Management', [
            'dataTable' => fn () => $this->service->getManagementDataTable($method),
            'tabs'      => fn () => array_keys($tabs),
            'method'    => fn () => $method,
        ]);
    }

    /**
     * Create a sales order with the vehicle provided.
     *
     * @param  Vehicle          $vehicle
     * @return RedirectResponse
     */
    public function storeSalesOrder(Vehicle $vehicle): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $this->authorize('create', SalesOrder::class);

            $salesOrderId = $this->service->setVehicle($vehicle)
                ->createSalesOrder();

            DB::commit();

            return redirect()->route('sales-orders.edit', ['sales_order' => $salesOrderId])->with('success', __('The record has been successfully updated.'));
        } catch (Throwable $th) {
            DB::rollBack();

            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error creating record.')]);
        }
    }

    /**
     * Generate a single quote pdf with the vehicle provided.
     *
     * @param IdLocaleRequest $request
     * @param Vehicle $vehicle
     * @return RedirectResponse
     */
    public function generateQuote(IdLocaleRequest $request, Vehicle $vehicle): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $this->service->setVehicle($vehicle)
                ->createQuotePdf($request);

            DB::commit();

            return redirect()->back()->with('success', __('The record has been successfully created.'));
        } catch (Throwable $th) {
            DB::rollBack();

            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error creating record.')]);
        }
    }

    /**
     * Follow or unfollow a vehicle.
     *
     * @param  Vehicle          $vehicle
     * @return RedirectResponse
     */
    public function follow(Vehicle $vehicle): RedirectResponse
    {
        try {
            $user = auth()->user();

            if ($user->followVehicles()->where('id', $vehicle->id)->exists()) {
                $user->followVehicles()->detach($vehicle->id);

                return redirect()->back()->with('success', __('The record has been unfollowed successfully.'));
            }

            $user->followVehicles()->attach($vehicle->id);

            return redirect()->back()->with('success', __('The record has been followed successfully.'));
        } catch (Throwable $th) {
            DB::rollBack();

            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error creating record.')]);
        }
    }

    /**
     * Transfer the vehicle to the company of the logged user.
     *
     * @param string $token
     * @return RedirectResponse
     */
    public function transfer(string $token): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $this->authorize('transfer', Vehicle::class);

            $this->service->transferVehicle($token);

            DB::commit();

            return redirect()->route('vehicles.edit', ['vehicle' => $this->service->getVehicle()->id])
                ->with('success', __('The record has been successfully created.'));
        } catch (VehicleException $e) {
            return redirect()->route('dashboard')->withErrors([$e->getMessage(), 'lqlqlqlqlqlq']);
        } catch (Throwable $th) {
            DB::rollBack();

            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->route('dashboard')->withErrors([__('Error creating record.')]);
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceOrderRequest;
use App\Http\Requests\UpdateServiceOrderRequest;
use App\Http\Requests\UpdateStatusRequest;

use App\Models\ServiceOrder;
use App\Services\AdditionalServiceService;
use App\Services\CompanyService;
use App\Services\ItemService;
use App\Services\MultiSelectService;
use App\Services\OwnershipService;
use App\Services\RoleService;
use App\Services\ServiceLevelService;
use App\Services\ServiceOrderService;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class ServiceOrderController extends Controller
{
    public ServiceOrderService $service;

    /**
     * Handle policy authorization and service initialization.
     */
    public function __construct()
    {
        $this->service = new ServiceOrderService();
        $this->authorizeResource(ServiceOrder::class);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(): Response
    {
        return Inertia::render('service-orders/Index', [
            'dataTable' => fn () => $this->service->getIndexMethodDataTable()->run(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('service-orders/Create', [
            'dataTable'            => fn () => $this->service->getCreateMethodVehiclesTable(),
            'queryVehicleId'       => fn () => $this->service->getQueryVehicleId(),
            'companies'            => fn () => CompanyService::getCrmCompanies(),
            'serviceLevels'        => fn () => ServiceLevelService::getCompanyServiceLevels(),
            'resetServiceLevels'   => Inertia::lazy(fn () => ServiceLevelService::shouldResetServiceLevels()),
            'serviceLevelDefaults' => Inertia::lazy(fn () => ServiceLevelService::getServiceLevelDefaults()),
            'mainCompanyRoles'     => Inertia::lazy(fn () => (new MultiSelectService(RoleService::getMainCompanyRoles()))->dataForSelect()),
            'mainCompanyUsers'     => fn () => (new MultiSelectService(RoleService::getCompanyMainRolesUsers()))->setTextColumnName('full_name')->dataForSelect(),
            'customers'            => Inertia::lazy(fn () => UserService::getCustomers()),
            'items'                => Inertia::lazy(fn () => ItemService::getByServiceLevel()),
            'levelServices'        => Inertia::lazy(fn () => AdditionalServiceService::getByServiceLevel()),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreServiceOrderRequest $request
     * @return RedirectResponse
     * @throws Throwable
     */
    public function store(StoreServiceOrderRequest $request): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $this->service
                ->createServiceOrder($request);

            DB::commit();

            return redirect()->route('service-orders.edit', ['service_order' => $this->service->serviceOrder->id])->with('success', __('The record has been successfully created.'));
        } catch (Throwable $th) {
            DB::rollBack();

            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error creating record.')]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  ServiceOrder $serviceOrder
     * @return Response
     */
    public function edit(ServiceOrder $serviceOrder): Response
    {
        $serviceOrder->load([
            'ownerships.user:id,full_name',
            'customer:id,full_name,company_id',
            'seller:id,full_name',
            'serviceVehicle' => function ($query) {
                $query->withTrashed();
            },
            'images',
            'internalRemarks', 'statuses',
        ]);

        $this->service->setServiceOrder($serviceOrder);

        return Inertia::render('service-orders/Edit', [
            'serviceOrder'         => fn () => $serviceOrder,
            'dataTable'            => fn () => $this->service->getEditMethodVehiclesTable(),
            'files'                => fn () => $serviceOrder->getGroupedFiles(['files', 'vehicleDocuments']),
            'companies'            => fn () => CompanyService::getCrmCompanies(),
            'customers'            => fn () => UserService::getCustomers($serviceOrder->customer_company_id),
            'pendingOwnerships'    => fn () => OwnershipService::getPending($serviceOrder),
            'acceptedOwnership'    => fn () => OwnershipService::getAccepted($serviceOrder),
            'mainCompanyRoles'     => Inertia::lazy(fn () => (new MultiSelectService(RoleService::getMainCompanyRoles()))->dataForSelect()),
            'mainCompanyUsers'     => fn () => (new MultiSelectService(RoleService::getCompanyMainRolesUsers()))->setTextColumnName('full_name')->dataForSelect(),
            'serviceLevels'        => fn () => ServiceLevelService::getCompanyServiceLevels($serviceOrder->customer_company_id),
            'resetServiceLevels'   => Inertia::lazy(fn () => ServiceLevelService::shouldResetServiceLevels($serviceOrder)),
            'serviceLevelDefaults' => Inertia::lazy(fn () => ServiceLevelService::getServiceLevelDefaults()),
            'items'                => fn () => ItemService::getByServiceLevel($serviceOrder->orderItems()),
            'levelServices'        => fn () => AdditionalServiceService::getByServiceLevel($serviceOrder->orderServices()),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateServiceOrderRequest $request
     * @param  ServiceOrder              $serviceOrder
     * @return RedirectResponse
     * @throws Throwable
     */
    public function update(UpdateServiceOrderRequest $request, ServiceOrder $serviceOrder): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $this->service
                ->setServiceOrder($serviceOrder)
                ->updateServiceOrder($request);

            DB::commit();

            return redirect()->route('service-orders.edit', ['service_order' => $serviceOrder->id])->with('success', __('The record has been successfully updated.'));
        } catch (Throwable $th) {
            DB::rollBack();

            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error updating record.')]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  ServiceOrder     $serviceOrder
     * @return RedirectResponse
     */
    public function destroy(ServiceOrder $serviceOrder): RedirectResponse
    {
        try {
            $serviceOrder->delete();

            return redirect()->back()->with('success', __('The record has been successfully deleted.'));
        } catch (Throwable $th) {
            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error deleting record.')]);
        }
    }

    /**
     * Updates the status of the Service order.
     *
     * @param  UpdateStatusRequest $request
     * @return RedirectResponse
     */
    public function updateStatus(UpdateStatusRequest $request): RedirectResponse
    {
        try {
            $validatedRequest = $request->validated();

            $serviceOrder = ServiceOrder::findOrFail($validatedRequest['id']);
            $this->authorize('updateStatus', [$serviceOrder, $validatedRequest['status']]);

            $this->service->setServiceOrder($serviceOrder)
                ->updateServiceOrderStatus($validatedRequest['status']);

            return redirect()->back()->with('success', __('The record has been successfully updated.'));
        } catch (Throwable $th) {
            DB::rollback();

            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error updating record.')]);
        }
    }
}

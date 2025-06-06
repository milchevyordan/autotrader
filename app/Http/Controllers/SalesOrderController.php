<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\SalesOrderException;
use App\Http\Requests\StoreSalesOrderRequest;
use App\Http\Requests\StoreWorkOrdersRequest;
use App\Http\Requests\UpdateSalesOrderRequest;
use App\Http\Requests\UpdateStatusRequest;

use App\Models\SalesOrder;
use App\Models\WorkOrder;
use App\Services\AdditionalServiceService;
use App\Services\CompanyService;
use App\Services\ItemService;
use App\Services\MultiSelectService;
use App\Services\OwnershipService;
use App\Services\RoleService;
use App\Services\SalesOrderService;
use App\Services\ServiceLevelService;
use App\Services\UserService;
use App\Services\Vehicles\SystemVehicleService;
use App\Services\VehicleStockService;
use App\Services\Workflow\ProcessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class SalesOrderController extends Controller
{
    public SalesOrderService $service;

    /**
     * Handle policy authorization and service initialization.
     */
    public function __construct()
    {
        $this->authorizeResource(SalesOrder::class);
        $this->service = new SalesOrderService();
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(): Response
    {
        $dataTable = $this->service->getIndexMethodDataTable()->run();

        return Inertia::render('sales-orders/Index', compact('dataTable'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('sales-orders/Create', [
            'companies'            => fn () => CompanyService::getCrmCompanies(),
            'customers'            => Inertia::lazy(fn () => UserService::getCustomers()),
            'dataTable'            => fn () => $this->service->getCreateMethodVehiclesDataTable(),
            'items'                => Inertia::lazy(fn () => ItemService::getByServiceLevel()),
            'levelServices'        => Inertia::lazy(fn () => AdditionalServiceService::getByServiceLevel()),
            'mainCompanyRoles'     => Inertia::lazy(fn () => (new MultiSelectService(RoleService::getMainCompanyRoles()))->dataForSelect()),
            'mainCompanyUsers'     => fn () => (new MultiSelectService(RoleService::getCompanyMainRolesUsers()))->setTextColumnName('full_name')->dataForSelect(),
            'serviceLevels'        => fn () => ServiceLevelService::getCompanyServiceLevels(),
            'resetServiceLevels'   => Inertia::lazy(fn () => ServiceLevelService::shouldResetServiceLevels()),
            'serviceLevelDefaults' => Inertia::lazy(fn () => ServiceLevelService::getServiceLevelDefaults()),
            'vehicleInformation'   => Inertia::lazy(fn () => SystemVehicleService::getVehicleInformation()),
            'workflowProcesses'    => Inertia::lazy(fn () => MultiSelectService::dataForSelectFromArray(ProcessService::getCompanyProcesses()->toArray(), 'className')),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreSalesOrderRequest $request
     * @return RedirectResponse
     */
    public function store(StoreSalesOrderRequest $request): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $this->service->createSalesOrder($request);

            DB::commit();

            return redirect()->route('sales-orders.edit', ['sales_order' => $this->service->getSalesOrder()->id])->with('success', __('The record has been successfully created.'));
        } catch (Throwable $th) {
            DB::rollBack();

            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error creating record.')]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  SalesOrder $salesOrder
     * @return Response
     */
    public function edit(SalesOrder $salesOrder): Response
    {
        $salesOrder->load([
            'ownerships.user:id,full_name',
            'customerCompany:id,name,email',
            'customer:id,full_name,email,company_id',
            'seller:id,full_name,company_id',
            'quote:id,sales_order_id',
            'internalRemarks',
            'mails',
            'statuses',
            'vehicles' => function ($query) {
                $query->withTrashed()->select('id', 'make_id', 'vehicle_model_id', 'supplier_company_id');
            },
            'documents' => function ($query) {
                $query->withTrashed()->select('id', 'documents.documentable_type');
            },
            'vehicles.make:id,name',
            'vehicles.vehicleModel:id,name',
            'vehicles.workOrder' => function ($query) {
                $query->withTrashed()->select('id', 'vehicleable_type', 'vehicleable_id', 'deleted_at');
            },
            'vehicles.transportOrders' => function ($query) {
                $query->withTrashed()->select('id', 'transportable_type', 'deleted_at');
            },
            'vehicles.purchaseOrder' => function ($query) {
                $query->withTrashed()->select('id', 'deleted_at');
            },
            'vehicles.quotes' => function ($query) {
                $query->withTrashed()->select('id', 'deleted_at');
            },
            'vehicles.documents' => function ($query) {
                $query->withTrashed()->select('id', 'deleted_at');
            },
            'vehicles.supplier:id,company_id',
            'vehicles.calculation',
        ]);

        $this->service->setSalesOrder($salesOrder);

        return Inertia::render('sales-orders/Edit', [
            'salesOrder'                  => fn () => $salesOrder,
            'companies'                   => fn () => CompanyService::getCrmCompanies(),
            'pendingOwnerships'           => fn () => OwnershipService::getPending($salesOrder),
            'acceptedOwnership'           => fn () => OwnershipService::getAccepted($salesOrder),
            'dataTable'                   => fn () => $this->service->getEditMethodVehiclesDataTable(),
            'selectedVehicles'            => fn () => $this->service->getSelectedVehicles()->pluck('pivot', 'id'),
            'files'                       => fn () => $salesOrder->getGroupedFiles(['files', 'contractSignedFiles', 'creditCheckFiles', 'viesFiles', 'generatedPdf']),
            'serviceLevels'               => fn () => ServiceLevelService::getCompanyServiceLevels($salesOrder->customer_company_id),
            'resetServiceLevels'          => Inertia::lazy(fn () => ServiceLevelService::shouldResetServiceLevels($salesOrder)),
            'serviceLevelDefaults'        => Inertia::lazy(fn () => ServiceLevelService::getServiceLevelDefaults()),
            'items'                       => fn () => ItemService::getByServiceLevel($salesOrder->orderItems()),
            'levelServices'               => fn () => AdditionalServiceService::getByServiceLevel($salesOrder->orderServices()),
            'customers'                   => fn () => UserService::getCustomers($salesOrder->customer_company_id),
            'vehicleInformation'          => Inertia::lazy(fn () => SystemVehicleService::getVehicleInformation()),
            'mainCompanyRoles'            => Inertia::lazy(fn () => (new MultiSelectService(RoleService::getMainCompanyRoles()))->dataForSelect()),
            'mainCompanyUsers'            => fn () => (new MultiSelectService(RoleService::getCompanyMainRolesUsers()))->setTextColumnName('full_name')->dataForSelect(),
            'previewPdfUrl'               => Inertia::lazy(fn () => $this->service->generatePreviewPdf()),
            'canCreateDownPaymentInvoice' => fn () => $this->service->canCreateDownPaymentInvoice($salesOrder),
            'workflowProcesses'           => Inertia::lazy(fn () => MultiSelectService::dataForSelectFromArray(ProcessService::getCompanyProcesses()->toArray(), 'className')),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateSalesOrderRequest $request
     * @param  SalesOrder              $salesOrder
     * @return RedirectResponse
     * @throws Throwable
     */
    public function update(UpdateSalesOrderRequest $request, SalesOrder $salesOrder): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $this->service
                ->setSalesOrder($salesOrder)
                ->updateSalesOrder($request);

            DB::commit();

            return redirect()->route('sales-orders.edit', ['sales_order' => $salesOrder->id])->with('success', __('The record has been successfully updated.'));
        } catch (Throwable $th) {
            DB::rollBack();

            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error updating record.')]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  SalesOrder       $salesOrder
     * @return RedirectResponse
     */
    public function destroy(SalesOrder $salesOrder): RedirectResponse
    {
        try {
            $salesOrder->delete();

            return redirect()->back()->with('success', __('The record has been successfully deleted.'));
        } catch (Throwable $th) {
            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error deleting record.')]);
        }
    }

    /**
     * Updates the status of the Sales order.
     *
     * @param  UpdateStatusRequest $request
     * @param  VehicleStockService $vehicleStockService
     * @return RedirectResponse
     */
    public function updateStatus(UpdateStatusRequest $request, VehicleStockService $vehicleStockService): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $validatedRequest = $request->validated();
            $salesOrder = SalesOrder::findOrFail($validatedRequest['id']);
            $this->authorize('updateStatus', [$salesOrder, $validatedRequest['status']]);

            $this->service
                ->setSalesOrder($salesOrder)
                ->updateSalesOrderStatus($request);

            $vehicleStockService->handleSalesOrderStatusUpdate($salesOrder);

            DB::commit();

            return redirect()->back()->with('success', __('The record has been successfully updated.'));
        } catch (SalesOrderException $e) {

            return redirect()->back()->withErrors([$e->getMessage()]);
        } catch (Throwable $th) {
            DB::rollback();

            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error updating record.')]);
        }
    }

    /**
     * Create work orders from sales order.
     *
     * @param  StoreWorkOrdersRequest $request
     * @return RedirectResponse
     */
    public function storeWorkOrder(StoreWorkOrdersRequest $request): RedirectResponse
    {
        try {
            $this->authorize('create', WorkOrder::class);

            $this->service->createWorkOrders($request);

            return redirect()->back()->with('success', __('The record has been successfully updated.'));
        } catch (Throwable $th) {
            DB::rollback();

            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error updating record.')]);
        }
    }
}

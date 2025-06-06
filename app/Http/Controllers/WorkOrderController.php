<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreWorkOrderRequest;
use App\Http\Requests\StoreWorkOrdersFromVehicleRequest;
use App\Http\Requests\UpdateStatusRequest;
use App\Http\Requests\UpdateWorkOrderRequest;

use App\Models\WorkOrder;
use App\Services\MultiSelectService;
use App\Services\OwnershipService;
use App\Services\RoleService;
use App\Services\Workflow\ProcessService;
use App\Services\WorkOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class WorkOrderController extends Controller
{
    public WorkOrderService $service;

    /**
     * Handle policy authorization and service initialization.
     */
    public function __construct()
    {
        $this->authorizeResource(WorkOrder::class);
        $this->service = new WorkOrderService();
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(): Response
    {
        return Inertia::render('work-orders/Index', [
            'dataTable' => fn () => $this->service->getIndexMethodDataTable()->run(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('work-orders/Create', [
            'dataTable'         => fn () => $this->service->getCreateMethodVehicles(),
            'mainCompanyRoles'  => Inertia::lazy(fn () => (new MultiSelectService(RoleService::getMainCompanyRoles()))->dataForSelect()),
            'mainCompanyUsers'  => fn () => (new MultiSelectService(RoleService::getCompanyMainRolesUsers()))->setTextColumnName('full_name')->dataForSelect(),
            'workflowProcesses' => Inertia::lazy(fn () => MultiSelectService::dataForSelectFromArray(ProcessService::getCompanyProcesses()->toArray(), 'className')),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreWorkOrderRequest $request
     * @return RedirectResponse
     */
    public function store(StoreWorkOrderRequest $request): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $this->service->createWorkOrder($request);

            DB::commit();

            return redirect()->route('work-orders.edit', ['work_order' => $this->service->workOrder->id])->with('success', __('The record has been successfully created.'));
        } catch (Throwable $th) {
            DB::rollBack();

            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error creating record.')]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  WorkOrder $workOrder
     * @return Response
     */
    public function edit(WorkOrder $workOrder): Response
    {
        $workOrder->load([
            'ownerships.user:id,full_name',
            'internalRemarks',
            'vehicleable' => function ($query) {
                $query->withTrashed();
            },
            'tasks', 'tasks.images', 'tasks.files', 'statuses',
        ]);

        $this->service->setWorkOrder($workOrder);

        return Inertia::render('work-orders/Edit', [
            'workOrder'         => fn () => $workOrder,
            'mainCompanyUsers'  => fn () => (new MultiSelectService(RoleService::getCompanyMainRolesUsers()))->setTextColumnName('full_name')->dataForSelect(),
            'pendingOwnerships' => fn () => OwnershipService::getPending($workOrder),
            'acceptedOwnership' => fn () => OwnershipService::getAccepted($workOrder),
            'dataTable'         => fn () => $this->service->getEditMethodVehicles(),
            'files'             => fn () => $workOrder->getGroupedFiles(['files']),
            'workflowProcesses' => Inertia::lazy(fn () => MultiSelectService::dataForSelectFromArray(ProcessService::getCompanyProcesses()->toArray(), 'className')),
            'mainCompanyRoles'  => Inertia::lazy(fn () => (new MultiSelectService(RoleService::getMainCompanyRoles()))->dataForSelect()),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateWorkOrderRequest $request
     * @param  WorkOrder              $workOrder
     * @return RedirectResponse
     */
    public function update(UpdateWorkOrderRequest $request, WorkOrder $workOrder): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $this->service
                ->setWorkOrder($workOrder)
                ->updateWorkOrder($request);

            DB::commit();

            return redirect()->route('work-orders.edit', ['work_order' => $workOrder->id])->with('success', __('The record has been successfully created.'));
        } catch (Throwable $th) {
            DB::rollBack();

            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error creating record.')]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  WorkOrder        $workOrder
     * @return RedirectResponse
     */
    public function destroy(WorkOrder $workOrder): RedirectResponse
    {
        try {
            $workOrder->delete();

            return redirect()->back()->with('success', __('The record has been successfully deleted.'));
        } catch (Throwable $th) {
            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error deleting record.')]);
        }
    }

    /**
     * Updates the status of the Transport order.
     *
     * @param  UpdateStatusRequest $request
     * @return RedirectResponse
     */
    public function updateStatus(UpdateStatusRequest $request): RedirectResponse
    {
        try {
            $validatedRequest = $request->validated();
            $workOrder = WorkOrder::findOrFail($validatedRequest['id']);
            $this->authorize('updateStatus', [$workOrder, $validatedRequest['status']]);

            $this->service
                ->setWorkOrder($workOrder)
                ->updateWorkOrderStatus($request);

            return redirect()->back()->with('success', __('The record has been successfully updated.'));
        } catch (Throwable $th) {
            DB::rollback();

            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error updating record.')]);
        }
    }

    /**
     * Create work order from vehicle
     *
     * @param StoreWorkOrdersFromVehicleRequest $request
     * @return RedirectResponse
     */
    public function createFromVehicle(StoreWorkOrdersFromVehicleRequest $request): RedirectResponse
    {
        try {
            $this->authorize('create', WorkOrder::class);

            $workOrderId = $this->service->createWorkOrderFromVehicle($request);

            return redirect()->route('work-orders.edit', ['work_order' => $workOrderId])->with('success', __('The record has been successfully updated.'));
        } catch (Throwable $th) {
            DB::rollback();

            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error updating record.')]);
        }
    }
}

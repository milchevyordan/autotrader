<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\CompanyType;
use App\Exceptions\PurchaseOrderException;
use App\Http\Requests\StorePurchaseOrderRequest;
use App\Http\Requests\UpdatePurchaseOrderRequest;
use App\Http\Requests\UpdateStatusRequest;
use App\Models\PurchaseOrder;

use App\Services\CompanyService;
use App\Services\MultiSelectService;
use App\Services\OwnershipService;
use App\Services\PurchaseOrderService;
use App\Services\RoleService;
use App\Services\UserService;
use App\Services\VehicleStockService;
use App\Services\Workflow\ProcessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class PurchaseOrderController extends Controller
{
    public PurchaseOrderService $service;

    /**
     * Handle policy authorization and service initialization.
     */
    public function __construct()
    {
        $this->authorizeResource(PurchaseOrder::class);
        $this->service = new PurchaseOrderService();
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(): Response
    {
        return Inertia::render('purchase-orders/Index', [
            'dataTable' => $this->service->getIndexMethodTable()->run(),
        ]);
    }

    /**
     * Show the form for creating a new purchase order.
     */
    public function create(): Response
    {
        return Inertia::render('purchase-orders/Create', [
            'companies'         => fn () => CompanyService::getCrmCompanies(CompanyType::Supplier->value),
            'companyDefaults'   => Inertia::lazy(fn () => CompanyService::getCompanyDefaults()),
            'dataTable'         => fn () => $this->service->getCreateVehicles(),
            'queryVehicleIds'   => fn () => $this->service->getQueryVehicleIds(),
            'suppliers'         => fn () => UserService::getSuppliers(),
            'intermediaries'    => Inertia::lazy(fn () => UserService::getIntermediaries()),
            'purchasers'        => fn () => UserService::getCompanyPurchasers(),
            'workflowProcesses' => Inertia::lazy(fn () => MultiSelectService::dataForSelectFromArray(ProcessService::getCompanyProcesses()->toArray(), 'className')),
            'mainCompanyRoles'  => Inertia::lazy(fn () => (new MultiSelectService(RoleService::getMainCompanyRoles()))->dataForSelect()),
            'mainCompanyUsers'  => fn () => (new MultiSelectService(RoleService::getCompanyMainRolesUsers()))->setTextColumnName('full_name')->dataForSelect(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StorePurchaseOrderRequest $request
     * @return RedirectResponse
     * @throws Throwable
     */
    public function store(StorePurchaseOrderRequest $request): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $this->service->createPurchaseOrder($request);

            DB::commit();

            return redirect()->route('purchase-orders.edit', ['purchase_order' => $this->service->purchaseOrder->id])->with('success', __('The record has been successfully created.'));
        } catch (Throwable $th) {
            DB::rollBack();

            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error creating record.')]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  PurchaseOrder $purchaseOrder
     * @return Response
     */
    public function edit(PurchaseOrder $purchaseOrder): Response
    {
        $purchaseOrder->load([
            'ownerships.user:id,full_name',
            'supplierCompany:id,email',
            'supplier:id,full_name,company_id,email',
            'intermediary:id,full_name,company_id',
            'purchaser:id,full_name',
            'internalRemarks', 'mails', 'statuses',
        ]);

        $this->service->setPurchaseOrder($purchaseOrder);

        return Inertia::render('purchase-orders/Edit', [
            'pendingOwnerships'  => fn () => OwnershipService::getPending($purchaseOrder),
            'acceptedOwnership'  => fn () => OwnershipService::getAccepted($purchaseOrder),
            'companies'          => fn () => CompanyService::getCrmCompanies(CompanyType::Supplier->value),
            'companyDefaults'    => Inertia::lazy(fn () => CompanyService::getCompanyDefaults()),
            'suppliers'          => fn () => UserService::getSuppliers($purchaseOrder->supplier_company_id),
            'intermediaries'     => fn () => UserService::getIntermediaries($purchaseOrder->intermediary_company_id),
            'purchasers'         => fn () => UserService::getCompanyPurchasers(),
            'purchaseOrder'      => fn () => $purchaseOrder,
            'dataTable'          => fn () => $this->service->getEditVehicles(),
            'selectedVehicles'   => fn () => $this->service->getSelectedVehicles(),
            'selectedVehicleIds' => fn () => $this->service->getSelectedVehicles()->pluck('id'),
            'files'              => fn () => $purchaseOrder->getGroupedFiles(['files', 'contractSignedFiles', 'creditCheckFiles', 'viesFiles', 'generatedPdf']),
            'workflowProcesses'  => Inertia::lazy(fn () => MultiSelectService::dataForSelectFromArray(ProcessService::getCompanyProcesses()->toArray(), 'className')),
            'mainCompanyRoles'   => Inertia::lazy(fn () => (new MultiSelectService(RoleService::getMainCompanyRoles()))->dataForSelect()),
            'mainCompanyUsers'   => fn () => (new MultiSelectService(RoleService::getCompanyMainRolesUsers()))->setTextColumnName('full_name')->dataForSelect(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdatePurchaseOrderRequest $request
     * @param  PurchaseOrder              $purchaseOrder
     * @return RedirectResponse
     * @throws Throwable
     */
    public function update(UpdatePurchaseOrderRequest $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $this->service
                ->setPurchaseOrder($purchaseOrder)
                ->updatePurchaseOrder($request);

            DB::commit();

            return redirect()->route('purchase-orders.edit', ['purchase_order' => $purchaseOrder->id])->with('success', __('The record has been successfully updated.'));
        } catch (Throwable $th) {
            DB::rollBack();

            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error updating record.')]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  PurchaseOrder    $purchaseOrder
     * @return RedirectResponse
     */
    public function destroy(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        try {
            $purchaseOrder->delete();

            return redirect()->back()->with('success', __('The record has been successfully deleted.'));
        } catch (Throwable $th) {
            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error deleting record.')]);
        }
    }

    /**
     * Updates the status of the Purchase order.
     *
     * @param  UpdateStatusRequest $request
     * @param  VehicleStockService $vehicleStockService
     * @return RedirectResponse
     */
    public function updateStatus(UpdateStatusRequest $request, VehicleStockService $vehicleStockService): RedirectResponse
    {
        try {
            $validatedRequest = $request->validated();
            $purchaseOrder = PurchaseOrder::findOrFail($validatedRequest['id']);
            $this->authorize('updateStatus', [$purchaseOrder, $validatedRequest['status']]);

            $this->service
                ->setPurchaseOrder($purchaseOrder)
                ->updatePurchaseOrderStatus($request);

            $vehicleStockService->handlePurchaseOrderStatusUpdate($purchaseOrder);

            return redirect()->back()->with('success', __('The record has been successfully updated.'));
        } catch (PurchaseOrderException $e) {
            return redirect()->back()->withErrors([$e->getMessage()]);
        } catch (Throwable $th) {
            DB::rollback();

            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error updating record.')]);
        }
    }
}

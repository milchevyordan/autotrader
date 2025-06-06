<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ImportEuOrWorldType;
use App\Enums\PreOrderStatus;
use App\Http\Requests\StorePreOrderRequest;
use App\Http\Requests\StoreVehicleDuplicationRequest;
use App\Http\Requests\UpdatePreOrderRequest;
use App\Http\Requests\UpdateStatusRequest;
use App\Models\PreOrder;

use App\Services\DataTable\DataTable;
use App\Services\MultiSelectService;
use App\Services\OwnershipService;
use App\Services\PreOrderService;
use App\Services\RoleService;
use App\Services\UserService;
use App\Services\Vehicles\SystemVehicleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class PreOrderController extends Controller
{
    public PreOrderService $service;

    /**
     * Handle policy authorization and service initialization.
     */
    public function __construct()
    {
        $this->authorizeResource(PreOrder::class);
        $this->service = new PreOrderService();
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(): Response
    {
        $dataTable = (new DataTable(
            PreOrder::inThisCompany()->select(PreOrder::$defaultSelectFields)
        ))
            ->setRelation('supplierCompany', ['id', 'name'])
            ->setRelation('purchaser', ['id', 'company_id'])
            ->setRelation('purchaser.company', ['id', 'name'])
            ->setRelation('creator')
            ->setColumn('action', __('Action'), exportable: false)
            ->setColumn('id', '#', true, true)
            ->setColumn('creator.full_name', __('Creator'), true)
            ->setColumn('status', __('Status'), true, true)
            ->setColumn('type', __('Purchase Type'), true, true)
            ->setColumn('supplierCompany.name', __('Supplier'), true, true)
            ->setColumn('purchaser.company.name', __('Purchaser'), true, true)
            ->setTimestamps()
            ->setEnumColumn('status', PreOrderStatus::class)
            ->setEnumColumn('type', ImportEuOrWorldType::class)
            ->run();

        return Inertia::render('pre-orders/Index', compact('dataTable'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('pre-orders/Create', [
            'companies'         => fn () => $this->service->getCompanies(),
            'defaultCurrencies' => fn () => $this->service->getDefaultCurrencies(),
            'queryVehicleId'    => fn () => $this->service->getQueryVehicleId(),
            'dataTable'         => fn () => $this->service->getCreateVehicles(),
            'suppliers'         => Inertia::lazy(fn () => UserService::getSuppliers()),
            'intermediaries'    => Inertia::lazy(fn () => UserService::getIntermediaries()),
            'purchasers'        => fn () => UserService::getCompanyPurchasers(),
            'mainCompanyRoles'  => Inertia::lazy(fn () => (new MultiSelectService(RoleService::getMainCompanyRoles()))->dataForSelect()),
            'mainCompanyUsers'  => fn () => (new MultiSelectService(RoleService::getCompanyMainRolesUsers()))->setTextColumnName('full_name')->dataForSelect(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StorePreOrderRequest $request
     * @return RedirectResponse
     * @throws Throwable
     */
    public function store(StorePreOrderRequest $request): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $this->service->createPreOrder($request);

            DB::commit();

            return redirect()->route('pre-orders.edit', ['pre_order' => $this->service->preOrder->id])->with('success', __('The record has been successfully created.'));
        } catch (Throwable $th) {
            DB::rollBack();

            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error creating record.')]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  PreOrder $preOrder
     * @return Response
     */
    public function edit(PreOrder $preOrder): Response
    {
        $preOrder->load([
            'ownerships.user:id,full_name',
            'supplierCompany:id,email',
            'supplier:id,full_name,email',
            'intermediary:id,full_name',
            'purchaser:id,full_name',
            'preOrderVehicle' => function ($query) {
                $query->withTrashed()->select('id', 'deleted_at');
            },
            'preOrderVehicle.calculation',
            'internalRemarks', 'mails', 'statuses', 'changeLogs',
        ]);

        $this->service->setPreOrder($preOrder);

        return Inertia::render('pre-orders/Edit', [
            'companies'         => fn () => $this->service->getCompanies(),
            'defaultCurrencies' => fn () => $this->service->getDefaultCurrencies(),
            'suppliers'         => fn () => UserService::getSuppliers($preOrder->supplier_company_id),
            'intermediaries'    => fn () => UserService::getIntermediaries($preOrder->intermediary_company_id),
            'purchasers'        => fn () => UserService::getCompanyPurchasers(),
            'pendingOwnerships' => fn () => OwnershipService::getPending($preOrder),
            'acceptedOwnership' => fn () => OwnershipService::getAccepted($preOrder),
            'preOrder'          => fn () => $preOrder,
            'dataTable'         => fn () => $this->service->getEditVehicles(),
            'files'             => fn () => $preOrder->getGroupedFiles(['files', 'contractUnsignedFiles', 'contractSignedFiles', 'creditCheckFiles', 'viesFiles', 'generatedPdf']),
            'mainCompanyRoles'  => Inertia::lazy(fn () => (new MultiSelectService(RoleService::getMainCompanyRoles()))->dataForSelect()),
            'mainCompanyUsers'  => fn () => (new MultiSelectService(RoleService::getCompanyMainRolesUsers()))->setTextColumnName('full_name')->dataForSelect(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdatePreOrderRequest $request
     * @param  PreOrder              $preOrder
     * @return RedirectResponse
     * @throws Throwable
     */
    public function update(UpdatePreOrderRequest $request, PreOrder $preOrder): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $this->service
                ->setPreOrder($preOrder)
                ->updatePreOrder($request);

            DB::commit();

            return redirect()->route('pre-orders.edit', ['pre_order' => $preOrder->id])->with('success', __('The record has been successfully updated.'));
        } catch (Throwable $th) {
            DB::rollBack();

            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error updating record.')]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  PreOrder         $preOrder
     * @return RedirectResponse
     */
    public function destroy(PreOrder $preOrder): RedirectResponse
    {
        try {
            $preOrder->delete();

            return redirect()->back()->with('success', __('The record has been successfully deleted.'));
        } catch (Throwable $th) {
            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error deleting record.')]);
        }
    }

    /**
     * Updates the status of the Pre order.
     *
     * @param  UpdateStatusRequest $request
     * @return RedirectResponse
     */
    public function updateStatus(UpdateStatusRequest $request): RedirectResponse
    {
        try {
            $validatedRequest = $request->validated();
            $preOrder = PreOrder::findOrFail($validatedRequest['id']);
            $this->authorize('updateStatus', [$preOrder, $validatedRequest['status']]);

            $this->service
                ->setPreOrder($preOrder)
                ->updatePreOrderStatus($request);

            return redirect()->back()->with('success', __('The record has been successfully updated.'));
        } catch (Throwable $th) {
            DB::rollback();

            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error updating record.')]);
        }
    }

    /**
     * Create vehicles from preorder vehicles.
     *
     * @param  StoreVehicleDuplicationRequest $request
     * @return RedirectResponse
     */
    public function storeVehicles(StoreVehicleDuplicationRequest $request): RedirectResponse
    {
        try {
            $preOrder = PreOrder::with([
                'preOrderVehicle', 'preOrderVehicle.make:id,name', 'preOrderVehicle.vehicleModel:id,name',
                'preOrderVehicle.variant:id,name', 'preOrderVehicle.engine:id,name', 'preOrderVehicle.calculation',
            ])->findOrFail($request->id);
            $this->authorize('update', $preOrder);

            DB::beginTransaction();

            $systemVehicleService = new SystemVehicleService();
            $createdVehicleIds = $systemVehicleService->duplicate(
                $preOrder->preOrderVehicle,
                $request,
                ['id', 'configuration_number', 'komm_number', 'calculation', 'expected_delivery_weeks', 'production_weeks', 'registration_weeks_from', 'registration_weeks_to', 'created_at', 'updated_at'],
            );

            $preOrder->vehicles()->sync($createdVehicleIds);

            $preOrder->status = PreOrderStatus::Files_creation->value;
            $preOrder->save();

            DB::commit();

            return redirect()->back()->with('success', __('The record has been successfully updated.'));
        } catch (Throwable $th) {
            DB::rollback();

            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error updating record.')]);
        }
    }
}

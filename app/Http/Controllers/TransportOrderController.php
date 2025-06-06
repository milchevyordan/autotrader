<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\CompanyType;
use App\Enums\TransportOrderFileType;
use App\Exceptions\TransportOrderException;
use App\Http\Requests\GenerateTransportOrderFileRequest;
use App\Http\Requests\StoreTransportOrderRequest;
use App\Http\Requests\UpdateStatusRequest;
use App\Http\Requests\UpdateTransportOrderRequest;

use App\Models\TransportOrder;
use App\Services\CompanyService;
use App\Services\MultiSelectService;
use App\Services\OwnershipService;
use App\Services\RoleService;
use App\Services\TransportOrderService;
use App\Services\UserService;
use App\Services\Workflow\ProcessService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class TransportOrderController extends Controller
{
    public TransportOrderService $service;

    /**
     * Handle policy authorization and service initialization.
     */
    public function __construct()
    {
        $this->authorizeResource(TransportOrder::class);
        $this->service = new TransportOrderService();
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(): Response
    {
        return Inertia::render('transport-orders/Index', [
            'dataTable' => $this->service->getIndexMethodTable()->run(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('transport-orders/Create', [
            'companies'             => fn () => CompanyService::getCrmCompanies(CompanyType::Transport->value),
            'dataTable'             => fn () => $this->service->initTransportablesDataTable(),
            'queryVehicleIds'       => fn () => $this->service->getQueryVehicleIds(),
            'mainCompanyRoles'      => Inertia::lazy(fn () => (new MultiSelectService(RoleService::getMainCompanyRoles()))->dataForSelect()),
            'mainCompanyUsers'      => fn () => (new MultiSelectService(RoleService::getCompanyMainRolesUsers()))->setTextColumnName('full_name')->dataForSelect(),
            'transporters'          => Inertia::lazy(fn () => UserService::getTransportSuppliers()),
            'pickUpAddresses'       => Inertia::lazy(fn () => CompanyService::getCompanyLogisticsAddresses(request()->input('pick_up_company_id'))),
            'deliveryAddresses'     => Inertia::lazy(fn () => CompanyService::getCompanyLogisticsAddresses(request()->input('delivery_company_id'))),
            'ownLogisticsAddresses' => fn () => (new MultiSelectService(auth()->user()->company->logisticsAddresses()))->setTextColumnName('address')->dataForSelect(),
            'workflowProcesses'     => Inertia::lazy(fn () => MultiSelectService::dataForSelectFromArray(ProcessService::getCompanyProcesses()->toArray(), 'className')),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreTransportOrderRequest $request
     * @return RedirectResponse
     */
    public function store(StoreTransportOrderRequest $request): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $this->service->createTransportOrder($request);

            DB::commit();

            return redirect()->route('transport-orders.edit', ['transport_order' => $this->service->transportOrder->id])
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
     * @param  TransportOrder $transportOrder
     * @return Response
     */
    public function edit(TransportOrder $transportOrder): Response
    {
        $transportOrder->load([
            'ownerships.user:id,full_name',
            'transportCompany:id,name,email',
            'transporter:id,full_name,company_id,email',
            'internalRemarks', 'mails', 'statuses',
            'pickUpLocation:id,address', 'deliveryLocation:id,address',
        ]);

        $this->service->setTransportOrder($transportOrder);

        $selectedTransportables = $this->service->getSelectedTransportables();

        return Inertia::render('transport-orders/Edit', [
            'companies'                => fn () => CompanyService::getCrmCompanies(CompanyType::Transport->value),
            'transportOrder'           => fn () => $transportOrder,
            'dataTable'                => fn () => $this->service->initTransportablesDataTable(),
            'selectedTransportables'   => fn () => $selectedTransportables->pluck('pivot', 'id'),
            'selectedTransportableIds' => fn () => $selectedTransportables->pluck('id'),
            'files'                    => fn () => $transportOrder->getGroupedFiles([
                'files',
                'transportInvoiceFiles',
                'cmrWaybillFiles',
                'generatedPickupAuthorizationPdf',
                'generatedTransportRequestOrTransportOrderPdf',
                'generatedStickervelPdf',
            ]),
            'transporters'            => fn () => UserService::getTransportSuppliers($transportOrder->transport_company_id),
            'pickUpAddresses'         => fn () => CompanyService::getCompanyLogisticsAddresses(request()->input('pick_up_company_id', $transportOrder->pick_up_company_id)),
            'deliveryAddresses'       => fn () => CompanyService::getCompanyLogisticsAddresses(request()->input('delivery_company_id', $transportOrder->delivery_company_id)),
            'mainCompanyRoles'        => Inertia::lazy(fn () => (new MultiSelectService(RoleService::getMainCompanyRoles()))->dataForSelect()),
            'mainCompanyUsers'        => fn () => (new MultiSelectService(RoleService::getCompanyMainRolesUsers()))->setTextColumnName('full_name')->dataForSelect(),
            'ownLogisticsAddresses'   => fn () => (new MultiSelectService(auth()->user()->company->logisticsAddresses()))->setTextColumnName('address')->dataForSelect(),
            'pendingOwnerships'       => fn () => OwnershipService::getPending($transportOrder),
            'acceptedOwnership'       => fn () => OwnershipService::getAccepted($transportOrder),
            'pickUpAuthorizationFile' => Inertia::lazy(fn () => $this->service->generateTransportOrderPickUpAuthorizationFile()),
            'workflowProcesses'       => Inertia::lazy(fn () => MultiSelectService::dataForSelectFromArray(ProcessService::getCompanyProcesses()->toArray(), 'className')),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateTransportOrderRequest $request
     * @param  TransportOrder              $transportOrder
     * @return RedirectResponse
     */
    public function update(UpdateTransportOrderRequest $request, TransportOrder $transportOrder): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $this->service
                ->setTransportOrder($transportOrder)
                ->updateTransportOrder($request);

            DB::commit();

            return redirect()->route('transport-orders.edit', ['transport_order' => $transportOrder->id])->with('success', __('The record has been successfully updated.'));
        } catch (Throwable $th) {
            DB::rollBack();

            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error updating record.')]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  TransportOrder   $transportOrder
     * @return RedirectResponse
     */
    public function destroy(TransportOrder $transportOrder): RedirectResponse
    {
        try {
            $transportOrder->delete();

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
            $transportOrder = TransportOrder::findOrFail($validatedRequest['id']);
            $this->authorize('updateStatus', [$transportOrder, $validatedRequest['status']]);

            $this->service
                ->setTransportOrder($transportOrder)
                ->updateTransportOrderStatus($request);

            return redirect()->back()->with('success', __('The record has been successfully updated.'));
        } catch (Throwable $th) {
            DB::rollback();

            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error updating record.')]);
        }
    }

    /**
     * Generate pick up an authorization file.
     *
     * @param  GenerateTransportOrderFileRequest $request
     * @return RedirectResponse
     */
    public function generateFile(GenerateTransportOrderFileRequest $request): RedirectResponse
    {
        try {
            $validatedRequest = $request->validated();

            $transportOrder = TransportOrder::findOrFail($validatedRequest['id']);

            $this->service->setTransportOrder($transportOrder);
            switch ($validatedRequest['type']) {
                case TransportOrderFileType::Transport_Request->value:
                    $this->service->generateTransportRequestOrTransportOrderPdf($validatedRequest['locale'], 'transport-request');

                    break;
                case TransportOrderFileType::Pick_Up_Authorization->value:
                    $this->service->generateTransportOrderPickUpAuthorization($validatedRequest['locale']);

                    break;
                case TransportOrderFileType::Stickervel->value:
                    $this->service->generateStickervel($validatedRequest['locale']);

                    break;
                default:
                    throw new Exception();
            }

            return redirect()->back()->with('success', __('The record has been successfully updated.'));
        } catch (TransportOrderException $e) {
            return redirect()->back()->withErrors([$e->getMessage()]);
        } catch (Throwable $th) {
            DB::rollback();

            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error updating record.')]);
        }
    }
}

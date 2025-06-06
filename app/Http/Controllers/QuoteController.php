<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreQuoteRequest;
use App\Http\Requests\UpdateQuoteRequest;
use App\Http\Requests\UpdateStatusRequest;
use App\Models\Quote;

use App\Models\SalesOrder;
use App\Models\UserGroup;
use App\Services\AdditionalServiceService;
use App\Services\CompanyService;
use App\Services\ItemService;
use App\Services\MultiSelectService;
use App\Services\OwnershipService;
use App\Services\QuoteService;
use App\Services\RoleService;
use App\Services\ServiceLevelService;
use App\Services\UserService;
use App\Services\Vehicles\SystemVehicleService;
use App\Services\Workflow\ProcessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class QuoteController extends Controller
{
    private QuoteService $service;

    /**
     * Handle policy authorization and service initialization.
     */
    public function __construct()
    {
        $this->authorizeResource(Quote::class);
        $this->service = new QuoteService();
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        return Inertia::render('quotes/Index', [
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
        return Inertia::render('quotes/Create', [
            'dataTable'            => fn () => $this->service->getVehicleDataTable(),
            'queryVehicleId'       => fn () => $this->service->getQueryVehicleId(),
            'companies'            => fn () => CompanyService::getCrmCompanies(),
            'mainCompanyRoles'     => Inertia::lazy(fn () => (new MultiSelectService(RoleService::getMainCompanyRoles()))->dataForSelect()),
            'mainCompanyUsers'     => fn () => (new MultiSelectService(RoleService::getCompanyMainRolesUsers()))->setTextColumnName('full_name')->dataForSelect(),
            'customers'            => Inertia::lazy(fn () => UserService::getCustomers()),
            'serviceLevels'        => fn () => ServiceLevelService::getCompanyServiceLevels(),
            'resetServiceLevels'   => Inertia::lazy(fn () => ServiceLevelService::shouldResetServiceLevels()),
            'serviceLevelDefaults' => Inertia::lazy(fn () => ServiceLevelService::getServiceLevelDefaults()),
            'vehicleInformation'   => Inertia::lazy(fn () => SystemVehicleService::getVehicleInformation()),
            'items'                => Inertia::lazy(fn () => ItemService::getByServiceLevel()),
            'levelServices'        => Inertia::lazy(fn () => AdditionalServiceService::getByServiceLevel()),
            'workflowProcesses'    => Inertia::lazy(fn () => MultiSelectService::dataForSelectFromArray(ProcessService::getCompanyProcesses()->toArray(), 'className')),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreQuoteRequest $request
     * @return RedirectResponse
     */
    public function store(StoreQuoteRequest $request): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $this->service->createQuote($request);

            DB::commit();

            return redirect()->route('quotes.edit', ['quote' => $this->service->getQuote()->id])->with('success', __('The record has been successfully created.'));
        } catch (Throwable $th) {
            DB::rollBack();

            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error creating record.')]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Quote    $quote
     * @return Response
     */
    public function edit(Quote $quote): Response
    {
        $quote->load([
            'ownerships.user:id,full_name',
            'customer:id,full_name,company_id',
            'salesOrder' => function ($query) {
                $query->withTrashed()->select('id', 'deleted_at');
            },
            'salesOrder.documents' => function ($query) {
                $query->withTrashed()->select('id', 'documents.documentable_type');
            },
            'vehicles' => function ($query) {
                $query->withTrashed()
                    ->select('id', 'make_id', 'vehicle_model_id', 'variant_id', 'engine_id', 'fuel', 'transmission', 'color_type', 'kw', 'hp', 'kilometers', 'image_path');
            },
            'vehicles.workOrder' => function ($query) {
                $query->withTrashed()->select('id', 'vehicleable_type', 'vehicleable_id', 'deleted_at');
            },
            'vehicles.transportOrders' => function ($query) {
                $query->withTrashed()->select('id', 'transportable_type', 'deleted_at');
            },
            'vehicles.purchaseOrder' => function ($query) {
                $query->withTrashed()->select('id', 'deleted_at');
            },
            'vehicles.documents' => function ($query) {
                $query->withTrashed()->select('id', 'deleted_at');
            },
            'vehicles.calculation',
            'vehicles.make:id,name',
            'vehicles.vehicleModel:id,name',
            'vehicles.variant:id,name',
            'vehicles.engine:id,name',
            'quoteInvitations' => function ($query) {
                $query->orderBy('status', 'desc');
            },
            'quoteInvitations.customer:id,company_id,full_name,email,mobile',
            'files', 'mails', 'statuses', 'internalRemarks',
        ]);

        $this->service->setQuote($quote);

        return Inertia::render('quotes/Edit', [
            'quote'                => fn () => $quote,
            'dataTable'            => fn () => $this->service->getVehicleDataTable(),
            'companies'            => fn () => CompanyService::getCrmCompanies(),
            'selectedVehicles'     => fn () => $this->service->getSelectedVehicles()->pluck('pivot', 'id'),
            'mainCompanyRoles'     => Inertia::lazy(fn () => (new MultiSelectService(RoleService::getMainCompanyRoles()))->dataForSelect()),
            'mainCompanyUsers'     => fn () => (new MultiSelectService(RoleService::getCompanyMainRolesUsers()))->setTextColumnName('full_name')->dataForSelect(),
            'pendingOwnerships'    => fn () => OwnershipService::getPending($quote),
            'acceptedOwnership'    => fn () => OwnershipService::getAccepted($quote),
            'customers'            => fn () => UserService::getCustomers($quote->customer?->company_id),
            'allCustomers'         => fn () => UserService::getAllCustomers(),
            'userGroups'           => fn () => (new MultiSelectService(UserGroup::inThisCompany()))->dataForSelect(),
            'serviceLevels'        => fn () => ServiceLevelService::getCompanyServiceLevels($quote->customer_company_id),
            'resetServiceLevels'   => Inertia::lazy(fn () => ServiceLevelService::shouldResetServiceLevels($quote)),
            'serviceLevelDefaults' => Inertia::lazy(fn () => ServiceLevelService::getServiceLevelDefaults()),
            'vehicleInformation'   => Inertia::lazy(fn () => SystemVehicleService::getVehicleInformation()),
            'items'                => fn () => ItemService::getByServiceLevel($quote->orderItems()),
            'levelServices'        => fn () => AdditionalServiceService::getByServiceLevel($quote->orderServices()),
            'previewPdfUrl'        => Inertia::lazy(fn () => $this->service->generatePreviewPdf()),
            'workflowProcesses'    => Inertia::lazy(fn () => MultiSelectService::dataForSelectFromArray(ProcessService::getCompanyProcesses()->toArray(), 'className')),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateQuoteRequest $request
     * @param  Quote              $quote
     * @return RedirectResponse
     */
    public function update(UpdateQuoteRequest $request, Quote $quote): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $quoteService = $this->service
                ->setQuote($quote)
                ->updateQuote($request);

            DB::commit();

            return redirect()->route('quotes.edit', ['quote' => $quoteService->getQuote()->id])->with('success', __('The record has been successfully created.'));
        } catch (Throwable $th) {
            DB::rollBack();

            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error updating record.')]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Quote            $quote
     * @return RedirectResponse
     */
    public function destroy(Quote $quote): RedirectResponse
    {
        try {
            $quote->delete();

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
            $quote = Quote::findOrFail($validatedRequest['id']);
            $this->authorize('updateStatus', [$quote, $validatedRequest['status']]);

            $this->service->setQuote($quote)
                ->updateStatus($validatedRequest['status']);

            return redirect()->back()->with('success', __('The record has been successfully updated.'));
        } catch (Throwable $th) {
            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error updating record.')]);
        }
    }

    /**
     * Reserve the quote for specific customer until specific time,
     * then it should automatically change status to not be reserved.
     *
     * @param  Request          $request
     * @param  Quote            $quote
     * @return RedirectResponse
     */
    public function reserve(Request $request, Quote $quote): RedirectResponse
    {
        $this->authorize('reserve', $quote);

        try {

            $this->service->reserveQuote($request, $quote);

            return redirect()->back()->with('success', __('The record has been successfully updated.'));
        } catch (Throwable $th) {
            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error updating record.')]);
        }
    }

    public function cancelReservation(Request $request, Quote $quote): RedirectResponse
    {
        $this->authorize('reserve', $quote);

        try {
            $this->service->cancelReservation($request, $quote);

            return redirect()->back()->with('success', __('The record has been successfully updated.'));
        } catch (Throwable $th) {
            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error updating record.')]);
        }
    }

    public function acceptCustomer(Request $request, Quote $quote): RedirectResponse
    {
        $this->authorize('update', $quote);

        try {
            $this->service->updateCustomer($request, $quote);

            return redirect()->back()->with('success', __('The record has been successfully updated.'));
        } catch (Throwable $th) {
            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error updating record.')]);
        }
    }

    /**
     * Create sales order from quote.
     *
     * @param  Quote            $quote
     * @return RedirectResponse
     */
    public function storeSalesOrder(Quote $quote): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $this->authorize('create', SalesOrder::class);
            $salesOrder = $this->service->setQuote($quote)->createSalesOrder();

            DB::commit();

            return redirect()->route('sales-orders.edit', ['sales_order' => $salesOrder->id])->with('success', __('The record has been successfully created.'));
        } catch (Throwable $th) {
            DB::rollBack();

            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error creating record.')]);
        }
    }

    /**
     * Duplicate the quote.
     *
     * @param  Quote            $quote
     * @return RedirectResponse
     */
    public function duplicate(Quote $quote): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $this->authorize('duplicate', $quote);
            $this->service->setQuote($quote)->duplicate();

            DB::commit();

            return redirect()->back()->with('success', __('The record has been successfully duplicated.'));
        } catch (Throwable $th) {
            DB::rollBack();

            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error duplicating record.')]);
        }
    }
}

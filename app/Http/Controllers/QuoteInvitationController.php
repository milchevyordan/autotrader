<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\QuoteAlreadyAcceptedException;
use App\Http\Requests\StoreQuoteInvitationRequest;
use App\Models\QuoteInvitation;
use App\Models\Vehicle;
use App\Services\QuoteInvitationService;
use App\Services\ServiceLevelService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class QuoteInvitationController extends Controller
{
    private QuoteInvitationService $service;

    /**
     * Handle policy authorization and service initialization.
     */
    public function __construct()
    {
        $this->authorizeResource(QuoteInvitation::class);
        $this->service = new QuoteInvitationService();
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(): Response
    {
        return Inertia::render('quote-invitations/Index', [
            'dataTable' => fn () => $this->service->getIndexMethodTable()->run(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreQuoteInvitationRequest $request
     * @return RedirectResponse
     */
    public function store(StoreQuoteInvitationRequest $request): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $this->service->createQuoteInvitation($request);

            DB::commit();

            return redirect()->back()->with('success', __('The record has been successfully created.'));
        } catch (Throwable $th) {
            DB::rollBack();

            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error creating record.')]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  QuoteInvitation $quoteInvitation
     * @return Response
     */
    public function show(QuoteInvitation $quoteInvitation): Response
    {
        $quoteInvitation->load([
            'customer:id,full_name,company_id',
            'quote' => function ($query) {
                $query->withTrashed()->select('id', 'status', 'discount_in_output', 'calculation_on_quote', 'discount', 'total_sales_price_service_items', 'service_level_id', 'is_brutto', 'calculation_on_quote', 'total_fee_intermediate_supplier', 'total_sales_price_exclude_vat', 'total_vat', 'total_sales_price_include_vat', 'total_bpm', 'total_quote_price_exclude_vat', 'total_quote_price', 'reservation_customer_id', 'total_registration_fees', 'reservation_until');
            },
            'quote.vehicles' => function ($vehicleQuery) {
                $vehicleQuery
                    ->withTrashed()
                    ->withPivot('delivery_week')
                    ->select(Vehicle::$summarySelectFields);
            },
            'quote.vehicles.calculation',
            'quote.vehicles.creator',
            'quote.vehicles.make:id,name',
            'quote.vehicles.vehicleModel:id,name',
            'quote.vehicles.variant:id,name',
            'quote.vehicles.engine:id,name',
            'quote.orderItems',
            'quote.orderServices',
            'quote.serviceLevel:id,name',
        ]);

        $this->service->setQuoteInvitation($quoteInvitation);

        return Inertia::render('quote-invitations/Show', [
            'quoteInvitation'        => $quoteInvitation,
            'allItemsAndAdditionals' => ServiceLevelService::checkAllItemsAndAdditionalsInOutput($quoteInvitation->quote),
        ]);
    }

    /**
     * Accept the quote invitation and reserves the quote.
     *
     * @param  int                         $id
     * @return Redirector|RedirectResponse
     * @throws AuthorizationException
     */
    public function accept(int $id): Redirector|RedirectResponse
    {
        DB::beginTransaction();

        try {
            $quoteInvitation = QuoteInvitation::findOrFail($id);
            $this->authorize('accept', $quoteInvitation);

            $this->service->setQuoteInvitation($quoteInvitation)->accept();

            DB::commit();

            return redirect()->back()->with('success', __('Accepted successfully.'));
        } catch (QuoteAlreadyAcceptedException $th) {
            DB::rollBack();

            return redirect()->back()->withErrors([__($th->getMessage())]);
        } catch (Throwable $th) {
            DB::rollBack();

            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error accepting.')]);
        }
    }

    /**
     * Accept the quote invitation and reserves the quote.
     *
     * @param  int                         $id
     * @return Redirector|RedirectResponse
     * @throws AuthorizationException
     */
    public function reject(int $id): Redirector|RedirectResponse
    {
        DB::beginTransaction();

        try {
            $quoteInvitation = QuoteInvitation::findOrFail($id);
            $this->authorize('reject', $quoteInvitation);

            $this->service->setQuoteInvitation($quoteInvitation)->reject();

            DB::commit();

            return redirect()->back()->with('success', __('Rejected successfully.'));
        } catch (Throwable $th) {
            DB::rollBack();

            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error rejecting.')]);
        }
    }

    /**
     * Redirect the customer to the invitation.
     *
     * @param  int                    $quoteId
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function invitation(int $quoteId): RedirectResponse
    {
        $quoteInvitation = QuoteInvitation::where('quote_id', $quoteId)->where('customer_id', auth()->id())->first();
        if (! $quoteInvitation) {
            abort(404);
        }

        return redirect()->route('quote-invitations.show', ['quote_invitation' => $quoteInvitation]);
    }
}

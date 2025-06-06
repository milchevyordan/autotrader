<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\OwnershipCancelledException;
use App\Models\Ownership;
use App\Services\DataTable\RawOrdering;
use App\Services\OwnershipService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class OwnershipController extends Controller
{
    private OwnershipService $service;

    public function __construct()
    {
        $this->authorizeResource(Ownership::class);
        $this->service = new OwnershipService();
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(): Response
    {
        $pendingOwnershipIds = auth()->user()->pendingOwnerships()->pluck('id')->toArray();

        $dataTable = $this->service->getIndexMethodTable();

        if ($pendingOwnershipIds) {
            $dataTable->setRawOrdering(new RawOrdering('FIELD(ownerships.id, '.implode(',', $pendingOwnershipIds).') DESC'));
        }

        return Inertia::render('ownerships/Index', [
            'dataTable'           => fn () => $dataTable->run(),
            'pendingOwnershipIds' => fn () => $pendingOwnershipIds,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  Ownership $ownership
     * @return Response
     */
    public function show(Ownership $ownership): Response
    {
        return Inertia::render('ownerships/Show', [
            'ownership' => fn () => $this->service->getOwnershipRelations($ownership),
        ]);
    }

    /**
     * Accept the quote invitation and reserves the quote.
     *
     * @param  Ownership                   $ownership
     * @return Redirector|RedirectResponse
     */
    public function accept(Ownership $ownership): Redirector|RedirectResponse
    {
        DB::beginTransaction();

        try {
            $this->authorize('accept', $ownership);

            $this->service->accept($ownership);

            DB::commit();

            return redirect()->back()->with('success', __('Accepted successfully.'));
        } catch (OwnershipCancelledException $th) {
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
     * @param  Ownership                   $ownership
     * @return Redirector|RedirectResponse
     */
    public function reject(Ownership $ownership): Redirector|RedirectResponse
    {
        DB::beginTransaction();

        try {
            $this->authorize('reject', $ownership);

            $this->service->reject($ownership);

            DB::commit();

            return redirect()->back()->with('success', __('Rejected successfully.'));
        } catch (OwnershipCancelledException $th) {
            DB::rollBack();

            return redirect()->back()->withErrors([__($th->getMessage())]);
        } catch (Throwable $th) {
            DB::rollBack();

            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error rejecting.')]);
        }
    }
}

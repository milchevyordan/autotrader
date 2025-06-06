<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreVariantRequest;
use App\Http\Requests\UpdateVariantRequest;
use App\Models\Variant;
use App\Services\DataTable\DataTable;
use App\Services\MakeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class VariantController extends Controller
{
    /**
     * Handle policy authorization.
     */
    public function __construct()
    {
        $this->authorizeResource(Variant::class);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(): Response
    {
        $dataTable = (new DataTable(
            Variant::inThisCompany()->select(Variant::$defaultSelectFields)
        ))
            ->setRelation('make', ['id', 'name'])
            ->setRelation('creator')
            ->setColumn('action', __('Action'), exportable: false)
            ->setColumn('id', '#', true, true)
            ->setColumn('creator.full_name', __('Creator'), true)
            ->setColumn('make.name', __('Make'), true, true)
            ->setColumn('name', __('Variant'), true, true)
            ->setTimestamps()
            ->run();

        return Inertia::render('variants/Index', [
            'make'      => fn () => MakeService::getMakes(),
            'dataTable' => fn () => $dataTable,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreVariantRequest $request
     * @return RedirectResponse
     */
    public function store(StoreVariantRequest $request): RedirectResponse
    {
        $variant = new Variant();
        $variant->fill($request->validated());
        $variant->creator_id = auth()->id();

        if ($variant->save()) {
            return redirect()->back()->with('success', __('The record has been successfully created.'));
        }

        return redirect()->back()->withErrors([__('Error creating record.')]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateVariantRequest $request
     * @param  Variant              $variant
     * @return RedirectResponse
     */
    public function update(UpdateVariantRequest $request, Variant $variant): RedirectResponse
    {
        if ($variant->update($request->validated())) {
            return redirect()->back()->with('success', __('The record has been successfully updated.'));
        }

        return redirect()->back()->withErrors([__('Error updating record.')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Variant          $variant
     * @return RedirectResponse
     */
    public function destroy(Variant $variant): RedirectResponse
    {
        try {
            $variant->delete();

            return redirect()->back()->with('success', __('The record has been successfully deleted.'));
        } catch (Throwable $th) {
            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error deleting record.')]);
        }
    }
}

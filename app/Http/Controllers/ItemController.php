<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ItemType;
use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\UpdateItemRequest;
use App\Models\Item;
use App\Services\DataTable\DataTable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class ItemController extends Controller
{
    /**
     * Handle policy authorization.
     */
    public function __construct()
    {
        $this->authorizeResource(Item::class);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(): Response
    {
        $dataTable = (new DataTable(
            Item::inThisCompany()
        ))
            ->setRelation('creator')
            ->setColumn('action', __('Action'), exportable: false)
            ->setColumn('id', '#', true, true)
            ->setColumn('creator.full_name', __('Creator'), true)
            ->setColumn('type', __('Type'), true, true)
            ->setColumn('shortcode', __('Shortcode'), true, true)
            ->setColumn('purchase_price', __('Purchase Price'), true, true)
            ->setColumn('sale_price', __('Sale Price'), true, true)
            ->setColumn('sale_price', __('Sale Price'), true, true)
            ->setTimestamps()
            ->setEnumColumn('type', ItemType::class)
            ->setPriceColumn('purchase_price')
            ->setPriceColumn('sale_price')
            ->run();

        return Inertia::render('items/Index', compact('dataTable'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreItemRequest $request
     * @return RedirectResponse
     */
    public function store(StoreItemRequest $request): RedirectResponse
    {
        $item = new Item();
        $item->fill($request->validated());
        $item->creator_id = auth()->id();

        if ($item->save()) {
            return redirect()->back()->with('success', __('The record has been successfully created.'));
        }

        return redirect()->back()->withErrors([__('Error creating record')]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateItemRequest $request
     * @param  Item              $item
     * @return RedirectResponse
     */
    public function update(UpdateItemRequest $request, Item $item): RedirectResponse
    {
        if ($item->update($request->validated())) {
            return redirect()->back()->with('success', __('The record has been successfully updated.'));
        }

        return redirect()->back()->withErrors([__('Error updating record.')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Item             $item
     * @return RedirectResponse
     */
    public function destroy(Item $item): RedirectResponse
    {
        try {
            $item->delete();

            return redirect()->back()->with('success', __('The record has been successfully deleted.'));
        } catch (Throwable $th) {
            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error deleting record.')]);
        }
    }
}

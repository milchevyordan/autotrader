<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreMakeRequest;
use App\Http\Requests\UpdateMakeRequest;
use App\Models\Make;
use App\Services\DataTable\DataTable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class MakeController extends Controller
{
    /**
     * Handle policy authorization.
     */
    public function __construct()
    {
        $this->authorizeResource(Make::class);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(): Response
    {
        $dataTable = (new DataTable(
            Make::inThisCompany()->select(Make::$defaultSelectFields)
        ))
            ->setRelation('creator')
            ->setColumn('action', __('Action'), exportable: false)
            ->setColumn('id', '#', true, true)
            ->setColumn('creator.full_name', __('Creator'), true)
            ->setColumn('name', __('Make'), true, true)
            ->setTimestamps()
            ->run();

        return Inertia::render('makes/Index', compact('dataTable'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreMakeRequest $request
     * @return RedirectResponse
     */
    public function store(StoreMakeRequest $request): RedirectResponse
    {
        $make = new Make();
        $make->fill($request->validated());
        $make->creator_id = auth()->id();

        if ($make->save()) {
            return redirect()->back()->with('success', __('The record has been successfully created.'));
        }

        return redirect()->back()->withErrors([__('Error creating record.')]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateMakeRequest $request
     * @param  Make              $make
     * @return RedirectResponse
     */
    public function update(UpdateMakeRequest $request, Make $make): RedirectResponse
    {
        if ($make->update($request->validated())) {
            return redirect()->back()->with('success', __('The record has been successfully updated.'));
        }

        return redirect()->back()->withErrors([__('Error updating record.')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Make             $make
     * @return RedirectResponse
     */
    public function destroy(Make $make): RedirectResponse
    {
        try {
            $make->delete();

            return redirect()->back()->with('success', __('The record has been successfully deleted.'));
        } catch (Throwable $th) {
            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error deleting record.')]);
        }
    }
}

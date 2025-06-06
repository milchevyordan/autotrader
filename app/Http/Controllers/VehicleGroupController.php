<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreVehicleGroupRequest;
use App\Http\Requests\UpdateVehicleGroupRequest;
use App\Models\VehicleGroup;
use App\Services\DataTable\Paginator;
use App\Services\VehicleGroupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class VehicleGroupController extends Controller
{
    private VehicleGroupService $service;

    /**
     * Handle policy authorization and service initialization.
     */
    public function __construct()
    {
        $this->authorizeResource(VehicleGroup::class);
        $this->service = new VehicleGroupService();
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(): Response
    {
        $expandGroupService = $this->service->getExpandGroupService();

        return Inertia::render('vehicle-groups/Index', [
            'paginator'     => new Paginator($expandGroupService->getExpandedGroupPaginator()),
            'vehicleGroups' => $expandGroupService->getExpandedGroupPaginator()->items(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreVehicleGroupRequest $request
     * @return RedirectResponse
     */
    public function store(StoreVehicleGroupRequest $request): RedirectResponse
    {
        $vehicleGroup = new VehicleGroup();
        $vehicleGroup->fill($request->validated());
        $vehicleGroup->creator_id = auth()->id();

        if ($vehicleGroup->save()) {
            return redirect()->back()->with('success', __('The record has been successfully created.'));
        }

        return redirect()->back()->withErrors([__('Error creating record.')]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateVehicleGroupRequest $request
     * @param  VehicleGroup              $vehicleGroup
     * @return RedirectResponse
     */
    public function update(UpdateVehicleGroupRequest $request, VehicleGroup $vehicleGroup): RedirectResponse
    {
        if ($vehicleGroup->update($request->validated())) {
            return back()->with('success', __('The record has been successfully updated.'));
        }

        return redirect()->back()->withErrors([__('Error updating  record.')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  VehicleGroup     $vehicleGroup
     * @return RedirectResponse
     */
    public function destroy(VehicleGroup $vehicleGroup): RedirectResponse
    {
        try {
            $vehicleGroup->delete();

            return redirect()->back()->with('success', __('The record has been successfully deleted.'));
        } catch (Throwable $th) {
            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error deleting record.')]);
        }
    }
}

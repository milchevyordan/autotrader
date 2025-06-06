<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreVehicleModelRequest;
use App\Http\Requests\UpdateVehicleModelRequest;
use App\Models\VehicleModel;
use App\Services\DataTable\DataTable;
use App\Services\MakeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class VehicleModelController extends Controller
{
    /**
     * Handle policy authorization.
     */
    public function __construct()
    {
        $this->authorizeResource(VehicleModel::class);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(): Response
    {
        $dataTable = (new DataTable(
            VehicleModel::inThisCompany()->select(VehicleModel::$defaultSelectFields)
        ))
            ->setRelation('make', ['id', 'name'])
            ->setRelation('creator')
            ->setColumn('action', __('Action'), exportable: false)
            ->setColumn('id', '#', true, true)
            ->setColumn('creator.full_name', __('Creator'), true)
            ->setColumn('make.name', __('Make'), true, true)
            ->setColumn('name', __('Model'), true, true)
            ->setTimestamps()
            ->run();

        return Inertia::render('vehicle-models/Index', [
            'dataTable' => fn () => $dataTable,
            'make'      => fn () => MakeService::getMakes(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreVehicleModelRequest $request
     * @return RedirectResponse
     */
    public function store(StoreVehicleModelRequest $request): RedirectResponse
    {
        $vehicleModel = new VehicleModel();
        $vehicleModel->fill($request->validated());
        $vehicleModel->creator_id = auth()->id();

        if ($vehicleModel->save()) {
            return redirect()->back()->with('success', __('The record has been successfully created.'));
        }

        return redirect()->back()->withErrors([__('Error creating record.')]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateVehicleModelRequest $request
     * @param  VehicleModel              $vehicleModel
     * @return RedirectResponse
     */
    public function update(UpdateVehicleModelRequest $request, VehicleModel $vehicleModel): RedirectResponse
    {
        if ($vehicleModel->update($request->validated())) {
            return redirect()->back()->with('success', __('The record has been successfully updated.'));
        }

        return redirect()->back()->withErrors([__('Error updating record.')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  VehicleModel     $vehicleModel
     * @return RedirectResponse
     */
    public function destroy(VehicleModel $vehicleModel): RedirectResponse
    {
        try {
            $vehicleModel->delete();

            return redirect()->back()->with('success', __('The record has been successfully deleted.'));
        } catch (Throwable $th) {
            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error deleting record.')]);
        }
    }
}

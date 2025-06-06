<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\WorkOrderTaskStatus;
use App\Events\AssignedToWorkOrderTask;
use App\Http\Requests\StoreWorkOrderTaskRequest;
use App\Http\Requests\UpdateWorkOrderTaskRequest;
use App\Models\WorkOrderTask;
use App\Services\Files\UploadHelper;
use App\Services\WorkOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class WorkOrderTaskController extends Controller
{
    /**
     * Handle policy authorization.
     */
    public function __construct()
    {
        $this->authorizeResource(WorkOrderTask::class);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreWorkOrderTaskRequest $request
     * @return RedirectResponse
     */
    public function store(StoreWorkOrderTaskRequest $request): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $workOrderTask = new WorkOrderTask();
            $validatedRequest = $request->validated();
            $workOrderTask->fill($validatedRequest);
            $workOrderTask->creator_id = auth()->id();
            if ($validatedRequest['status'] == WorkOrderTaskStatus::Completed->value) {
                $workOrderTask->completed_at = now();
            }
            $workOrderTask->save();

            if ($validatedRequest['assigned_to_id']) {
                event(new AssignedToWorkOrderTask($workOrderTask));
            }

            $uploadedFiles = UploadHelper::uploadMultipleFiles($validatedRequest, 'files');
            $uploadedImages = UploadHelper::uploadMultipleImages($validatedRequest, 'images');

            $workOrderTask->saveWithFiles($uploadedFiles, 'files');
            $workOrderTask->saveWithImages($uploadedImages, 'images');

            (new WorkOrderService())->setWorkOrder($workOrderTask->workOrder)->workOrderTaskUpdated();

            DB::commit();

            return redirect()->back()->with('success', __('The record has been successfully created.'));
        } catch (Throwable $th) {
            DB::rollBack();

            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error creating record.')]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateWorkOrderTaskRequest $request
     * @param  WorkOrderTask              $workOrderTask
     * @return RedirectResponse
     */
    public function update(UpdateWorkOrderTaskRequest $request, WorkOrderTask $workOrderTask): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $assignedToIdBeforeUpdate = $workOrderTask->assigned_to_id;

            $validatedRequest = $request->validated();
            if ($validatedRequest['status'] == WorkOrderTaskStatus::Completed->value) {
                $workOrderTask->completed_at = now();
            }
            $workOrderTask->update($validatedRequest);

            $uploadedFiles = UploadHelper::uploadMultipleFiles($validatedRequest, 'files');
            $uploadedImages = UploadHelper::uploadMultipleImages($validatedRequest, 'images');

            $workOrderTask->saveWithFiles($uploadedFiles, 'files');
            $workOrderTask->saveWithImages($uploadedImages, 'images');

            if ($validatedRequest['assigned_to_id'] && ($validatedRequest['assigned_to_id'] != $assignedToIdBeforeUpdate)) {
                event(new AssignedToWorkOrderTask($workOrderTask));
            }

            (new WorkOrderService())->setWorkOrder($workOrderTask->workOrder)->workOrderTaskUpdated();

            DB::commit();

            return redirect()->back()->with('success', __('The record has been successfully updated.'));
        } catch (Throwable $th) {
            DB::rollBack();

            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error updating record.')]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  WorkOrderTask    $workOrderTask
     * @return RedirectResponse
     */
    public function destroy(WorkOrderTask $workOrderTask): RedirectResponse
    {
        try {
            $workOrder = $workOrderTask->workOrder;
            $workOrderTask->delete();

            (new WorkOrderService())->setWorkOrder($workOrder)->workOrderTaskUpdated();

            return redirect()->back()->with('success', __('The record has been successfully deleted.'));
        } catch (Throwable $th) {
            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error deleting record.')]);
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreWorkflowFinishedStepRequest;
use App\Http\Requests\UpdateWorkflowFinishedStepRequest;
use App\Models\WorkflowFinishedStep;
use App\Services\Files\UploadHelper;
use App\Services\Workflow\WorkflowFinishedStepService;
use App\Services\Workflow\WorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class WorkflowFinishedStepController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreWorkflowFinishedStepRequest $request
     * @return RedirectResponse
     */
    public function store(StoreWorkflowFinishedStepRequest $request): RedirectResponse
    {
        $workflowStep = new WorkflowFinishedStep();
        $validatedRequest = $request->validated();

        $workflowStep->fill($validatedRequest);
        $workflowStep->creator_id = Auth::id();

        $uploadedFiles = UploadHelper::uploadMultipleFiles($validatedRequest, 'files');

        if ($workflowStep->saveWithFiles($uploadedFiles)) {
            return redirect()->back()->with('success', __('The record has been successfully created.'));
        }

        return redirect()->back()->withErrors([__('Error creating record.')]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateWorkflowFinishedStepRequest $request
     * @param  WorkflowFinishedStep              $workflowFinishedStep
     * @return RedirectResponse
     * @throws Throwable
     */
    public function update(UpdateWorkflowFinishedStepRequest $request, WorkflowFinishedStep $workflowFinishedStep): RedirectResponse
    {
        $validatedRequest = $request->validated();
        $uploadedFiles = UploadHelper::uploadMultipleFiles($validatedRequest, 'files');
        $update = $workflowFinishedStep->update($validatedRequest);

        if ($uploadedFiles) {
            $workflowFinishedStep->saveWithFiles($uploadedFiles);
        }
        if ($update) {
            return redirect()->back()->with('success', __('The record has been successfully updated.'));
        }

        return redirect()->back()->withErrors([__('Error updating record.')]);
    }

    public function upsert(Request $request, WorkflowFinishedStepService $workflowFinishedStepService): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $nameSpace = WorkflowService::getWorkflowCompanyNameSpace(Auth::user()?->company).'\\Steps\\'.$request->class_name;

            $action = $workflowFinishedStepService->upsertWorkflowStep($request, $nameSpace);

            DB::commit();

            return redirect()->back()->with('success', __("The record has been successfully {$action}."));
        } catch (Throwable $th) {
            DB::rollBack();
            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error processing record.')]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  WorkflowFinishedStep        $workflowFinishedStep
     * @param  Request                     $request
     * @param  WorkflowFinishedStepService $workflowFinishedStepService
     * @return RedirectResponse
     */
    public function destroy(Request $request, WorkflowFinishedStepService $workflowFinishedStepService): RedirectResponse
    {
        try {
            $workflowFinishedStepService->deleteWorkflowStep($request);

            return redirect()->back()->with('success', __('The record has been successfully deleted.'));
        } catch (Throwable $th) {
            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error deleting record.')]);
        }
    }
}

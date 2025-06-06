<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreWorkflowRequest;
use App\Models\Workflow;
use App\Services\Vehicles\SystemVehicleService;
use App\Services\Workflow\WorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class WorkflowController extends Controller
{
    private WorkflowService $workflowService;

    /**
     * Handle policy authorization and service initialization.
     */
    public function __construct()
    {
        $this->authorizeResource(Workflow::class);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreWorkflowRequest $request
     * @param  WorkflowService      $workflowService
     * @return RedirectResponse
     */
    public function store(StoreWorkflowRequest $request, WorkflowService $workflowService): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $workflow = $workflowService->createWorkflow($request);

            DB::commit();

            return redirect()
                ->route('workflows.show', ['workflow' => $workflow->id])
                ->with('success', __('The record has been successfully created.'));
        } catch (Throwable $e) {
            $errorMessage = 'Error creating record, please contact the admin';
        }

        DB::rollBack();

        Log::error($e->getMessage(), ['exception' => $e]);

        return redirect()->back()->withErrors([__($errorMessage)]);
    }

    /**
     * Display the specified resource.
     *
     * @param  Workflow        $workflow
     * @param  WorkflowService $workflowService
     * @return Response
     */
    public function show(Workflow $workflow, WorkflowService $workflowService): Response
    {
        $workflowComponent = $workflowService->getWorkflow($workflow);

        return Inertia::render('workflows/Show', [
            'workflow'             => fn () => $workflowComponent,
            'transferVehicleToken' => Inertia::lazy(fn () => (new SystemVehicleService)->setVehicle($workflowComponent->vehicle)->getTransferVehicleLink()),
        ]);
    }
}

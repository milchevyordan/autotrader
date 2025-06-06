<?php

declare(strict_types=1);

namespace App\Services\Workflow;

use App\Http\Requests\StoreWorkflowRequest;
use App\Models\Company;
use App\Models\ServiceVehicle;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Workflow as ModelsWorkflow;
use App\Services\ConfigurationService;
use App\Services\Workflow\Components\Workflow as ComponentsWorkflow;
use App\Services\Workflow\Exceptions\ClassNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class WorkflowService
{
    public ComponentsWorkflow $workflow;

    private ModelsWorkflow $modelsWorkflow;

    private string $workflowCompanyNameSpace;

    public function __construct()
    {
        // $this->workflowCompanyNameSpace =
    }

    /**
     * Get the value of workflow.
     *
     * @param  ModelsWorkflow     $modelsWorkflow
     * @return ComponentsWorkflow
     */
    public function getWorkflow(ModelsWorkflow $modelsWorkflow): ComponentsWorkflow
    {
        if (empty($this->workflow)) {
            $this->initWorkflow($modelsWorkflow);
        }

        return $this->workflow;
    }

    /**
     * Get the value of workflowCompanyNameSpace.
     *
     * @param  Company $company
     * @return string
     */
    public static function getWorkflowCompanyNameSpace(Company $company): string
    {
        $configurationService = new ConfigurationService();

        $nameSpace = $configurationService->getConfig($company, 'workflowNamespace');

        return "App\\Services\\Workflow\\Companies\\{$nameSpace}";
    }

    /**
     * @param  StoreWorkflowRequest $request
     * @return ModelsWorkflow
     */
    public function createWorkflow(StoreWorkflowRequest $request): ModelsWorkflow
    {
        $validatedRequest = $request->validated();

        $auth = User::where('id', Auth::id())->with([
            'company:id,name',
        ])->first();

        $workflowCompanyNameSpace = self::getWorkflowCompanyNameSpace($auth->company);

        $workflowProcessNameSpace = "{$workflowCompanyNameSpace}\\Processes\\{$request['workflow_process_class']}";

        $workflow = new ModelsWorkflow();
        $workflow->fill($validatedRequest);
        $workflow->workflow_process_namespace = $workflowProcessNameSpace;
        $workflow->creator_id = $auth->id;
        $workflow->save();

        return $workflow;
    }

    /**
     * Get the value of modelsWorkflow.
     *
     * @return ModelsWorkflow
     */
    public function getModelsWorkflow(): ModelsWorkflow
    {
        return $this->modelsWorkflow;
    }

    private function initWorkflow(ModelsWorkflow $modelsWorkflow)
    {
        $images = [];
        $files = [];

        $modelsWorkflow->load([
            'creator:id,full_name,company_id',
            'creator.company' => function ($query) {
                $query->select('id', 'name')->withTrashed();
            },
            'creator.company.configurations',
            'finishedSteps',
            'finishedSteps.files',
            'finishedSteps.images',
        ])
            ->load([
                'vehicleable' => function ($query) use ($modelsWorkflow) {
                    $databaseRelationsInitiator = $this->getDatabaseRelationsInitiatorNameSpace($modelsWorkflow->creator->company);
                    $query->withTrashed();
                    $morphedClassName = key($query->getDictionary());

                    $modelColumns = $databaseRelationsInitiator->getMorphedColumnsToSelect($morphedClassName);
                    $modelRelations = $databaseRelationsInitiator->getMorphedRelations($morphedClassName);

                    if ($modelColumns !== null) {
                        $query->select($modelColumns);
                    }

                    if ($modelRelations !== null) {
                        $query->with($modelRelations);
                    }
                },
            ]);

        $vehicle = $modelsWorkflow->vehicleable;

        switch (get_class($modelsWorkflow->vehicleable)) {
            case Vehicle::class:
                $images = new Collection();
                $files = new Collection();
                $stepImages = new Collection();
                $stepFiles = new Collection();

                foreach ($modelsWorkflow->finishedSteps as $step) {
                    if ($step->images->isNotEmpty()) {
                        $stepImages = $stepImages->merge($step->images);
                    }

                    if ($step->files->isNotEmpty()) {
                        $stepFiles = $stepFiles->merge($step->files);
                    }
                }

                $images->put('stepImages', $stepImages);
                $files->put('stepFiles', $stepFiles);

                $images = $images->merge($vehicle->getGroupedImages(['internalImages', 'externalImages']));
                $files = $files->merge($vehicle->getGroupedFiles(['internalFiles', 'externalFiles']));

                break;
            case ServiceVehicle::class:
                $transportOrders = $vehicle->transportOrders();
        }

        $process = new $modelsWorkflow->workflow_process_namespace($modelsWorkflow);

        $this->workflow = new ComponentsWorkflow(
            vehicle: $vehicle,
            vehicleableType: $vehicle::class,
            id: $modelsWorkflow->id,
            process: $process,
            images: $images,
            files: $files
        );
    }

    private function getDatabaseRelationsInitiatorNameSpace(Company $company): BaseDatabaseRelationsInitiator
    {
        $databaseRelationsInitiatorNameSpace = self::getWorkflowCompanyNameSpace($company).'\\DatabaseRelationsInitiator';

        if (! class_exists($databaseRelationsInitiatorNameSpace)) {
            throw new ClassNotFoundException("The class {$databaseRelationsInitiatorNameSpace} does not exist.");
        }

        return new $databaseRelationsInitiatorNameSpace();
    }
}

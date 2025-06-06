<?php

declare(strict_types=1);

namespace Database\Seeders\Workflow;

use App\Models\WorkflowProcess;
use App\Models\WorkflowStatus;
use App\Models\WorkflowStep;
use App\Models\WorkflowSubprocess;
use Database\Seeders\BaseSeeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class Base extends BaseSeeder
{
    /**
     * The company id 1 or 2.
     *
     * @var int
     */
    private int $companyId;

    /**
     * The structure.
     *
     * @var array|string[]
     */
    private array $structureProps = [
        'processes',
        'subprocesses',
    ];

    /**
     * Detailed data.
     *
     * @var array
     */
    protected array $seedData;

    /**
     * Data that will be seeded.
     *
     * @var array
     */
    protected array $processSubprocessData;

    /**
     * Create a new Base instance.
     *
     * @param int $companyId
     */
    public function __construct(int $companyId)
    {
        parent::__construct();
        $this->companyId = $companyId;
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $processesInsertData = [];
        $subprocessesInsertData = [];
        $statusesInsertData = [];
        $stepsInsertData = [];

        $allSubprocessesSeedData = [];
        $allStatusesSeedData = [];

        // Manage the process seeding
        foreach ($this->seedData['processes'] as $seedProcess) {
            $processesInsertData[] = [
                'company_id'        => $this->companyId,
                'name'              => $seedProcess['name'],
                'variable_map_name' => $seedProcess['variable_map_name'],
            ];
        }
        WorkflowProcess::insert($processesInsertData);

        $insertedProcessesCollection = new Collection(
            WorkflowProcess::select('id', 'name')
                ->whereIn('id', $this->getInsertedIds(WorkflowProcess::class, $processesInsertData))
                ->get()
        );

        foreach ($this->seedData['subprocesses'] as $processKey => $subprocess) {
            $subprocessesInsertData[] = [
                'name'                => $subprocess['name'],
                'icon_component_name' => $subprocess['icon_component_name'],
            ];
        }

        WorkflowSubprocess::insert($subprocessesInsertData);

        // Manage the status seeding
        $companySubprocessIds = $this->getInsertedIds(WorkflowSubprocess::class, $subprocessesInsertData);
        foreach ($companySubprocessIds as $subprocessKey => $subprocessId) {
            foreach ($this->seedData['subprocesses'][$subprocessKey]['statuses'] as $status) {
                $statusesInsertData[] = [
                    'workflow_subprocess_id' => $subprocessId,
                    'name'                   => $status['name'],
                ];

                $allStatusesSeedData[] = $status;
            }
        }
        WorkflowStatus::insert($statusesInsertData);

        // Manage steps seeding
        $companyStatusIds = $this->getInsertedIds(WorkflowStatus::class, $statusesInsertData);
        foreach ($companyStatusIds as $satusKey => $statusId) {
            foreach ($allStatusesSeedData[$satusKey]['steps'] as $step) {
                $stepsInsertData[] = [
                    'workflow_status_id'         => $statusId,
                    'name'                       => $step['name'],
                    'variable_map_name'          => $step['variable_map_name'],
                    'additional_modal_component' => $step['additional_modal_component'],
                ];
            }
        }
        WorkflowStep::insert($stepsInsertData);

        // Attach subprocesses to processes
        $insertedSubprocessesCollection = new Collection(
            WorkflowSubprocess::select('id', 'name')
                ->whereIn('id', $companySubprocessIds)
                ->get()
        );

        $processSubprocessInsertData = [];
        foreach ($this->processSubprocessData as $processName => $subprocessNames) {
            $process = $insertedProcessesCollection->firstWhere('name', $processName);

            foreach ($subprocessNames as $subprocessName) {
                $subprocess = $insertedSubprocessesCollection->firstWhere('name', $subprocessName);

                $processSubprocessInsertData[] = [
                    'workflow_process_id'    => $process['id'],
                    'workflow_subprocess_id' => $subprocess['id'],
                ];
            }
        }

        DB::table('workflow_process_subprocess')->insert($processSubprocessInsertData);
    }

    /**
     * Get an array of inserted IDs for a given model class and seed data.
     *
     * @param  string                   $modelClass       the fully qualified class name of the model
     * @param  array                    $insertedSeedData the array of seed data that has been inserted
     * @return array                    an array containing the range of IDs between the first and last ID of the model
     * @throws InvalidArgumentException if the specified class does not exist
     */
    private function getInsertedIds(string $modelClass, array $insertedSeedData): array
    {
        if (! class_exists($modelClass)) {
            throw new InvalidArgumentException("Class '{$modelClass}' does not exist.");
        }

        $lastId = $modelClass::latest('id')->value('id');
        $firstId = $lastId - count($insertedSeedData) + 1;

        return range($firstId, $lastId);
    }

    /**
     * Generate seed data based on internal class variables.
     *
     * @param  Base  $seedClass
     * @return array
     */
    protected function generateSeedData(self $seedClass): array
    {
        $data = [];
        foreach ($this->structureProps as $propertyName) {
            if (property_exists($seedClass, $propertyName) && is_array($seedClass->{$propertyName})) {
                $data[$propertyName] = $seedClass->{$propertyName};
            }
        }

        return $data;
    }
}

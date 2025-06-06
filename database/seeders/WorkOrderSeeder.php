<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\WorkOrderTaskStatus;
use App\Enums\WorkOrderTaskType;
use App\Enums\WorkOrderType;
use App\Models\ServiceVehicle;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkOrderSeeder extends BaseSeeder
{
    /**
     * Array of vehicle ids.
     *
     * @var array
     */
    private array $vehicleIds;

    /**
     * Array of service vehicle ids.
     *
     * @var array
     */
    private array $serviceVehicleIds;

    /**
     * Create a new WorkOrderSeeder instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Run the seeder.
     */
    public function run(): void
    {
        $workOrderData = [];
        $workOrderTaskData = [];
        $workOrderId = 1;

        foreach ($this->getCompanyCompanyAdministratorIdsMap() as $companyId => $companyAdministratorId) {
            $usersCanCreateWorkOrderIds = User::where('company_id', $companyId)
                ->whereHas('roles.permissions', function ($query) {
                    $query->where('name', 'create-work-order');
                })->get(['id'])->pluck('id')->all();

            if (empty($usersCanCreateWorkOrderIds)) {
                continue;
            }

            $this->vehicleIds = Vehicle::whereHas('creator', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })->get(['id'])->pluck('id')->all();

            $this->serviceVehicleIds = ServiceVehicle::whereHas('creator', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })->get(['id'])->pluck('id')->all();

            for ($i = 0; $i < $this->faker->numberBetween(15, 25); $i++) {
                $type = $this->faker->numberBetween(1, 2);
                $vehicleableData = $this->getWorkorderableData($type);
                if (! $vehicleableData) {
                    continue;
                }

                $userId = $this->faker->randomElement($usersCanCreateWorkOrderIds);
                $totalPrice = 0;
                $status = WorkOrderTaskStatus::Completed->value;

                for ($j = 0; $j < $this->faker->numberBetween(0, 7); $j++) {
                    $price = $this->faker->numberBetween(100, 20000);
                    $statusTask = $this->faker->boolean(70) ? 2 : 1;

                    if (1 == $statusTask) {
                        $status = 1;
                    }

                    $workOrderTaskData[] = $this->createWorkOrderTaskItem($workOrderId, $userId, $price, $statusTask);
                    $totalPrice += $price;
                }

                $workOrderData[] = [
                    'id'               => $workOrderId,
                    'creator_id'       => $userId,
                    'vehicleable_type' => $vehicleableData['vehicleable_type'],
                    'vehicleable_id'   => $vehicleableData['vehicleable_id'],
                    'type'             => $type,
                    'status'           => $status,
                    'total_price'      => 0 == $totalPrice ? null : $totalPrice,
                    'created_at'       => $this->safeDateTimeBetween('-2 years', 'now', 'UTC'),
                    'updated_at'       => now(),
                ];
                $workOrderId++;
            }
        }

        DB::table('work_orders')->insert($workOrderData);
        DB::table('work_order_tasks')->insert($workOrderTaskData);
    }

    /**
     * Return workorderable morphs data based on type.
     *
     * @param             $type
     * @return null|array
     */
    private function getWorkorderableData($type): ?array
    {
        $config = [
            WorkOrderType::Vehicle->value => [
                'ids'  => &$this->vehicleIds,
                'type' => Vehicle::class,
            ],
            WorkOrderType::Service_vehicle->value => [
                'ids'  => &$this->serviceVehicleIds,
                'type' => ServiceVehicle::class,
            ],
        ];

        if (! isset($config[$type]) || empty($config[$type]['ids'])) {
            return null;
        }

        $workorderableId = $this->faker->randomElement($config[$type]['ids']);
        $key = array_search($workorderableId, $config[$type]['ids'], true);
        if ($key !== false) {
            unset($config[$type]['ids'][$key]);
        }

        return [
            'vehicleable_type' => $config[$type]['type'],
            'vehicleable_id'   => $workorderableId,
        ];
    }

    /**
     * Return work order task data ready to be inserted.
     *
     * @param  int   $workOrderId
     * @param  int   $userId
     * @param  int   $price
     * @param  int   $statusTask
     * @return array
     */
    private function createWorkOrderTaskItem(int $workOrderId, int $userId, int $price, int $statusTask): array
    {
        return [
            'work_order_id'   => $workOrderId,
            'creator_id'      => $userId,
            'name'            => $this->faker->name,
            'type'            => $this->faker->randomElement(WorkOrderTaskType::values()),
            'status'          => $statusTask,
            'estimated_price' => $price,
            'actual_price'    => $price,
            'completed_at'    => $statusTask == WorkOrderTaskStatus::Completed->value ? now() : null,
            'created_at'      => $this->safeDateTimeBetween('-2 years', 'now', 'UTC'),
            'updated_at'      => now(),
        ];
    }
}

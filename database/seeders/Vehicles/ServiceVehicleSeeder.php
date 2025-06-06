<?php

declare(strict_types=1);

namespace Database\Seeders\Vehicles;

use App\Models\Make;
use App\Models\Variant;
use App\Models\VehicleModel;
use Illuminate\Support\Facades\DB;

class ServiceVehicleSeeder extends VehicleSeeder
{
    /**
     * Array of vehicle model ids mapped by make.
     *
     * @var array
     */
    private array $modelIdsMapped;

    /**
     * Array of variant ids mapped by make.
     *
     * @var array
     */
    private array $variantIdsMapped;

    /**
     * Create a new serviceVehicleseeder instance.
     */
    public function __construct()
    {
        parent::__construct();

        $this->modelIdsMapped = VehicleModel::mapByColumn('make_id');
        $this->variantIdsMapped = Variant::mapByColumn('make_id');
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $makes = Make::all();

        $serviceVehicleInserts = [];
        foreach ($makes as $make) {
            if (! isset(
                $this->modelIdsMapped[$make->id],
                $this->variantIdsMapped[$make->id])
            ) {
                continue;
            }

            for ($i = 0; $i < $this->faker->numberBetween(70, 150); $i++) {
                $serviceVehicleInserts[] = $this->getVehicleData($make);
            }
        }

        $chunks = array_chunk($serviceVehicleInserts, 500);

        foreach ($chunks as $chunk) {
            DB::table('service_vehicles')->insert($chunk);
        }
    }

    /**
     * Return array of service vehicle data ready to insert.
     *
     * @param        $make
     * @return array
     */
    private function getVehicleData($make): array
    {
        return [
            'creator_id'              => $make->creator_id,
            'make_id'                 => $make->id,
            'vehicle_model_id'        => $this->faker->randomElement($this->modelIdsMapped[$make->id]),
            'current_registration'    => $this->faker->numberBetween(1, 20),
            'vehicle_type'            => $this->faker->numberBetween(1, 3),
            'co2_type'                => $this->faker->numberBetween(1, 2),
            'co2_value'               => $this->faker->numberBetween(100, 20000),
            'kilometers'              => $this->faker->numberBetween(0, 200000),
            'vin'                     => $this->faker->regexify('[A-HJ-NPR-Z0-9]{17}'),
            'first_registration_date' => $this->safeDateTimeBetween('-2 years', 'now', 'UTC'),
            'created_at'              => $this->safeDateTimeBetween('-2 years', 'now', 'UTC'),
        ];
    }
}

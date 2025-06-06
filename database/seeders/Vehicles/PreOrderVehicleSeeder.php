<?php

declare(strict_types=1);

namespace Database\Seeders\Vehicles;

use App\Enums\Airconditioning;
use App\Enums\AppConnect;
use App\Enums\Camera;
use App\Enums\ColorType;
use App\Enums\CruiseControl;
use App\Enums\DigitalCockpit;
use App\Enums\ExteriorColour;
use App\Enums\Headlights;
use App\Enums\InteriorColour;
use App\Enums\InteriorMaterial;
use App\Enums\KeylessEntry;
use App\Enums\Navigation;
use App\Enums\Optics;
use App\Enums\Panorama;
use App\Enums\PDC;
use App\Enums\SeatHeating;
use App\Enums\SeatMassage;
use App\Enums\SeatsElectricallyAdjustable;
use App\Enums\SecondWheels;
use App\Enums\SportsPackage;
use App\Enums\SportsSeat;
use App\Enums\TintedWindows;
use App\Enums\TowBar;
use App\Enums\Transmission;
use App\Enums\VehicleBody;
use App\Enums\VehicleStatus;
use App\Enums\VehicleType;
use App\Enums\Warranty;
use App\Models\Engine;
use App\Models\Make;
use App\Models\PreOrderVehicle;
use App\Models\Variant;
use App\Models\VehicleGroup;
use App\Models\VehicleModel;
use Database\Seeders\Traits\WeekInputDates;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PreOrderVehicleSeeder extends VehicleSeeder
{
    use WeekInputDates;

    /**
     * Users with role Supplier mapped by creator.
     *
     * @var array
     */
    private array $suppliersMap;

    /**
     * Vehicle models mapped by make.
     *
     * @var array
     */
    private array $modelIdsMapped;

    /**
     * Variants mapped by make.
     *
     * @var array
     */
    private array $variantIdsMapped;

    /**
     * Engines mapped by make.
     *
     * @var array
     */
    private array $engineIdsMapped;

    /**
     * Vehicle groups mapped by creator.
     *
     * @var array
     */
    private array $vehicleGroupIdsMapped;

    /**
     * Key value array where key is engine id and value is fuel id.
     *
     * @var array
     */
    private array $fuelIdsMapped;

    /**
     * Create a new PreOrderVehicleSeeder instance.
     */
    public function __construct()
    {
        parent::__construct();

        $this->vehicleGroupIdsMapped = VehicleGroup::mapByColumn('creator_id');
        $this->modelIdsMapped = VehicleModel::mapByColumn('make_id');
        $this->variantIdsMapped = Variant::mapByColumn('make_id');
        $this->engineIdsMapped = Engine::mapByColumn('make_id');
        $this->fuelIdsMapped = DB::table('engines')->pluck('fuel', 'id')->all();
    }

    /**
     * Run the seeder.
     *
     * @return void
     */
    public function run(): void
    {
        $model = new PreOrderVehicle();
        $makes = Make::all();
        $preparedArrayData = [];
        $calculationInserts = [];

        $preOrderVehicleId = 1;
        foreach ($this->getCompanyCompanyAdministratorIdsMap() as $companyId => $creatorId) {
            $supplierIds = $this->getSupplierIds($companyId);

            foreach ($makes->where('creator_id', $creatorId) as $make) {
                if (! isset(
                    $this->modelIdsMapped[$make->id],
                    $this->variantIdsMapped[$make->id],
                    $this->engineIdsMapped[$make->id],
                    $this->vehicleGroupIdsMapped[$make->creator_id])
                ) {
                    continue;
                }

                for ($i = 0; $i < $this->faker->numberBetween(3, 7); $i++) {
                    $preparedArrayData[] = $this->getVehicleData($make, $preOrderVehicleId, $supplierIds);
                    $calculationInserts[] = $this->generateCalculationData($model, $preOrderVehicleId);
                    $preOrderVehicleId++;
                }
            }
        }

        $chunks = array_chunk($preparedArrayData, 500);
        foreach ($chunks as $chunk) {
            DB::table('pre_order_vehicles')->insert($chunk);
        }

        $calculationChunks = array_chunk($calculationInserts, 500);
        foreach ($calculationChunks as $calculationChunk) {
            DB::table('calculations')->insert($calculationChunk);
        }
    }

    /**
     * Return array of vehicle data ready for insert.
     *
     * @param  Make  $make
     * @param  int   $preOrderVehicleId
     * @param  array $supplierIds
     * @return array
     */
    private function getVehicleData(Make $make, int $preOrderVehicleId, Collection $supplierIds): array
    {
        $engineId = $this->faker->randomElement($this->engineIdsMapped[$make->id]);

        $currentRegistration = $this->faker->boolean(70) ? $this->faker->numberBetween(1, 20) : null;

        $supplier = $this->faker->randomElement($supplierIds);

        return [
            'id'                                  => $preOrderVehicleId,
            'make_id'                             => $make->id,
            'creator_id'                          => $make->creator_id,
            'supplier_company_id'                 => $supplierIds->search($supplier),
            'supplier_id'                         => $this->faker->boolean() ? $supplier : null,
            'vehicle_model_id'                    => $this->faker->randomElement($this->modelIdsMapped[$make->id]),
            'vehicle_model_free_text'             => $this->faker->randomElement($this->vehicleModels),
            'engine_id'                           => $engineId,
            'engine_free_text'                    => $this->faker->randomElement($this->engines),
            'variant_id'                          => $this->faker->randomElement($this->variantIdsMapped[$make->id]),
            'variant_free_text'                   => $this->faker->randomElement($this->variants),
            'type'                                => $this->faker->randomElement(VehicleType::values()),
            'body'                                => $this->faker->randomElement(VehicleBody::values()),
            'fuel'                                => $this->fuelIdsMapped[$engineId],
            'vehicle_status'                      => $currentRegistration ? $this->faker->numberBetween(2, 3) : VehicleStatus::New_without_registration,
            'current_registration'                => $currentRegistration,
            'transmission'                        => $this->faker->randomElement(Transmission::values()),
            'transmission_free_text'              => $this->faker->randomElement($this->transmissions),
            'interior_material'                   => $this->faker->randomElement(InteriorMaterial::values()),
            'specific_exterior_color'             => $this->faker->randomElement(ExteriorColour::values()),
            'factory_name_color'                  => $this->faker->colorName,
            'specific_interior_color'             => $this->faker->randomElement(InteriorColour::values()),
            'factory_name_interior'               => $this->faker->colorName,
            'panorama'                            => $this->faker->randomElement(Panorama::values()),
            'headlights'                          => $this->faker->randomElement(Headlights::values()),
            'digital_cockpit'                     => $this->faker->randomElement(DigitalCockpit::values()),
            'cruise_control'                      => $this->faker->randomElement(CruiseControl::values()),
            'keyless_entry'                       => $this->faker->randomElement(KeylessEntry::values()),
            'air_conditioning'                    => $this->faker->randomElement(Airconditioning::values()),
            'pdc'                                 => $this->faker->randomElement(PDC::values()),
            'second_wheels'                       => $this->faker->randomElement(SecondWheels::values()),
            'camera'                              => $this->faker->randomElement(Camera::values()),
            'tow_bar'                             => $this->faker->randomElement(TowBar::values()),
            'seat_heating'                        => $this->faker->randomElement(SeatHeating::values()),
            'seat_massage'                        => $this->faker->randomElement(SeatMassage::values()),
            'optics'                              => $this->faker->randomElement(Optics::values()),
            'tinted_windows'                      => $this->faker->randomElement(TintedWindows::values()),
            'sports_package'                      => $this->faker->randomElement(SportsPackage::values()),
            'warranty'                            => $this->faker->randomElement(Warranty::values()),
            'navigation'                          => $this->faker->randomElement(Navigation::values()),
            'sports_seat'                         => $this->faker->randomElement(SportsSeat::values()),
            'seats_electrically_adjustable'       => $this->faker->randomElement(SeatsElectricallyAdjustable::values()),
            'app_connect'                         => $this->faker->randomElement(AppConnect::values()),
            'color_type'                          => $this->faker->randomElement(ColorType::values()),
            'highlight_1'                         => $this->faker->word(),
            'highlight_2'                         => $this->faker->word(),
            'highlight_3'                         => $this->faker->word(),
            'highlight_4'                         => $this->faker->word(),
            'highlight_5'                         => $this->faker->word(),
            'highlight_6'                         => $this->faker->word(),
            'configuration_number'                => $this->faker->word(),
            'kilometers'                          => $this->faker->boolean ? $this->faker->numberBetween(1, 50) : null,
            'production_weeks'                    => $this->getWeekInputDates(),
            'expected_delivery_weeks'             => $this->getWeekInputDates(),
            'expected_leadtime_for_delivery_from' => $this->faker->numberBetween(1, 3),
            'expected_leadtime_for_delivery_to'   => $this->faker->numberBetween(3, 6),
            'registration_weeks_from'             => $this->faker->numberBetween(1, 3),
            'registration_weeks_to'               => $this->faker->numberBetween(3, 6),
            'vehicle_reference'                   => $this->faker->bothify('??-####-??'),
            'option'                              => $this->faker->sentence,
            'created_at'                          => $this->safeDateTimeBetween('-2 years', 'now', 'UTC'),
            'updated_at'                          => null,
        ];
    }
}

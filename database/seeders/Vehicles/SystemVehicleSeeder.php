<?php

declare(strict_types=1);

namespace Database\Seeders\Vehicles;

use App\Enums\Airconditioning;
use App\Enums\AppConnect;
use App\Enums\Camera;
use App\Enums\Coc;
use App\Enums\ColorType;
use App\Enums\Country;
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
use App\Enums\VehicleStock;
use App\Enums\VehicleType;
use App\Enums\Warranty;
use App\Models\Engine;
use App\Models\Make;
use App\Models\User;
use App\Models\Variant;
use App\Models\Vehicle;
use App\Models\VehicleGroup;
use App\Models\VehicleModel;
use Database\Seeders\Traits\WeekInputDates;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SystemVehicleSeeder extends VehicleSeeder
{
    use WeekInputDates;

    /**
     * Users with role Supplier mapped by creator.
     *
     * @var array
     */
    private array $supplierIdsMapped;

    /**
     * Array of vehicle model ids mapped by make.
     *
     * @var array
     */
    private array $modelIdsMapped;

    /**
     * Array of variants mapped by make.
     *
     * @var array
     */
    private array $variantsMapped;

    /**
     * Array of engines mapped by make.
     *
     * @var array
     */
    private array $enginesMapped;

    /**
     * Array of vehicle group ids mapped by creator.
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
     * Vehicle default data for these ids.
     *
     * @var array
     */
    private array $vehicleStaticData = [
        [
            'id'                      => 349,
            'hp'                      => 630,
            'kilometers'              => 13543,
            'kw'                      => 463,
            'co2_wltp'                => 151,
            'first_registration_date' => '2024-09-10',
        ],
        [
            'id'                      => 350,
            'hp'                      => 630,
            'kilometers'              => 11200,
            'kw'                      => 463,
            'co2_wltp'                => 151,
            'first_registration_date' => '2024-10-02',
        ],
        [
            'id'                      => 351,
            'hp'                      => 630,
            'kilometers'              => 6797,
            'kw'                      => 463,
            'co2_wltp'                => 151,
            'first_registration_date' => '2024-10-16',
        ],
        [
            'id'                      => 352,
            'hp'                      => 630,
            'kilometers'              => 930,
            'kw'                      => 463,
            'co2_wltp'                => 151,
            'first_registration_date' => '2024-10-03',
        ],
        [
            'id'                      => 353,
            'hp'                      => 630,
            'kilometers'              => 17394,
            'kw'                      => 463,
            'co2_wltp'                => 151,
            'first_registration_date' => '2024-08-07',
        ],
        [
            'id'                      => 354,
            'hp'                      => 630,
            'kilometers'              => 14506,
            'kw'                      => 463,
            'co2_wltp'                => 151,
            'first_registration_date' => '2024-05-06',
        ],
    ];

    /**
     * Create a new VehicleSeeder instance.
     */
    public function __construct()
    {
        parent::__construct();

        $this->supplierIdsMapped = User::withRoleMapByColumn('Supplier', 'creator_id');
        $this->vehicleGroupIdsMapped = VehicleGroup::mapByColumn('creator_id');
        $this->modelIdsMapped = $this->mapByColumn(VehicleModel::class);
        $this->variantsMapped = $this->mapByColumn(Variant::class);
        $this->enginesMapped = $this->mapByColumn(Engine::class);
        $this->fuelIdsMapped = DB::table('engines')->pluck('fuel', 'id')->all();
    }

    /**
     * Run the seeder.
     *
     * @return void
     */
    public function run(): void
    {
        $model = new Vehicle();
        $makes = Make::all();
        $vehicleInserts = [];
        $calculationInserts = [];

        $vehicleId = 1;
        foreach ($this->getCompanyCompanyAdministratorIdsMap() as $companyId => $creatorId) {
            $supplierIds = $this->getSupplierIds($companyId);

            foreach ($makes->where('creator_id', $creatorId) as $make) {
                if (! isset(
                    $this->modelIdsMapped[$make->id],
                    $this->variantsMapped[$make->id],
                    $this->enginesMapped[$make->id],
                    $this->vehicleGroupIdsMapped[$make->creator_id],
                )) {
                    continue;
                }

                for ($i = 0; $i < $this->faker->numberBetween(10, 15); $i++) {
                    $vehicleInserts[] = $this->getVehicleData($make, $vehicleId, $supplierIds);
                    $calculationInserts[] = $this->generateCalculationData($model, $vehicleId);
                    $vehicleId++;
                }
            }
        }

        $chunks = array_chunk($vehicleInserts, 500);
        foreach ($chunks as $chunk) {
            DB::table('vehicles')->insert($chunk);
        }

        $calculationChunks = array_chunk($calculationInserts, 500);
        foreach ($calculationChunks as $calculationChunk) {
            DB::table('calculations')->insert($calculationChunk);
        }
    }

    /**
     * Return vehicle data ready to insert.
     *
     * @param Make $make
     * @param int $vehicleId
     * @param Collection $supplierIds
     * @return array
     */
    protected function getVehicleData(Make $make, int $vehicleId, Collection $supplierIds): array
    {
        $currentRegistration = $this->faker->numberBetween(1, 20);

        $engine = $this->faker->randomElement($this->enginesMapped[$make->id]);
        $variant = $this->faker->randomElement($this->variantsMapped[$make->id]);
        $vehicleModel = $this->faker->randomElement($this->modelIdsMapped[$make->id]);
        $reference = $this->faker->bothify('??####??');
        $vin = $this->faker->regexify('[A-HJ-NPR-Z0-9]{17}');
        $kw = $this->faker->numberBetween(30, 1000);

        $supplier = $this->faker->randomElement($supplierIds);

        $dynamicVehicleData = [
            'id'                                          => $vehicleId,
            'make_id'                                     => $make->id,
            'creator_id'                                  => $make->creator_id,
            'supplier_company_id'                         => $supplierIds->search($supplier),
            'supplier_id'                                 => $this->faker->boolean() ? $supplier : null,
            'vehicle_model_id'                            => $vehicleModel['id'],
            'vehicle_model_free_text'                     => $this->faker->randomElement($this->vehicleModels),
            'engine_id'                                   => $engine['id'],
            'engine_free_text'                            => $this->faker->randomElement($this->engines),
            'variant_id'                                  => $variant['id'],
            'variant_free_text'                           => $this->faker->randomElement($this->variants),
            'vehicle_group_id'                            => isset($this->vehicleGroupIdsMapped[$make->creator_id]) ? $this->faker->randomElement($this->vehicleGroupIdsMapped[$make->creator_id]) : null,
            'type'                                        => $this->faker->randomElement(VehicleType::values()),
            'body'                                        => $this->faker->randomElement(VehicleBody::values()),
            'fuel'                                        => $this->fuelIdsMapped[$engine['id']],
            'vehicle_status'                              => $this->faker->randomElement(VehicleStatus::values()),
            'current_registration'                        => $currentRegistration,
            'transmission'                                => $this->faker->randomElement(Transmission::values()),
            'transmission_free_text'                      => $this->faker->randomElement($this->transmissions),
            'coc'                                         => $this->faker->randomElement(Coc::values()),
            'interior_material'                           => $this->faker->randomElement(InteriorMaterial::values()),
            'specific_exterior_color'                     => $this->faker->randomElement(ExteriorColour::values()),
            'factory_name_color'                          => $this->faker->colorName,
            'specific_interior_color'                     => $this->faker->randomElement(InteriorColour::values()),
            'factory_name_interior'                       => $this->faker->colorName,
            'panorama'                                    => $this->faker->randomElement(Panorama::values()),
            'headlights'                                  => $this->faker->randomElement(Headlights::values()),
            'digital_cockpit'                             => $this->faker->randomElement(DigitalCockpit::values()),
            'cruise_control'                              => $this->faker->randomElement(CruiseControl::values()),
            'keyless_entry'                               => $this->faker->randomElement(KeylessEntry::values()),
            'air_conditioning'                            => $this->faker->randomElement(Airconditioning::values()),
            'pdc'                                         => $this->faker->randomElement(PDC::values()),
            'second_wheels'                               => $this->faker->randomElement(SecondWheels::values()),
            'camera'                                      => $this->faker->randomElement(Camera::values()),
            'tow_bar'                                     => $this->faker->randomElement(TowBar::values()),
            'seat_heating'                                => $this->faker->randomElement(SeatHeating::values()),
            'seat_massage'                                => $this->faker->randomElement(SeatMassage::values()),
            'optics'                                      => $this->faker->randomElement(Optics::values()),
            'tinted_windows'                              => $this->faker->randomElement(TintedWindows::values()),
            'sports_package'                              => $this->faker->randomElement(SportsPackage::values()),
            'warranty'                                    => $this->faker->randomElement(Warranty::values()),
            'navigation'                                  => $this->faker->randomElement(Navigation::values()),
            'sports_seat'                                 => $this->faker->randomElement(SportsSeat::values()),
            'seats_electrically_adjustable'               => $this->faker->randomElement(SeatsElectricallyAdjustable::values()),
            'app_connect'                                 => $this->faker->randomElement(AppConnect::values()),
            'color_type'                                  => $this->faker->randomElement(ColorType::values()),
            'highlight_1'                                 => $this->faker->word(),
            'highlight_2'                                 => $this->faker->word(),
            'highlight_3'                                 => $this->faker->word(),
            'highlight_4'                                 => $this->faker->word(),
            'highlight_5'                                 => $this->faker->word(),
            'highlight_6'                                 => $this->faker->word(),
            'advert_link'                                 => $this->faker->url(),
            'vehicle_reference'                           => $reference,
            'co2_wltp'                                    => $this->faker->numberBetween(10, 999),
            'co2_nedc'                                    => $this->faker->numberBetween(10, 999),
            'kw'                                          => $kw,
            'hp'                                          => $kw * 1.35962,
            'kilometers'                                  => $this->faker->numberBetween(0, 200000),
            'option'                                      => $this->faker->sentence,
            'damage_description'                          => $this->faker->sentence,
            'supplier_reference_number'                   => $this->faker->bothify('#########'),
            'supplier_given_damages'                      => $this->faker->numberBetween(0, 50000),
            'gross_bpm_new'                               => $this->faker->numberBetween(0, 50000),
            'rest_bpm_as_per_table'                       => $this->faker->numberBetween(0, 50000),
            'calculation_bpm_in_so'                       => $this->faker->numberBetween(0, 50000),
            'bpm_declared'                                => $this->faker->numberBetween(0, 50000),
            'gross_bpm_recalculated_based_on_declaration' => $this->faker->numberBetween(0, 50000),
            'gross_bpm_on_registration'                   => $this->faker->numberBetween(0, 50000),
            'rest_bpm_to_date'                            => $this->faker->numberBetween(0, 50000),
            'invoice_bpm'                                 => $this->faker->numberBetween(0, 50000),
            'bpm_post_change_amount'                      => $this->faker->numberBetween(0, 50000),
            'depreciation_percentage'                     => $this->faker->numberBetween(0, 100),
            'vin'                                         => $vin,
            'nl_registration_number'                      => $currentRegistration == Country::Netherlands->value ? $this->faker->bothify('??-####-??') : null,
            'identification_code'                         => $this->generateIdentificationCode($vin, $reference, $make, $vehicleModel, $variant, $engine),
            'expected_date_of_availability_from_supplier' => $this->getWeekInputDates(),
            'expected_leadtime_for_delivery_from'         => $this->faker->numberBetween(1, 3),
            'expected_leadtime_for_delivery_to'           => $this->faker->numberBetween(3, 6),
            'first_registration_date'                     => $this->safeDateTimeBetween('-10 years'),
            'first_registration_nl'                       => $this->safeDateTimeBetween('-5 years'),
            'registration_nl'                             => $this->safeDateTimeBetween('-5 years'),
            'registration_date_approval'                  => $this->safeDateTimeBetween('-5 years'),
            'last_name_registration'                      => $this->safeDateTimeBetween('-5 years'),
            'first_name_registration_nl'                  => $this->safeDateTimeBetween('-2 years', 'now', 'UTC'),
            'registration_valid_until'                    => $this->safeDateTimeBetween('now', '+5 years'),
            'created_at'                                  => $this->safeDateTimeBetween('-2 years', 'now', 'UTC'),
            'updated_at'                                  => null,
        ];

        if (in_array($vehicleId, [349, 350, 351, 352, 353, 354], true)) {
            $vehicle = collect($this->vehicleStaticData)->firstWhere('id', $vehicleId);

            return array_merge($dynamicVehicleData, [
                'hp'                      => $vehicle['hp'],
                'kilometers'              => $vehicle['kilometers'],
                'kw'                      => $vehicle['kw'],
                'co2_wltp'                => $vehicle['co2_wltp'],
                'first_registration_date' => $vehicle['first_registration_date'],
            ]);
        }

        return $dynamicVehicleData;
    }

    /**
     * Return string with identification code for the vehicle.
     *
     * @param         $vin
     * @param         $reference
     * @param         $make
     * @param         $vehicleModel
     * @param         $variant
     * @param         $engine
     * @return string
     */
    private function generateIdentificationCode($vin, $reference, $make, $vehicleModel, $variant, $engine): string
    {
        $vinLast6 = substr($vin, -6);

        $parts = [];

        if ($vinLast6) {
            $parts[] = $vinLast6;
        }
        if ($reference) {
            $parts[] = $reference;
        }
        if ($make) {
            $parts[] = $make->name;
        }
        if ($vehicleModel) {
            $parts[] = $vehicleModel['name'];
        }
        if ($variant) {
            $parts[] = $variant['name'];
        }
        if ($engine) {
            $parts[] = $engine['name'];
        }

        return implode(' ', $parts);
    }

    /**
     * Return an associative array where key is make_id and value has id and name of resource.
     *
     * @param        $model
     * @return array
     */
    private function mapByColumn($model): array
    {
        $mappedArray = [];

        $data = $model::select('make_id', 'name', 'id')
            ->get();

        foreach ($data as $row) {
            $keyId = $row->make_id;
            $valueId = $row->id;

            if (! isset($mappedArray[$keyId])) {
                $mappedArray[$keyId] = [];
            }

            $mappedArray[$keyId][$valueId] = [
                'id'   => $valueId,
                'name' => $row->name,
            ];
        }

        return $mappedArray;
    }
}

<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\CompanyAddressType;
use App\Enums\CompanyType;
use App\Enums\TransportType;
use App\Models\Company;
use App\Models\CompanyAddress;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TransportOrderSeeder extends BaseSeeder
{
    /**
     * Array of users that have the permission to create transport orders.
     *
     * @var array
     */
    private array $userCanCreateTransportOrderIds = [];

    /**
     * @var array
     */
    private array $companyAddressesMapped = [];

    /**
     * Create a new TransportOrderSeeder instance.
     */
    public function __construct()
    {
        parent::__construct();

        $this->userCanCreateTransportOrderIds = User::whereHas('roles.permissions', function ($query) {
            $query->where('name', 'create-transport-order');
        })->pluck('company_id', 'id')->all();

        $this->companyAddressesMapped = $this->getCompanyAddressesMapped('company_id');
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $preparedArrayData = [];

        foreach ($this->userCanCreateTransportOrderIds as $userId => $companyId) {
            $crmCompanyIds = Company::crmCompanies(CompanyType::Transport->value)
                ->whereHas('creator', function ($query) use ($companyId) {
                    $query->where('company_id', $companyId);
                })->get(['id'])->pluck('id')->all();

            $transportSuppliers = User::whereIn('company_id', $crmCompanyIds)->role('Transport Supplier')->get();

            for ($i = 0; $i < $this->faker->numberBetween(3, 7); $i++) {
                $transportCompanyUse = $this->faker->boolean();
                $transportType = $this->faker->randomElement(TransportType::values());

                $transportCompanyId = $transportCompanyUse ? $this->faker->randomElement($crmCompanyIds) : null;
                $transporters = $transportSuppliers->where('company_id', $transportCompanyId);
                $transporterId = $transportCompanyUse && $transporters->isNotEmpty() ? $this->faker->randomElement($transporters->pluck('id')->toArray()) : null;

                $companiesArray = [
                    'pickUpCompany'      => null,
                    'pickUpLocationId'   => null,
                    'deliveryCompany'    => null,
                    'deliveryLocationId' => null,
                ];

                if ($transportType == TransportType::Other->value) {
                    $companiesArray['pickUpCompany'] = $this->faker->randomElement($crmCompanyIds);
                    if (isset($this->companyAddressesMapped[$companiesArray['pickUpCompany']])) {
                        $companiesArray['pickUpLocationId'] = $this->faker->randomElement($this->companyAddressesMapped[$companiesArray['pickUpCompany']]);
                    }

                    $companiesArray['deliveryCompany'] = $this->faker->randomElement($crmCompanyIds);
                    if (isset($this->companyAddressesMapped[$companiesArray['deliveryCompany']])) {
                        $companiesArray['deliveryLocationId'] = $this->faker->randomElement($this->companyAddressesMapped[$companiesArray['deliveryCompany']]);
                    }
                }

                $preparedArrayData[] = [
                    'creator_id'                  => $userId,
                    'transport_company_id'        => $transportCompanyUse ? $transportCompanyId : null,
                    'transporter_id'              => $transportCompanyUse ? $transporterId : null,
                    'status'                      => 1,
                    'transport_company_use'       => $transportCompanyUse,
                    'transport_type'              => $transportType,
                    'vehicle_type'                => $this->faker->numberBetween(1, 3),
                    'pick_up_company_id'          => $companiesArray['pickUpCompany'],
                    'pick_up_location_id'         => $companiesArray['pickUpLocationId'],
                    'pick_up_location_free_text'  => $transportType != TransportType::Inbound->value ? $this->faker->sentence() : null,
                    'pick_up_notes'               => $this->faker->sentence(),
                    'pick_up_after_date'          => $this->safeDateTimeBetween('-2 years', '-1 years'),
                    'delivery_company_id'         => $companiesArray['deliveryCompany'],
                    'delivery_location_id'        => $companiesArray['deliveryLocationId'],
                    'delivery_location_free_text' => $transportType != TransportType::Outbound->value ? $this->faker->sentence() : null,
                    'delivery_notes'              => $this->faker->sentence(),
                    'deliver_before_date'         => $this->safeDateTimeBetween('+1 years', '+2 years'),
                    'created_at'                  => $this->safeDateTimeBetween('-2 years', 'now', 'UTC'),
                    'updated_at'                  => date('Y-m-d H:i:s'),
                ];
            }
        }

        DB::table('transport_orders')->insert($preparedArrayData);
    }

    /**
     * Used in seeders to map resource by the key provided.
     *
     * @param        $key
     * @return array
     */
    private function getCompanyAddressesMapped($key): array
    {
        $mappedArray = [];

        $data = CompanyAddress::where('type', CompanyAddressType::Logistics->value)->select($key, 'id')
            ->get();

        foreach ($data as $row) {
            $keyId = $row->{$key};
            $valueId = $row->id;

            if (! isset($mappedArray[$keyId])) {
                $mappedArray[$keyId] = [];
            }

            $mappedArray[$keyId][] = $valueId;
        }

        return $mappedArray;
    }
}

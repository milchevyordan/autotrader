<?php

declare(strict_types=1);

namespace Database\Seeders\Vehicles;

use App\Enums\CompanyType;
use App\Models\Company;
use App\Models\PreOrderVehicle;
use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\BaseSeeder;
use Illuminate\Support\Collection;

class VehicleSeeder extends BaseSeeder
{
    /**
     * Calculation default data for these ids.
     *
     * @var array
     */
    private array $calculationStaticData = [
        [
            'vehicleable_id'                 => 349,
            'is_vat'                         => true,
            'is_locked'                      => true,
            'intermediate'                   => true,
            'vat_percentage'                 => 21,
            'costs_of_damages'               => 125000,
            'net_purchase_price'             => 3750000,
            'fee_intermediate'               => 30000,
            'total_purchase_price'           => 3780000,
            'transport_inbound'              => 57500,
            'transport_outbound'             => 8500,
            'recycling_fee'                  => 2100,
            'sales_margin'                   => 224173,
            'total_costs_with_fee'           => 417273,
            'sales_price_net'                => 4197273,
            'vat'                            => 881427,
            'sales_price_incl_vat_or_margin' => 5078700,
            'rest_bpm_indication'            => 283900,
            'leges_vat'                      => 12400,
            'sales_price_total'              => 5375000,
            'gross_bpm'                      => 10000,
            'bpm_percent'                    => 1200,
            'bpm'                            => 960520,
        ],
        [
            'vehicleable_id'                 => 350,
            'is_vat'                         => true,
            'is_locked'                      => false,
            'intermediate'                   => true,
            'vat_percentage'                 => 21,
            'costs_of_damages'               => 22500,
            'net_purchase_price'             => 3800000,
            'fee_intermediate'               => 30000,
            'total_purchase_price'           => 3830000,
            'transport_inbound'              => 57500,
            'transport_outbound'             => 8500,
            'recycling_fee'                  => 2100,
            'sales_margin'                   => 203450,
            'total_costs_with_fee'           => 294050,
            'sales_price_net'                => 4124050,
            'vat'                            => 866051,
            'sales_price_incl_vat_or_margin' => 4990101,
            'rest_bpm_indication'            => 297500,
            'leges_vat'                      => 12400,
            'sales_price_total'              => 5300001,
            'gross_bpm'                      => 1091500,
            'bpm_percent'                    => null,
            'bpm'                            => 1091500,
        ],
        [
            'vehicleable_id'                 => 351,
            'is_vat'                         => true,
            'is_locked'                      => true,
            'intermediate'                   => true,
            'vat_percentage'                 => 21,
            'costs_of_damages'               => 22500,
            'net_purchase_price'             => 3500000,
            'fee_intermediate'               => 30000,
            'total_purchase_price'           => 3530000,
            'transport_inbound'              => 57500,
            'transport_outbound'             => 8500,
            'recycling_fee'                  => 2100,
            'sales_margin'                   => 179400,
            'total_costs_with_fee'           => 270000,
            'sales_price_net'                => 3800000,
            'vat'                            => 798000,
            'sales_price_incl_vat_or_margin' => 4598000,
            'rest_bpm_indication'            => 320000,
            'leges_vat'                      => 12400,
            'sales_price_total'              => 4930400,
            'gross_bpm'                      => 1091500,
            'bpm_percent'                    => null,
            'bpm'                            => 1091500,
        ],
        [
            'vehicleable_id'                 => 352,
            'is_vat'                         => true,
            'is_locked'                      => false,
            'intermediate'                   => true,
            'vat_percentage'                 => 21,
            'costs_of_damages'               => 22500,
            'net_purchase_price'             => 3800000,
            'fee_intermediate'               => 30000,
            'total_purchase_price'           => 3830000,
            'transport_inbound'              => 57500,
            'transport_outbound'             => 8500,
            'recycling_fee'                  => 2100,
            'sales_margin'                   => 203967,
            'total_costs_with_fee'           => 294567,
            'sales_price_net'                => 4124567,
            'vat'                            => 866159,
            'sales_price_incl_vat_or_margin' => 4990726,
            'rest_bpm_indication'            => 283900,
            'leges_vat'                      => 12400,
            'sales_price_total'              => 5287026,
            'gross_bpm'                      => 1091500,
            'bpm_percent'                    => null,
            'bpm'                            => 1091500,
        ],
        [
            'vehicleable_id'                 => 353,
            'is_vat'                         => true,
            'is_locked'                      => true,
            'intermediate'                   => true,
            'vat_percentage'                 => 21,
            'costs_of_damages'               => 22500,
            'net_purchase_price'             => 3800000,
            'fee_intermediate'               => 30000,
            'total_purchase_price'           => 3830000,
            'transport_inbound'              => 57500,
            'transport_outbound'             => 8500,
            'recycling_fee'                  => 2100,
            'sales_margin'                   => 173367,
            'total_costs_with_fee'           => 263967,
            'sales_price_net'                => 4093967,
            'vat'                            => 859733,
            'sales_price_incl_vat_or_margin' => 4953700,
            'rest_bpm_indication'            => 283900,
            'leges_vat'                      => 12400,
            'sales_price_total'              => 5250000,
            'gross_bpm'                      => 1091500,
            'bpm_percent'                    => 1600,
            'bpm'                            => 916860,
        ],
        [
            'vehicleable_id'                 => 354,
            'is_vat'                         => true,
            'is_locked'                      => false,
            'intermediate'                   => true,
            'vat_percentage'                 => 21,
            'costs_of_damages'               => 22500,
            'net_purchase_price'             => 3600000,
            'fee_intermediate'               => 30000,
            'total_purchase_price'           => 3630000,
            'transport_inbound'              => 57500,
            'transport_outbound'             => 8500,
            'recycling_fee'                  => 2100,
            'sales_margin'                   => 203967,
            'total_costs_with_fee'           => 294567,
            'sales_price_net'                => 3924567,
            'vat'                            => 824159,
            'sales_price_incl_vat_or_margin' => 4748726,
            'rest_bpm_indication'            => 300000,
            'leges_vat'                      => 12400,
            'sales_price_total'              => 5061126,
            'gross_bpm'                      => 1091500,
            'bpm_percent'                    => 2700,
            'bpm'                            => 796795,
        ],
    ];

    protected array $transmissions = [
        '6-speed auto',
        '7-speed dual-clutch',
        '5-speed manual',
        'CVT',
        '8-speed sport auto',
    ];

    protected array $engines = [
        '2.0L Turbo I4, 250 HP',
        '3.5L V6, 280 HP',
        '1.8L Hybrid-Electric',
        '4.0L Twin-Turbo V8, 450 HP',
        'Electric, 300-mile range',
    ];

    protected array $variants = [
        'Sport Edition',
        'Luxury Trim',
        'Off-Road Package',
        'Performance Edition',
        'Base Model',
    ];

    protected array $vehicleModels = [
        'Civic LX',
        'Accord Sport',
        'Mustang GT',
        'Camry Hybrid',
        'Ranger XLT',
        'Model S Plaid',
        'Wrangler Rubicon',
        'X5 M',
        'A4 Quattro',
        'Corolla GR',
    ];

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Return vehicle calculations.
     *
     * @param  Vehicle|PreOrderVehicle $vehicleModel
     * @param  int                     $id
     * @return array
     */
    public function generateCalculationData(Vehicle|PreOrderVehicle $vehicleModel, int $id): array
    {
        $sellingPriceSupplier = $this->faker->numberBetween(1000, 50000);
        $currencyExchangeRate = $this->faker->randomFloat(2, 0.1, 10);

        $dynamicCalculationData = [
            'vehicleable_type'               => $vehicleModel::class,
            'vehicleable_id'                 => $id,
            'is_vat'                         => $this->faker->boolean,
            'is_locked'                      => false,
            'original_currency'              => $this->faker->numberBetween(1, 6),
            'selling_price_supplier'         => $sellingPriceSupplier,
            'sell_price_currency_euro'       => $sellingPriceSupplier / $currencyExchangeRate,
            'vat_percentage'                 => 21,
            'currency_exchange_rate'         => $currencyExchangeRate,
            'transport_outbound'             => 8500,
            'recycling_fee'                  => 2100,
            'sales_margin'                   => 200000,
            'leges_vat'                      => 9200,
            'total_costs_with_fee'           => 210600,
            'sales_price_net'                => 210600,
            'sales_price_incl_vat_or_margin' => 210600,
            'sales_price_total'              => 219800,
            'intermediate'                   => false,
            'costs_of_damages'               => null,
            'net_purchase_price'             => null,
            'fee_intermediate'               => null,
            'total_purchase_price'           => null,
            'transport_inbound'              => null,
            'vat'                            => null,
            'rest_bpm_indication'            => null,
            'gross_bpm'                      => null,
            'bpm_percent'                    => null,
            'bpm'                            => null,
        ];

        if (in_array($id, [349, 350, 351, 352, 353, 354], true)) {
            $calculation = collect($this->calculationStaticData)->firstWhere('vehicleable_id', $id);

            return array_merge($dynamicCalculationData, [
                'is_vat'                         => $calculation['is_vat'],
                'is_locked'                      => $calculation['is_locked'],
                'intermediate'                   => $calculation['intermediate'],
                'vat_percentage'                 => $calculation['vat_percentage'],
                'costs_of_damages'               => $calculation['costs_of_damages'],
                'net_purchase_price'             => $calculation['net_purchase_price'],
                'fee_intermediate'               => $calculation['fee_intermediate'],
                'total_purchase_price'           => $calculation['total_purchase_price'],
                'transport_inbound'              => $calculation['transport_inbound'],
                'transport_outbound'             => $calculation['transport_outbound'],
                'recycling_fee'                  => $calculation['recycling_fee'],
                'sales_margin'                   => $calculation['sales_margin'],
                'total_costs_with_fee'           => $calculation['total_costs_with_fee'],
                'sales_price_net'                => $calculation['sales_price_net'],
                'vat'                            => $calculation['vat'],
                'sales_price_incl_vat_or_margin' => $calculation['sales_price_incl_vat_or_margin'],
                'rest_bpm_indication'            => $calculation['rest_bpm_indication'],
                'leges_vat'                      => $calculation['leges_vat'],
                'sales_price_total'              => $calculation['sales_price_total'],
                'gross_bpm'                      => $calculation['gross_bpm'],
                'bpm_percent'                    => $calculation['bpm_percent'],
                'bpm'                            => $calculation['bpm'],
            ]);
        }

        return $dynamicCalculationData;
    }

    /**
     * Return supplier ids collection
     *
     * @param int $companyId
     * @return Collection
     */
    protected function getSupplierIds(int $companyId): Collection
    {
        $crmCompanyIds = Company::crmCompanies(CompanyType::Supplier->value)
            ->whereHas('creator', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })->get(['id'])->pluck('id')->all();

        return User::whereIn('company_id', $crmCompanyIds)->role('Supplier')->pluck('id', 'company_id');
    }
}

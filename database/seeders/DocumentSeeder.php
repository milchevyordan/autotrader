<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\CompanyType;
use App\Enums\DocumentableType;
use App\Enums\DocumentLineType;
use App\Enums\PaymentCondition;
use App\Enums\SalesOrderStatus;
use App\Enums\ServiceOrderStatus;
use App\Enums\WorkOrderStatus;
use App\Models\Company;
use App\Models\Document;
use App\Models\PreOrderVehicle;
use App\Models\SalesOrder;
use App\Models\ServiceOrder;
use App\Models\ServiceVehicle;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\WorkOrder;
use App\Support\CurrencyHelper;
use App\Support\EnumHelper;
use Illuminate\Support\Facades\DB;

class DocumentSeeder extends BaseSeeder
{
    /**
     * Map documentable type to target model class.
     *
     * @var array|string[]
     */
    private array $documentableTypeClassMap = [
        DocumentableType::Pre_order_vehicle->value        => PreOrderVehicle::class,
        DocumentableType::Vehicle->value                  => Vehicle::class,
        DocumentableType::Service_vehicle->value          => ServiceVehicle::class,
        DocumentableType::Sales_order_down_payment->value => SalesOrder::class,
        DocumentableType::Sales_order->value              => SalesOrder::class,
        DocumentableType::Service_order->value            => ServiceOrder::class,
        DocumentableType::Work_order->value               => WorkOrder::class,
    ];

    /**
     * Create a new DocumentSeeder instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $documentInserts = [];
        $documentableInserts = [];
        $documentLineInserts = [];

        $documentId = 1;
        foreach ($this->getCompanyCompanyAdministratorIdsMap() as $companyId => $creatorId) {
            $usersCanCreateDocument = User::where('company_id', $companyId)
                ->whereHas('roles.permissions', function ($query) {
                    $query->where('name', 'create-document');
                })->get(['id'])->pluck('id')->all();

            if (empty($usersCanCreateDocument)) {
                continue;
            }

            $crmCompanyIds = Company::crmCompanies(CompanyType::General->value)
                ->whereHas('creator', function ($query) use ($companyId) {
                    $query->where('company_id', $companyId);
                })->get(['id'])->pluck('id')->all();

            $customerIds = User::whereIn('company_id', $crmCompanyIds)->role('Customer')->pluck('id', 'company_id');

            $preOrderVehicles = PreOrderVehicle::whereHas('creator', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })
                ->with(['calculation'])
                ->get();

            $vehicles = Vehicle::whereHas('creator', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })
                ->whereHas('workflow')
                ->with(['calculation'])
                ->limit(2)
                ->get();

            $serviceVehicles = ServiceVehicle::whereHas('creator', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })->whereHas('workflow')->limit(2)->get();

            $salesOrders = SalesOrder::whereHas('creator', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })
                ->whereIn('status', [SalesOrderStatus::Ready_for_down_payment_invoice->value, SalesOrderStatus::Completed->value])
                ->with(['orderItems', 'orderServices'])
                ->limit(2)
                ->get();

            $serviceOrders = ServiceOrder::whereHas('creator', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })
                ->where('status', ServiceOrderStatus::Completed->value)
                ->with(['orderItems', 'orderServices'])
                ->limit(2)
                ->get();

            $workOrders = WorkOrder::whereHas('creator', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })->where('status', WorkOrderStatus::Completed->value)->with(['tasks'])->limit(2)->get();

            for ($i = 0; $i < $this->faker->numberBetween(10, 15); $i++) {
                $documentableType = $this->faker->randomElement(DocumentableType::values());

                $totalPriceIncludeVat = 0;

                $relationMethod = (new Document())->typeRelationMap[$documentableType];
                for ($j = 0; $j < $this->faker->numberBetween(1, 4); $j++) {
                    if (in_array($documentableType, [DocumentableType::Sales_order_down_payment->value, DocumentableType::Sales_order->value], true)) {
                        $documentable = $this->faker->randomElement($salesOrders
                            ->where('status', $documentableType == DocumentableType::Sales_order_down_payment->value ? SalesOrderStatus::Ready_for_down_payment_invoice : SalesOrderStatus::Completed));
                    } else {
                        $documentable = $this->faker->randomElement(${$relationMethod});
                    }

                    if (! isset($documentable)) {
                        continue;
                    }

                    ${$relationMethod} = ${$relationMethod}->reject(function ($item) use ($documentable) {
                        return $item->id == $documentable->id;
                    });

                    if (isset($documentable->calculation)) {
                        $totalPriceIncludeVatToAdd = CurrencyHelper::convertCurrencyToUnits($documentable->calculation->sales_price_total);
                        $documentLineInserts[] = [
                            'document_id'       => $documentId,
                            'documentable_id'   => $documentable->id,
                            'name'              => EnumHelper::getEnumName(DocumentableType::class, $documentableType),
                            'vat_percentage'    => $documentable->calculation->vat_percentage,
                            'type'              => DocumentLineType::Main->value,
                            'price_exclude_vat' => CurrencyHelper::convertCurrencyToUnits($documentable->calculation->sales_price_net),
                            'vat'               => CurrencyHelper::convertCurrencyToUnits($documentable->calculation->vat),
                            'price_include_vat' => $totalPriceIncludeVatToAdd,
                            'created_at'        => $this->safeDateTimeBetween('-2 years', 'now', 'UTC'),
                            'updated_at'        => now(),
                        ];
                        $totalPriceIncludeVat += $totalPriceIncludeVatToAdd;

                        if ($documentable->calculation->is_vat) {
                            continue;
                        }

                        if ($documentable->calculation->rest_bpm_indication) {
                            $totalPriceIncludeVatToAdd = CurrencyHelper::convertCurrencyToUnits($documentable->calculation->rest_bpm_indication);
                            $prices = $this->getPriceExcludeVatAndVat($totalPriceIncludeVatToAdd);

                            $documentLineInserts[] = [
                                'document_id'       => $documentId,
                                'documentable_id'   => $documentable->id,
                                'name'              => __('BPM'),
                                'vat_percentage'    => 21,
                                'type'              => DocumentLineType::Bpm->value,
                                'price_exclude_vat' => $prices['price_exclude_vat'],
                                'vat'               => $prices['vat'],
                                'price_include_vat' => $totalPriceIncludeVatToAdd,
                                'created_at'        => $this->safeDateTimeBetween('-2 years', 'now', 'UTC'),
                                'updated_at'        => now(),
                            ];
                            $totalPriceIncludeVat += $totalPriceIncludeVatToAdd;
                        }

                        if ($documentable->calculation->leges_vat) {
                            $totalPriceIncludeVatToAdd = CurrencyHelper::convertCurrencyToUnits($documentable->calculation->leges_vat);
                            $prices = $this->getPriceExcludeVatAndVat($totalPriceIncludeVatToAdd);

                            $documentLineInserts[] = [
                                'document_id'       => $documentId,
                                'documentable_id'   => $documentable->id,
                                'name'              => __('RDW Identification & Leges'),
                                'vat_percentage'    => 21,
                                'type'              => DocumentLineType::Other->value,
                                'price_exclude_vat' => $prices['price_exclude_vat'],
                                'vat'               => $prices['vat'],
                                'price_include_vat' => $totalPriceIncludeVatToAdd,
                                'created_at'        => $this->safeDateTimeBetween('-2 years', 'now', 'UTC'),
                                'updated_at'        => now(),
                            ];
                            $totalPriceIncludeVat += $totalPriceIncludeVatToAdd;
                        }

                        if ($documentable->calculation->recycling_fee) {
                            $totalPriceIncludeVatToAdd = CurrencyHelper::convertCurrencyToUnits($documentable->calculation->recycling_fee);
                            $prices = $this->getPriceExcludeVatAndVat($totalPriceIncludeVatToAdd);

                            $documentLineInserts[] = [
                                'document_id'       => $documentId,
                                'documentable_id'   => $documentable->id,
                                'name'              => __('Recycling fee'),
                                'vat_percentage'    => 21,
                                'type'              => DocumentLineType::Other->value,
                                'price_exclude_vat' => $prices['price_exclude_vat'],
                                'vat'               => $prices['vat'],
                                'price_include_vat' => $totalPriceIncludeVatToAdd,
                                'created_at'        => $this->safeDateTimeBetween('-2 years', 'now', 'UTC'),
                                'updated_at'        => now(),
                            ];
                            $totalPriceIncludeVat += $totalPriceIncludeVatToAdd;
                        }
                    }

                    if (isset($documentable->down_payment_amount)) {
                        $name = 'Sales Order';
                        if ($documentableType === DocumentableType::Sales_order_down_payment->value) {
                            $name .= ' down payment';
                            $priceIncludeVat = CurrencyHelper::convertCurrencyToUnits($documentable->down_payment_amount);
                            $totalPriceIncludeVat += CurrencyHelper::convertCurrencyToUnits($documentable->down_payment_amount);
                        } else {
                            $priceIncludeVat = -CurrencyHelper::convertCurrencyToUnits($documentable->down_payment_amount);
                            $totalPriceIncludeVat -= CurrencyHelper::convertCurrencyToUnits($documentable->down_payment_amount);
                        }

                        $prices = $this->getPriceExcludeVatAndVat($priceIncludeVat, $documentable->vat_percentage);

                        $documentLineInserts[] = [
                            'document_id'       => $documentId,
                            'documentable_id'   => $documentable->id,
                            'name'              => $name,
                            'vat_percentage'    => $documentable->vat_percentage,
                            'price_exclude_vat' => $prices['price_exclude_vat'],
                            'vat'               => $prices['vat'],
                            'type'              => DocumentLineType::Main->value,
                            'price_include_vat' => $priceIncludeVat,
                            'created_at'        => $this->safeDateTimeBetween('-2 years', 'now', 'UTC'),
                            'updated_at'        => now(),
                        ];

                        $totalPriceIncludeVat += $priceIncludeVat;
                    }

                    if ($documentableType != DocumentableType::Sales_order_down_payment->value && isset($documentable->orderItems)) {
                        foreach ($documentable->orderItems as $item) {
                            if (! $item->in_output) {
                                continue;
                            }

                            $totalPriceIncludeVatToAdd = CurrencyHelper::convertCurrencyToUnits($item->sale_price);
                            $prices = $this->getPriceExcludeVatAndVat($totalPriceIncludeVatToAdd);

                            $documentLineInserts[] = [
                                'document_id'       => $documentId,
                                'documentable_id'   => $documentable->id,
                                'name'              => $item->item->shortcode,
                                'vat_percentage'    => $item->item->vat_percentage,
                                'type'              => DocumentLineType::Other->value,
                                'price_exclude_vat' => $prices['price_exclude_vat'],
                                'vat'               => $prices['vat'],
                                'price_include_vat' => $totalPriceIncludeVatToAdd,
                                'created_at'        => $this->safeDateTimeBetween('-2 years', 'now', 'UTC'),
                                'updated_at'        => now(),
                            ];

                            $totalPriceIncludeVat += $totalPriceIncludeVatToAdd;
                        }
                    }

                    if ($documentableType != DocumentableType::Sales_order_down_payment->value && isset($documentable->orderServices)) {
                        foreach ($documentable->orderServices as $orderService) {
                            if (! $orderService->in_output) {
                                continue;
                            }

                            $totalPriceIncludeVatToAdd = CurrencyHelper::convertCurrencyToUnits($orderService->sale_price);
                            $prices = $this->getPriceExcludeVatAndVat($totalPriceIncludeVatToAdd);

                            $documentLineInserts[] = [
                                'document_id'       => $documentId,
                                'documentable_id'   => $documentable->id,
                                'name'              => $orderService->name,
                                'vat_percentage'    => null,
                                'type'              => DocumentLineType::Other->value,
                                'price_exclude_vat' => $prices['price_exclude_vat'],
                                'vat'               => $prices['vat'],
                                'price_include_vat' => $totalPriceIncludeVatToAdd,
                                'created_at'        => $this->safeDateTimeBetween('-2 years', 'now', 'UTC'),
                                'updated_at'        => now(),
                            ];

                            $totalPriceIncludeVat += $totalPriceIncludeVatToAdd;
                        }
                    }

                    if (isset($documentable->tasks)) {
                        foreach ($documentable->tasks as $task) {
                            $totalPriceIncludeVatToAdd = CurrencyHelper::convertCurrencyToUnits($task->actual_price);
                            $prices = $this->getPriceExcludeVatAndVat($totalPriceIncludeVatToAdd);

                            $documentLineInserts[] = [
                                'document_id'       => $documentId,
                                'documentable_id'   => $documentable->id,
                                'name'              => $task->name,
                                'vat_percentage'    => null,
                                'type'              => DocumentLineType::Other->value,
                                'price_exclude_vat' => $prices['price_exclude_vat'],
                                'vat'               => $prices['vat'],
                                'price_include_vat' => $totalPriceIncludeVatToAdd,
                                'created_at'        => $this->safeDateTimeBetween('-2 years', 'now', 'UTC'),
                                'updated_at'        => now(),
                            ];

                            $totalPriceIncludeVat += $totalPriceIncludeVatToAdd;
                        }
                    }

                    $documentableInserts[] = [
                        'document_id'       => $documentId,
                        'documentable_type' => $this->documentableTypeClassMap[$documentableType],
                        'documentable_id'   => $documentable->id,
                        'created_at'        => $this->safeDateTimeBetween('-2 years', 'now', 'UTC'),
                        'updated_at'        => now(),
                    ];
                }

                $customer = $this->faker->randomElement($customerIds);

                $documentInserts[] = [
                    'id'                      => $documentId,
                    'creator_id'              => $this->faker->randomElement($usersCanCreateDocument),
                    'customer_company_id'     => $customerIds->search($customer),
                    'customer_id'             => $this->faker->boolean() ? $customer : null,
                    'documentable_type'       => $documentableType,
                    'payment_condition'       => $this->faker->randomElement(PaymentCondition::values()),
                    'number'                  => 'INV-'.$this->faker->unique()->numberBetween(1000, 9999),
                    'notes'                   => $this->faker->sentence(),
                    'total_price_include_vat' => 0 == $totalPriceIncludeVat ? null : $totalPriceIncludeVat,
                    'created_at'              => $this->safeDateTimeBetween('-2 years', 'now', 'UTC'),
                    'updated_at'              => now(),
                ];

                $documentId++;
            }
        }

        DB::table('documents')->insert($documentInserts);
        DB::table('documentables')->insert($documentableInserts);
        DB::table('document_lines')->insert($documentLineInserts);
    }

    /**
     * @param  int      $priceIncludeVat
     * @param  null|int $vatPercentage
     * @return array
     */
    private function getPriceExcludeVatAndVat(int $priceIncludeVat, ?int $vatPercentage = null): array
    {
        $priceIncludeVatUnits = 'integer' == gettype($priceIncludeVat) ? $priceIncludeVat : CurrencyHelper::convertCurrencyToUnits($priceIncludeVat);

        $vatUnits = (($vatPercentage ?? 21) * $priceIncludeVatUnits) / 100;

        return [
            'vat'               => $vatUnits,
            'price_exclude_vat' => $priceIncludeVatUnits - $vatUnits,
        ];
    }
}

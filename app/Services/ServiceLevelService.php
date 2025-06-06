<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ItemType;
use App\Enums\ServiceLevelType;
use App\Http\Requests\StoreServiceLevelRequest;
use App\Http\Requests\UpdateServiceLevelRequest;
use App\Models\Item;
use App\Models\Quote;
use App\Models\SalesOrder;
use App\Models\ServiceLevel;
use App\Models\ServiceOrder;
use App\Services\DataTable\DataTable;
use App\Services\DataTable\RawOrdering;
use App\Support\CurrencyHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use RuntimeException;

class ServiceLevelService extends Service
{
    /**
     * Service level model instance.
     *
     * @var ServiceLevel
     */
    private ServiceLevel $serviceLevel;

    /**
     * DataTable of the items datatable shown in edit form.
     *
     * @var DataTable
     */
    private DataTable $itemsDataTable;

    /**
     * Array of the item ids that are selected.
     *
     * @var array
     */
    private array $selectedItemIds;

    /**
     * Create a new ServiceLevelService instance.
     */
    public function __construct()
    {
        $this->setServiceLevel(new ServiceLevel());
    }

    /**
     * Return service level in this company datatable used in index page.
     *
     * @return DataTable
     */
    public function getIndexMethodDataTable(): DataTable
    {
        return (new DataTable(
            ServiceLevel::inThisCompany()->select(ServiceLevel::$defaultSelectFields)
        ))
            ->setRelation('creator')
            ->setColumn('action', __('Action'), exportable: false)
            ->setColumn('id', '#', true, true)
            ->setColumn('creator.full_name', __('Created by'), true, true)
            ->setColumn('type', __('Type'), true, true)
            ->setColumn('name', __('Name'), true, true)
            ->setTimestamps()
            ->setEnumColumn('type', ServiceLevelType::class);
    }

    /**
     * Get the value of selectedItemIds.
     *
     * @return array
     */
    public function getSelectedItemIds(): array
    {
        if (! isset($this->selectedItemIds)) {
            $this->setSelectedItemIds();
        }

        return $this->selectedItemIds;
    }

    /**
     * Set the value of selectedItemIds.
     *
     * @return void
     */
    private function setSelectedItemIds(): void
    {
        $this->selectedItemIds = $this->getServiceLevel()->items->pluck('id')->toArray();
    }

    /**
     * Get the items datatable shown in edit form.
     *
     * @return DataTable
     */
    public function getItemsDataTable(): DataTable
    {
        if (! isset($this->itemsDataTable)) {
            $this->setItemsDataTable();
        }

        return $this->itemsDataTable;
    }

    /**
     * Set the items datatable shown in edit form.
     *
     * @return void
     */
    private function setItemsDataTable(): void
    {
        $serviceLevel = $this->getServiceLevel();
        $userHasSearched = request(null)->input('filter.global');

        $this->itemsDataTable = (new DataTable(
            Item::inThisCompany()->select(Item::$defaultSelectFields)
        ))
            ->setColumn('action', __('Action'), exportable: false)
            ->setColumn('id', '#', true, true)
            ->setColumn('type', __('Type'), true, true)
            ->setColumn('shortcode', __('Shortcode'), true, true)
            ->setColumn('description', __('Description'), true, true)
            ->setColumn('purchase_price', __('Purchase Price'), true, true)
            ->setColumn('sale_price', __('Sale Price'), true, true)
            ->setEnumColumn('type', ItemType::class)
            ->setPriceColumn('purchase_price')
            ->setPriceColumn('sale_price');

        if ($selectedItemIds = $this->getSelectedItemIds()) {
            $this->itemsDataTable->setRawOrdering(new RawOrdering('FIELD(id, '.implode(',', $selectedItemIds).') DESC'));
        }

        $this->itemsDataTable->run(5, function ($model) use ($userHasSearched, $serviceLevel) {
            if ($serviceLevel->id && ! $userHasSearched) {
                return $model->whereHas('serviceLevels', function ($model) use ($serviceLevel) {
                    $model->where('service_levels.id', $serviceLevel->id);
                });
            }

            return $model->withoutTrashed();
        });
    }

    /**
     * Service level creation.
     *
     * @param StoreServiceLevelRequest $request
     * @return self
     */
    public function createServiceLevel(StoreServiceLevelRequest $request): self
    {
        $validatedRequest = $request->validated();

        $serviceLevel = new ServiceLevel();
        $serviceLevel->creator_id = auth()->id();
        $serviceLevel->fill($validatedRequest);

        if (! $serviceLevel->save()) {
            throw new RuntimeException('Service Level creation failed.');
        }

        if (ServiceLevelType::Client == $serviceLevel->type) {
            $serviceLevel->companies()->sync(collect($validatedRequest['companies'] ?? []));
        }

        // Set the 'in_output' value for each item
        $items = array_map(function ($itemId) use ($validatedRequest) {
            return [
                'item_id'   => $itemId,
                'in_output' => in_array($itemId, $validatedRequest['items_in_output'], true),
            ];
        }, $validatedRequest['items']);

        $serviceLevel->items()->attach($items);

        $serviceLevel->additionalServices()->createMany($validatedRequest['additional_services']);

        $this->setServiceLevel($serviceLevel);

        return $this;
    }

    /**
     * Service level update.
     *
     * @param UpdateServiceLevelRequest $request
     * @return self
     */
    public function updateServiceLevel(UpdateServiceLevelRequest $request): self
    {
        $validatedRequest = $request->validated();

        $serviceLevel = $this->getServiceLevel();
        $changeLoggerService = new ChangeLoggerService($serviceLevel, ['items', 'additionalServices']);

        $serviceLevel->update($validatedRequest);

        $this->syncItems($validatedRequest['items'] ?? [], $validatedRequest['items_in_output'] ?? []);
        $this->syncAdditionalServices($validatedRequest['additional_services'] ?? []);

        $changeLoggerService->logChanges($serviceLevel);

        return $this;
    }

    /**
     * Sync items' relation.
     *
     * @param       $validatedRequestItems
     * @param       $itemsInOutput
     * @return void
     */
    private function syncItems($validatedRequestItems, $itemsInOutput): void
    {
        $serviceLevel = $this->getServiceLevel();
        $items = [];

        foreach ($validatedRequestItems as $itemId) {
            $items[$itemId] = [
                'in_output' => in_array($itemId, $itemsInOutput, true),
            ];
        }

        $serviceLevel->items()->sync($items);
    }

    /**
     * Sync additional services relation.
     *
     * @param       $additionalServices
     * @return void
     */
    private function syncAdditionalServices($additionalServices): void
    {
        $serviceLevel = $this->getServiceLevel();
        $existingServiceIds = collect($additionalServices)->pluck('id')->filter();
        $serviceLevel->additionalServices()->whereNotIn('id', $existingServiceIds)->delete();

        foreach ($additionalServices as &$service) {
            $service['service_level_id'] = $serviceLevel->id;
            $service['sale_price'] = CurrencyHelper::convertCurrencyToUnits($service['sale_price']);
            $service['purchase_price'] = CurrencyHelper::convertCurrencyToUnits($service['purchase_price']);
            unset($service['is_service_level']);
        }

        $serviceLevel->additionalServices()->upsert($additionalServices, ['id'], ['service_level_id', 'name', 'sale_price', 'purchase_price', 'in_output']);
    }

    /**
     * Get the value of serviceLevel.
     *
     * @return ServiceLevel
     */
    public function getServiceLevel(): ServiceLevel
    {
        return $this->serviceLevel;
    }

    /**
     * Set the value of serviceLevel.
     *
     * @param  ServiceLevel $serviceLevel
     * @return self
     */
    public function setServiceLevel(ServiceLevel $serviceLevel): self
    {
        $this->serviceLevel = $serviceLevel;

        return $this;
    }

    /**
     * Return turnover data used when showing service level statistics.
     *
     * @return array|array[]
     */
    public function generateTurnoverData(): array
    {
        $serviceLevel = $this->getServiceLevel();

        $turnoverData = [
            'thisYear' => [
                'vehicles' => 0,
                'turnover' => 0,
                'year'     => (int) date('Y'),
            ],
            'lastYear' => [
                'vehicles' => 0,
                'turnover' => 0,
                'year'     => (int) date('Y') - 1,
            ],
        ];

        $currentAndPreviousYearOrders = $serviceLevel->salesOrders()
            ->with('vehicles:id')
            ->whereYear('created_at', $turnoverData['thisYear']['year'])
            ->orWhereYear('created_at', $turnoverData['lastYear']['year'])
            ->get();

        foreach ($currentAndPreviousYearOrders as $salesOrder) {
            $orderYear = $salesOrder->created_at->year;
            if ($orderYear === $turnoverData['thisYear']['year']) {
                $turnoverData['thisYear']['vehicles'] += $salesOrder->vehicles->count();
                $turnoverData['thisYear']['turnover'] += CurrencyHelper::convertCurrencyToUnits($salesOrder->total_sales_price_include_vat);
            } elseif ($orderYear === $turnoverData['lastYear']['year']) {
                $turnoverData['lastYear']['vehicles'] += $salesOrder->vehicles->count();
                $turnoverData['lastYear']['turnover'] += CurrencyHelper::convertCurrencyToUnits($salesOrder->total_sales_price_include_vat);
            }
        }

        return $turnoverData;
    }

    /**
     * Return Multiselect with service levels in this company with type system and client ones that are for company
     * provided in the request or as a param.
     *
     * @param  null|int   $customerCompanyId
     * @return Collection
     */
    public static function getCompanyServiceLevels(?int $customerCompanyId = null): Collection
    {
        $selectedCustomerCompanyId = request(null)->input('customer_company_id', $customerCompanyId);

        $serviceLevelsQuery = ServiceLevel::inThisCompany()
            ->where(function ($query) use ($selectedCustomerCompanyId) {
                $query?->where('type', ServiceLevelType::Client)
                    ->whereHas('companies', function ($subQuery) use ($selectedCustomerCompanyId) {
                        $subQuery->where('companies.id', $selectedCustomerCompanyId);
                    })
                    ->orWhere('type', ServiceLevelType::System);
            });

        return (new MultiSelectService($serviceLevelsQuery))->dataForSelect();
    }

    /**
     * Check if the service level select should be reset
     * It can be reset only of the customer company does not have the currently selected service level available for selection
     *
     * @param SalesOrder|ServiceOrder|Quote|null $model
     * @return bool
     */
    public static function shouldResetServiceLevels(SalesOrder|ServiceOrder|Quote|null $model = null): bool
    {
        $selectedCustomerCompanyId = request(null)->input('customer_company_id', $model?->customer_company_id);
        $selectedServiceLevelId = request(null)->input('service_level_id', $model?->service_level_id);

        if (! $selectedServiceLevelId) {
            return false;
        }

        $serviceLevel = ServiceLevel::with(['companies:id'])->select('id', 'type')->find($selectedServiceLevelId);
        if ($serviceLevel?->type?->value == ServiceLevelType::Client->value && ! $serviceLevel->companies->pluck('id')->contains($selectedCustomerCompanyId)) {
            return true;
        }

        return false;
    }

    /**
     * Return datatable of Service Levels by provided builder.
     *
     * @param  Builder   $builder
     * @return DataTable
     */
    public static function getServiceLevelsDataTableByBuilder(Builder $builder): DataTable
    {
        return (new DataTable(
            $builder->select(ServiceLevel::$defaultSelectFields)
        ))
            ->setColumn('id', '#', true, true)
            ->setColumn('type', __('Type'), true, true)
            ->setColumn('name', __('Name'), true, true)
            ->setTimestamps()
            ->setEnumColumn('type', ServiceLevelType::class);
    }

    /**
     * Return service level's defaults in an array from service_level_id given in the request.
     *
     * @return null|array
     */
    public static function getServiceLevelDefaults(): array|null
    {
        $serviceLevelId = request()->input('service_level_id');

        if (! $serviceLevelId) {
            return null;
        }

        $serviceLevel = ServiceLevel::select(
            'payment_condition',
            'payment_condition_free_text',
            'discount',
            'discount_in_output',
            'damage',
            'transport_included',
            'type_of_sale'
        )->find($serviceLevelId);

        return [
            'payment_condition'           => $serviceLevel->payment_condition,
            'payment_condition_free_text' => $serviceLevel->payment_condition_free_text,
            'discount'                    => $serviceLevel->discount,
            'discount_in_output'          => $serviceLevel->discount_in_output,
            'damage'                      => $serviceLevel->damage,
            'transport_included'          => $serviceLevel->transport_included,
            'type_of_sale'                => $serviceLevel->type_of_sale,
        ];
    }

    /**
     * Return true if one of the items or additional packages has in output set to true.
     *
     * @param  Model $model
     * @return bool
     */
    public static function checkAllItemsAndAdditionalsInOutput(Model $model): bool
    {
        $allItemsAndAdditionals = false;

        foreach (array_merge($model->orderItems?->all(), $model->orderServices?->all()) as $item) {
            if ($item->in_output) {
                $allItemsAndAdditionals = true;

                break;
            }
        }

        return $allItemsAndAdditionals;
    }
}

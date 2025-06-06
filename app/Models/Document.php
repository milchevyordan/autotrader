<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\Price;
use App\Enums\DocumentableType;
use App\Enums\DocumentStatus;
use App\Enums\PaymentCondition;
use App\Traits\HasChangeLogs;
use App\Traits\HasCreator;
use App\Traits\HasCustomer;
use App\Traits\HasFiles;
use App\Traits\HasInternalRemarks;
use App\Traits\HasMails;
use App\Traits\HasOwner;
use App\Traits\HasStatuses;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use HasCreator;
    use HasOwner;
    use HasFiles;
    use SoftDeletes;
    use HasInternalRemarks;
    use HasChangeLogs;
    use HasMails;
    use HasStatuses;
    use HasCustomer;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'customer_company_id',
        'customer_id',
        'documentable_type',
        'payment_condition',
        'payment_condition_free_text',
        'notes',
        'total_price_exclude_vat',
        'total_price_include_vat',
        'total_vat',
        'paid_at',
        'date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date'                    => 'date:Y-m-d',
        'paid_at'                 => 'date:Y-m-d',
        'total_price_exclude_vat' => Price::class,
        'total_vat'               => Price::class,
        'total_price_include_vat' => Price::class,
        'status'                  => DocumentStatus::class,
        'documentable_type'       => DocumentableType::class,
        'payment_condition'       => PaymentCondition::class,
    ];

    /**
     * Array that maps documentable type to it's correct relation.
     *
     * @var array|string[]
     */
    public array $typeRelationMap = [
        DocumentableType::Pre_order_vehicle->value        => 'preOrderVehicles',
        DocumentableType::Vehicle->value                  => 'vehicles',
        DocumentableType::Service_vehicle->value          => 'serviceVehicles',
        DocumentableType::Sales_order_down_payment->value => 'salesOrders',
        DocumentableType::Sales_order->value              => 'salesOrders',
        DocumentableType::Service_order->value            => 'serviceOrders',
        DocumentableType::Work_order->value               => 'workOrders',
    ];

    /**
     * Array holding fields that should be selected in the default dataTable structure.
     *
     * @var array|string[]
     */
    public static array $defaultSelectFields = ['id', 'creator_id', 'status', 'documentable_type', 'payment_condition', 'total_price_include_vat', 'created_at', 'updated_at'];

    /**
     * Array holding fields that should be selected in the summary card.
     *
     * @var array|string[]
     */
    public static array $summarySelectFields = ['id', 'creator_id', 'status', 'payment_condition', 'number', 'total_price_exclude_vat', 'total_price_include_vat'];

    /**
     * Return pre order vehicles if type is pre order vehicle.
     *
     * @return MorphToMany
     */
    public function preOrderVehicles(): MorphToMany
    {
        return $this->morphedByMany(PreOrderVehicle::class, 'documentable', 'documentables');
    }

    /**
     * Return vehicles if type is vehicles.
     *
     * @return MorphToMany
     */
    public function vehicles(): MorphToMany
    {
        return $this->morphedByMany(Vehicle::class, 'documentable', 'documentables');
    }

    /**
     * Return service vehicles if type is service vehicle.
     *
     * @return MorphToMany
     */
    public function serviceVehicles(): MorphToMany
    {
        return $this->morphedByMany(ServiceVehicle::class, 'documentable', 'documentables');
    }

    /**
     * Return sales orders if type is sales order.
     *
     * @return MorphToMany
     */
    public function salesOrders(): MorphToMany
    {
        return $this->morphedByMany(SalesOrder::class, 'documentable', 'documentables')->with('vehicles:id');
    }

    /**
     * Return service orders vehicles if type is service orders vehicle.
     *
     * @return MorphToMany
     */
    public function serviceOrders(): MorphToMany
    {
        return $this->morphedByMany(ServiceOrder::class, 'documentable', 'documentables');
    }

    /**
     * Return work orders if type is work order.
     *
     * @return MorphToMany
     */
    public function workOrders(): MorphToMany
    {
        return $this->morphedByMany(WorkOrder::class, 'documentable', 'documentables');
    }

    /**
     * Get all of the documentable models for the document.
     */
    public function documentables(): HasMany
    {
        return $this->hasMany(Documentable::class);
    }

    /**
     * Return document lines associated with that document.
     *
     * @return HasMany
     */
    public function lines(): HasMany
    {
        return $this->hasMany(DocumentLine::class);
    }
}

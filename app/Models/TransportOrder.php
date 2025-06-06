<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\Price;
use App\Enums\TransportableType;
use App\Enums\TransportOrderStatus;
use App\Enums\TransportType;
use App\Traits\HasCreator;
use App\Traits\HasFiles;
use App\Traits\HasInternalRemarks;
use App\Traits\HasMails;
use App\Traits\HasOwner;
use App\Traits\HasStatuses;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransportOrder extends Model
{
    use HasFiles;
    use HasCreator;
    use HasOwner;
    use SoftDeletes;
    use HasInternalRemarks;
    use HasMails;
    use HasStatuses;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'transport_company_use',
        'transport_company_id',
        'transporter_id',
        'pick_up_company_id',
        'pick_up_location_id',
        'pick_up_location_free_text',
        'pick_up_notes',
        'pick_up_after_date',
        'delivery_company_id',
        'delivery_location_id',
        'delivery_location_free_text',
        'delivery_notes',
        'deliver_before_date',
        'planned_delivery_date',
        'total_transport_price',
        'vehicle_type',
        'transport_type',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'transport_company_use' => 'boolean',
        'pick_up_after_date'    => 'datetime:Y-m-d H:i',
        'deliver_before_date'   => 'datetime:Y-m-d H:i',
        'planned_delivery_date' => 'datetime:Y-m-d H:i',
        'total_transport_price' => Price::class,
        'status'                => TransportOrderStatus::class,
        'transport_type'        => TransportType::class,
        'vehicle_type'          => TransportableType::class,
    ];

    /**
     * Array holding fields that should be selected in the default datatable structure.
     *
     * @var array|string[]
     */
    public static array $defaultSelectFields = ['id', 'creator_id', 'transporter_id', 'transport_company_id', 'status', 'transport_type', 'vehicle_type', 'total_transport_price', 'created_at', 'updated_at'];

    /**
     * Array holding fields that should be selected in the summary card.
     *
     * @var array|string[]
     */
    public static array $summarySelectFields = ['id', 'creator_id', 'transporter_id', 'transport_company_id', 'status', 'transport_type', 'transport_company_use', 'total_transport_price'];

    /**
     * Array that maps transport order type to it's correct relation.
     *
     * @var array|string[]
     */
    public array $typeRelationMap = [
        TransportableType::Vehicles->value           => 'vehicles',
        TransportableType::Pre_order_vehicles->value => 'preOrderVehicles',
        TransportableType::Service_vehicles->value   => 'serviceVehicles',
    ];

    /**
     * Array that maps transport order type to it's correct relation class.
     *
     * @var array|string[]
     */
    public array $typeRelationClassMap = [
        TransportableType::Vehicles->value           => Vehicle::class,
        TransportableType::Pre_order_vehicles->value => PreOrderVehicle::class,
        TransportableType::Service_vehicles->value   => ServiceVehicle::class,
    ];

    /**
     * Return transport order's transport supplier.
     *
     * @return BelongsTo
     */
    public function transportCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'transport_company_id');
    }

    /**
     * Return transport order's transport supplier.
     *
     * @return BelongsTo
     */
    public function transporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'transporter_id');
    }

    /**
     * Return vehicles related to the transport order if type is vehicles.
     *
     * @return MorphToMany
     */
    public function vehicles(): MorphToMany
    {
        return $this->morphedByMany(
            Vehicle::class,
            'transportable',
            'transportables'
        )->using(Transportable::class)->withPivot('location_id', 'location_free_text', 'price');
    }

    /**
     * Return service vehicles related to the transport order if type is service vehicles.
     *
     * @return MorphToMany
     */
    public function serviceVehicles(): MorphToMany
    {
        return $this->morphedByMany(
            ServiceVehicle::class,
            'transportable',
            'transportables',
            'transport_order_id', // Foreign key on the pivot table referencing TransportOrder
            'transportable_id' // Foreign key on the pivot table referencing the related model
        )->using(Transportable::class)->withPivot('location_id', 'location_free_text', 'price');
    }

    /**
     * Return pre order vehicles related to the transport order if type is pre order vehicles.
     *
     * @return MorphToMany
     */
    public function preOrderVehicles(): MorphToMany
    {
        return $this->morphedByMany(
            PreOrderVehicle::class,
            'transportable',
            'transportables',
            'transport_order_id', // Foreign key on the pivot table referencing TransportOrder
            'transportable_id'
        )->using(Transportable::class)->withPivot('location_id', 'location_free_text', 'price');
    }

    /**
     * Return pick up location addresses.
     *
     * @return BelongsTo
     */
    public function pickUpLocation(): BelongsTo
    {
        return $this->belongsTo(CompanyAddress::class, 'pick_up_location_id');
    }

    /**
     * Return pick up company.
     *
     * @return BelongsTo
     */
    public function pickUpCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'pick_up_company_id');
    }

    /**
     * Return delivery location addresses.
     *
     * @return BelongsTo
     */
    public function deliveryLocation(): BelongsTo
    {
        return $this->belongsTo(CompanyAddress::class, 'delivery_location_id');
    }

    /**
     * Return delivery company.
     *
     * @return BelongsTo
     */
    public function deliveryCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'delivery_company_id');
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Co2Type;
use App\Enums\Country;
use App\Enums\VehicleType;
use App\Traits\HasCreator;
use App\Traits\HasDocuments;
use App\Traits\HasOwner;
use App\Traits\HasTransports;
use App\Traits\HasWorkflow;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceVehicle extends Model
{
    use HasCreator;
    use HasTransports;
    use HasWorkflow;
    use HasDocuments;
    use SoftDeletes;
    use HasOwner;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'vehicle_type',
        'make_id',
        'vehicle_model_id',
        'variant_id',
        'co2_type',
        'co2_value',
        'kilometers',
        'nl_registration_number',
        'current_registration',
        'vin',
        'first_registration_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'current_registration'    => Country::class,
        'vehicle_type'            => VehicleType::class,
        'co2_type'                => Co2Type::class,
        'first_registration_date' => 'datetime:Y-m-d',
    ];

    /**
     * Array holding fields that should be selected in the default datatable structure.
     *
     * @var array|string[]
     */
    public static array $defaultSelectFields = ['id', 'creator_id', 'make_id', 'vehicle_model_id', 'variant_id', 'vin', 'created_at', 'updated_at', 'deleted_at'];

    /**
     * Return service vehicle's engine.
     *
     * @return BelongsTo
     */
    public function engine(): BelongsTo
    {
        return $this->belongsTo(Engine::class);
    }

    /**
     * Return service vehicle's make.
     *
     * @return BelongsTo
     */
    public function make(): BelongsTo
    {
        return $this->belongsTo(Make::class);
    }

    /**
     * Return service vehicle's variant.
     *
     * @return BelongsTo
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(Variant::class);
    }

    /**
     * Return service vehicle's model.
     *
     * @return BelongsTo
     */
    public function vehicleModel(): BelongsTo
    {
        return $this->belongsTo(VehicleModel::class);
    }

    /**
     * Return related service order.
     *
     * @return HasOne
     */
    public function serviceOrder(): HasOne
    {
        return $this->hasOne(ServiceOrder::class, 'service_vehicle_id');
    }

    /**
     * Return related work order.
     *
     * @return MorphOne
     */
    public function workOrder(): MorphOne
    {
        return $this->morphOne(WorkOrder::class, 'vehicleable');
    }
}

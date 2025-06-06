<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\Price;
use App\Enums\Damage;
use App\Enums\ImportOrOriginType;
use App\Enums\PaymentCondition;
use App\Enums\ServiceOrderStatus;
use App\Traits\HasCreator;
use App\Traits\HasCustomer;
use App\Traits\HasDocuments;
use App\Traits\HasFiles;
use App\Traits\HasImages;
use App\Traits\HasInternalRemarks;
use App\Traits\HasOwner;
use App\Traits\HasServiceLevel;
use App\Traits\HasStatuses;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceOrder extends Model
{
    use HasFactory;
    use HasCreator;
    use HasFiles;
    use HasImages;
    use SoftDeletes;
    use HasInternalRemarks;
    use HasServiceLevel;
    use HasStatuses;
    use HasOwner;
    use HasDocuments;
    use HasCustomer;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'service_vehicle_id',
        'customer_company_id',
        'customer_id',
        'seller_id',
        'service_level_id',
        'payment_condition',
        'payment_condition_free_text',
        'type_of_service',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status'            => ServiceOrderStatus::class,
        'type_of_service'   => ImportOrOriginType::class,
        'payment_condition' => PaymentCondition::class,
    ];

    /**
     * Array holding fields that should be selected in the default datatable structure.
     *
     * @var array|string[]
     */
    public static array $defaultSelectFields = ['id', 'creator_id', 'customer_company_id', 'customer_id', 'status', 'service_vehicle_id', 'created_at', 'updated_at', 'deleted_at'];

    /**
     * Array holding fields that should be selected in the summary card.
     *
     * @var array|string[]
     */
    public static array $summarySelectFields = ['id', 'creator_id', 'service_vehicle_id', 'customer_company_id', 'customer_id', 'seller_id', 'status'];

    /**
     * Relation to service order's seller
     *
     * @return BelongsTo
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Return service vehicle related to that service order.
     *
     * @return BelongsTo
     */
    public function serviceVehicle(): BelongsTo
    {
        return $this->belongsTo(ServiceVehicle::class, 'service_vehicle_id');
    }
}

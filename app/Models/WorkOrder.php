<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\Price;
use App\Enums\WorkOrderStatus;
use App\Enums\WorkOrderType;
use App\Traits\HasCreator;
use App\Traits\HasDocuments;
use App\Traits\HasFiles;
use App\Traits\HasInternalRemarks;
use App\Traits\HasOwner;
use App\Traits\HasStatuses;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkOrder extends Model
{
    use HasFiles;
    use HasCreator;
    use HasOwner;
    use SoftDeletes;
    use HasInternalRemarks;
    use HasStatuses;
    use HasDocuments;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'type',
        'total_price',
        'vehicleable_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'total_price' => Price::class,
        'status'      => WorkOrderStatus::class,
        'type'        => WorkOrderType::class,
    ];

    /**
     * Array holding fields that should be selected in the default datatable structure.
     *
     * @var array|string[]
     */
    public static array $defaultSelectFields = ['id', 'creator_id', 'total_price', 'type', 'status', 'created_at', 'updated_at', 'deleted_at'];

    /**
     * Array holding fields that should be selected in the summary card.
     *
     * @var array|string[]
     */
    public static array $summarySelectFields = ['id', 'creator_id', 'vehicleable_type', 'vehicleable_id', 'total_price', 'status', 'created_at'];

    /**
     * Return vehicle's morph relation.
     * (Service Vehicle / Vehicle).
     *
     * @return MorphTo
     */
    public function vehicleable(): MorphTo
    {
        return $this->morphTo('vehicleable');
    }

    /**
     * Return work order's task relation.
     *
     * @return HasMany
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(WorkOrderTask::class);
    }
}

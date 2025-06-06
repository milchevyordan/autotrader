<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\Price;
use App\Enums\WorkOrderTaskStatus;
use App\Enums\WorkOrderTaskType;
use App\Traits\HasCreator;
use App\Traits\HasFiles;
use App\Traits\HasImages;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkOrderTask extends Model
{
    use HasCreator;
    use HasImages;
    use HasFiles;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'work_order_id',
        'name',
        'description',
        'type',
        'status',
        'assigned_to_id',
        'estimated_price',
        'actual_price',
        'planned_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'type'            => WorkOrderTaskType::class,
        'status'          => WorkOrderTaskStatus::class,
        'estimated_price' => Price::class,
        'actual_price'    => Price::class,
        'planned_date'    => 'date:Y-m-d',
        'completed_at'    => 'date:Y-m-d H:i:s',
    ];

    /**
     * Return work order task's work order.
     *
     * @return BelongsTo
     */
    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }
}

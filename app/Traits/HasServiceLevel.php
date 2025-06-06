<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\OrderItem;
use App\Models\OrderService;
use App\Models\ServiceLevel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasServiceLevel
{
    /**
     * Items that are connected to the order or quote.
     *
     * @return MorphMany
     */
    public function orderItems(): MorphMany
    {
        return $this->morphMany(OrderItem::class, 'orderable')->with('item');
    }

    /**
     * Additional services that are connected to the order or quote.
     *
     * @return MorphMany
     */
    public function orderServices(): MorphMany
    {
        return $this->morphMany(OrderService::class, 'orderable');
    }

    /**
     * The service level relation to the order or quote.
     *
     * @return BelongsTo
     */
    public function serviceLevel(): BelongsTo
    {
        return $this->belongsTo(ServiceLevel::class);
    }
}

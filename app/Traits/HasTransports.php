<?php

declare(strict_types=1);

namespace App\Traits;

use App\Enums\TransportType;
use App\Models\TransportOrder;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasTransports
{
    /**
     * Transport orders relation.
     *
     * @return MorphToMany
     */
    public function transportOrders(): MorphToMany
    {
        return $this->morphToMany(TransportOrder::class, 'transportable');
    }

    /**
     * Transport order with type inbound relation.
     *
     * @return MorphToMany
     */
    public function transportOrderInbound(): MorphToMany
    {
        return $this->transportOrders()?->where('transport_type', TransportType::Inbound);
    }

    /**
     * Transport order with type outbound relation.
     *
     * @return MorphToMany
     */
    public function transportOrderOutbound(): MorphToMany
    {
        return $this->transportOrders()?->where('transport_type', TransportType::Outbound);
    }
}

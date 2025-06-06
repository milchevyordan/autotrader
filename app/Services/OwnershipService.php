<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\CacheTag;
use App\Enums\OwnershipStatus;
use App\Exceptions\OwnershipCancelledException;
use App\Models\Document;
use App\Models\Ownership;
use App\Models\PreOrder;
use App\Models\PurchaseOrder;
use App\Models\Quote;
use App\Models\QuoteInvitation;
use App\Models\SalesOrder;
use App\Models\ServiceOrder;
use App\Models\TransportOrder;
use App\Models\Vehicle;
use App\Models\WorkOrder;
use App\Services\DataTable\DataTable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class OwnershipService extends Service
{
    /**
     * Quote invitation model.
     *
     * @var Ownership
     */
    private Ownership $ownership;

    /**
     * Collection of DataTable<QuoteInvitation>.
     *
     * @return DataTable<QuoteInvitation>
     */
    public function getIndexMethodTable(): DataTable
    {
        return (new DataTable(
            auth()->user()->ownerships()->getQuery()->select(Ownership::$defaultSelectFields)
        ))
            ->setRelation('creator')
            ->setColumn('action', __('Action'), exportable: false)
            ->setColumn('id', '#', true, true)
            ->setColumn('creator.full_name', __('Creator'), true, true)
            ->setColumn('status', __('Status'), true, true)
            ->setColumn('resource', __('Resource'))
            ->setTimestamps()
            ->setEnumColumn('status', OwnershipStatus::class);
    }

    /**
     * Accept the quote invitation.
     *
     * @param  Ownership                   $ownership
     * @return void
     * @throws OwnershipCancelledException
     */
    public function accept(Ownership $ownership): void
    {
        if ($ownership->isCancelled()) {
            throw new OwnershipCancelledException('This ownership invitation is already cancelled');
        }

        self::cancelAllOwnershipInvitations($ownership);

        $ownership->changeStatus(OwnershipStatus::Accepted);

        Cache::tags(CacheTag::Pending_ownerships->value)->forget(auth()->id());
    }

    /**
     * Reject the quote invitation.
     *
     * @param  Ownership                   $ownership
     * @return void
     * @throws OwnershipCancelledException
     */
    public function reject(Ownership $ownership): void
    {
        if ($ownership->isCancelled()) {
            throw new OwnershipCancelledException('This ownership invitation is already cancelled');
        }

        $ownership->changeStatus(OwnershipStatus::Rejected);

        Cache::tags(CacheTag::Pending_ownerships->value)->forget(auth()->id());
    }

    /**
     * Close all quote invitations when invitation is accepted.
     *
     * @param  Ownership $ownership
     * @return void
     */
    public static function cancelAllOwnershipInvitations(Ownership $ownership): void
    {
        Ownership::where([
            'ownable_type' => $ownership->ownable_type,
            'ownable_id'   => $ownership->ownable_id,
        ])
            ->update(['status' => OwnershipStatus::Cancelled]);
    }

    /**
     * Return ownership with appropriate relations.
     *
     * @param  Ownership $ownership
     * @return Ownership
     */
    public function getOwnershipRelations(Ownership $ownership): Ownership
    {
        switch ($ownership->ownable_type) {
            case Vehicle::class:
                return $ownership->load([
                    'ownable:'.implode(',', Vehicle::$summarySelectFields),
                    'creator',
                    'ownable.creator',
                    'ownable.make:id,name',
                    'ownable.vehicleModel:id,name',
                    'ownable.variant:id,name',
                    'ownable.engine:id,name',
                    'ownable.calculation:vehicleable_id,vehicleable_type,sales_price_net',
                ]);
            case PreOrder::class:
                return $ownership->load([
                    'ownable:'.implode(',', PreOrder::$summarySelectFields),
                    'creator',
                    'ownable.creator',
                    'ownable.supplier:id,full_name',
                    'ownable.supplierCompany' => function ($query) {
                        $query->select('id', 'name')->withTrashed();
                    },
                    'ownable.intermediary:id,full_name',
                    'ownable.intermediaryCompany' => function ($query) {
                        $query->select('id', 'name')->withTrashed();
                    },
                    'ownable.purchaser:id,full_name',
                ]);
            case PurchaseOrder::class:
                return $ownership->load([
                    'ownable:'.implode(',', PurchaseOrder::$summarySelectFields),
                    'creator',
                    'ownable.creator',
                    'ownable.supplier:id,full_name',
                    'ownable.supplierCompany' => function ($query) {
                        $query->select('id', 'name')->withTrashed();
                    },
                    'ownable.intermediary:id,full_name',
                    'ownable.intermediaryCompany' => function ($query) {
                        $query->select('id', 'name')->withTrashed();
                    },
                    'ownable.purchaser:id,full_name',
                ]);
            case SalesOrder::class:
                return $ownership->load([
                    'ownable:'.implode(',', SalesOrder::$summarySelectFields),
                    'creator',
                    'ownable.creator',
                    'ownable.customer:id,full_name',
                    'ownable.customerCompany' => function ($query) {
                        $query->select('id', 'name')->withTrashed();
                    },
                    'ownable.seller:id,full_name',
                ]);
            case ServiceOrder::class:
                return $ownership->load([
                    'ownable:'.implode(',', ServiceOrder::$summarySelectFields),
                    'creator',
                    'ownable.creator',
                    'ownable.customer:id,full_name',
                    'ownable.customerCompany' => function ($query) {
                        $query->select('id', 'name')->withTrashed();
                    },
                    'ownable.seller:id,full_name',
                ]);
            case Quote::class:
                return $ownership->load([
                    'ownable:'.implode(',', Quote::$summarySelectFields),
                    'creator',
                    'ownable.creator',
                    'ownable.customer:id,full_name',
                    'ownable.customerCompany' => function ($query) {
                        $query->select('id', 'name')->withTrashed();
                    },
                ]);
            case Document::class:
                return $ownership->load([
                    'ownable:'.implode(',', Document::$summarySelectFields),
                    'creator',
                    'ownable.creator',
                ]);
            case WorkOrder::class:
                return $ownership->load([
                    'ownable:'.implode(',', WorkOrder::$summarySelectFields),
                    'creator',
                    'ownable.creator',
                    'ownable.tasks:id,work_order_id',
                ]);
            case TransportOrder::class:
                return $ownership->load([
                    'ownable:'.implode(',', TransportOrder::$summarySelectFields),
                    'creator',
                    'ownable.creator',
                    'ownable.transporter:id,full_name,company_id',
                    'ownable.transportCompany' => function ($query) {
                        $query->select('id', 'name')->withTrashed();
                    },
                ]);
            default:
                return $ownership;
        }
    }

    /**
     * Return pending ownerships.
     *
     * @param  Model $ownable
     * @return mixed
     */
    public static function getPending(Model $ownable): mixed
    {
        return $ownable->ownerships->where('status', OwnershipStatus::Pending)->values()->all();
    }

    /**
     * Return accepted ownership.
     *
     * @param  Model $ownable
     * @return mixed
     */
    public static function getAccepted(Model $ownable): mixed
    {
        return $ownable->ownerships->where('status', OwnershipStatus::Accepted)->first();
    }

    /**
     * Get the value of ownership.
     *
     * @return Ownership
     */
    public function getOwnership(): Ownership
    {
        return $this->ownership;
    }

    /**
     * Set the value of ownership.
     *
     * @param  Ownership $ownership
     * @return self
     */
    public function setOwnership(Ownership $ownership): self
    {
        $this->ownership = $ownership;

        return $this;
    }

    /**
     * Create auth ownership for the resources that are made with a single button.
     *
     * @param  Model $model
     * @return void
     */
    public static function createAuthOwnership(Model $model): void
    {
        Ownership::insert([
            'user_id'      => auth()->id(),
            'ownable_id'   => $model->id,
            'ownable_type' => $model::class,
            'creator_id'   => auth()->id(),
            'status'       => OwnershipStatus::Accepted,
            'created_at'   => now(),
        ]);
    }
}

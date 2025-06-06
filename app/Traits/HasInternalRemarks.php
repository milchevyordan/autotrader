<?php

declare(strict_types=1);

namespace App\Traits;

use App\Events\InternalRemarksUpdated;
use App\Models\InternalRemark;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasInternalRemarks
{
    /**
     * Return remarks with users, creator and replies relation.
     *
     * @return MorphMany
     */
    public function internalRemarks(): MorphMany
    {
        return $this->morphMany(InternalRemark::class, 'remarkable')->with(['users:id,full_name', 'creator', 'replies']);
    }

    /**
     * Fire event to send internalRemarks as emails and save them in system.
     *
     * @param       $validatedRequest
     * @return void
     */
    public function sendInternalRemarks($validatedRequest): void
    {
        if (! empty($validatedRequest['internal_remark_user_ids']) || ! empty($validatedRequest['internal_remark_role_ids'])) {
            event(new InternalRemarksUpdated($this, $validatedRequest['internal_remark_user_ids'] ?? [], $validatedRequest['internal_remark_role_ids'] ?? [], $validatedRequest['internal_remark']));
        }
    }
}

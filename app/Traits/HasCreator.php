<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasCreator
{
    /**
     * Return creator of resource.
     *
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id')
            ->select(
                'id',
                'full_name',
                'company_id',
            );
    }

    /**
     * Return record that have a creator in the same company as logged user.
     *
     * @param       $query
     * @param  ?int $companyId
     * @return void
     */
    public static function scopeInThisCompany($query, ?int $companyId = null): void
    {
        $query->whereHas('creator', function ($query) use ($companyId) {
            $query->where('company_id', $companyId ?? auth()->user()->company_id);
        });
    }
}

<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\CacheTag;
use App\Enums\Currency;
use App\Models\User;
use App\Support\EnumHelper;
use App\Support\StringHelper;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class CacheService extends Service
{
    /**
     * Create a new CacheService instance.
     */
    public function __construct()
    {
    }

    /**
     * Get the company currency.
     *
     * @return null|int
     */
    public function getUserCompanyCurrency(?User $user): ?int
    {
        return (int) Cache::tags(CacheTag::Currency->value)->remember($user?->company_id, now()->addHours(), function () use ($user) {
            return ((int) $user?->company?->default_currency?->value) ?? null;
        });
    }

    /**
     * Clear cache so notification count number is correct.
     *
     * @param  array $userIds
     * @return void
     */
    public static function clearUserNotificationCache(array $userIds): void
    {
        foreach ($userIds as $userId) {
            Cache::tags(CacheTag::User_notifications->value)->forget($userId);
        }
    }
}

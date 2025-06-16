<?php

declare(strict_types=1);

namespace App\Traits;

use App\Enums\CacheTag;
use App\Enums\MailType;
use App\Enums\OwnershipStatus;
use App\Models\Ownership;
use App\Models\User;
use App\Notifications\DatabaseNotification;
use App\Notifications\EmailNotification;
use App\Services\OwnershipService;
use App\Support\ModelHelper;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;

trait HasOwner
{
    /**
     * Default owner id field in the model.
     *
     * @var string
     */
    protected string $requestKeyOwner = 'owner_id';

    protected static function bootHasOwner(): void
    {
        static::saved(function ($model) {
            if (! $model->onSavedExecuted) {
                $model->onSaved();
                $model->onSavedExecuted = true;
            }
        });
    }

    /**
     * Method called after the model is updated.
     */
    public function onSaved(): void
    {
        $ownerId = request($this->requestKeyOwner);

        if (! $ownerId) {
            return;
        }

        $hasSameOwnership = $this->ownerships()
            ->where('user_id', $ownerId)
            ->where(function ($query) {
                $query->where('status', OwnershipStatus::Accepted)
                    ->orWhere('status', OwnershipStatus::Pending);
            })
            ->first();

        if ($hasSameOwnership) {
            return;
        }

        $modelUserOwnershipFields = [
            'user_id'      => $ownerId,
            'ownable_id'   => $this->id,
            'ownable_type' => static::class,
        ];

        $ownership = new Ownership($modelUserOwnershipFields);

        $ownership->creator_id = auth()->id();

        if (auth()->id() != $ownerId) {
            if ($this->ownerships()->doesntExist()) {
                Ownership::insert([
                    'user_id'      => auth()->id(),
                    'ownable_id'   => $this->id,
                    'ownable_type' => static::class,
                    'creator_id'   => auth()->id(),
                    'status'       => OwnershipStatus::Accepted,
                    'created_at'   => now(),
                ]);
            }

            $ownership->status = OwnershipStatus::Pending;
            $ownership->save();
            $this->sendOwnerChangedNotification($ownership, $ownerId);

            return;
        }

        OwnershipService::cancelAllOwnershipInvitations($ownership);
        $ownership->status = OwnershipStatus::Accepted;

        $ownership->save();
    }

    /**
     * Send a notification when the owner changes.
     *
     * @param  Ownership  $ownership
     * @param  int|string $requestOwnerId
     * @return void
     */
    protected function sendOwnerChangedNotification(Ownership $ownership, int|string $requestOwnerId): void
    {
        $notifiable = User::inThisCompany()->findOrFail($requestOwnerId);
        if (! $notifiable) {
            return;
        }

        $resourceNameWithId = ModelHelper::getModelNameWithId($this);
        $message = auth()->user()->full_name.__(' has requested your profile for the ownership of ').$resourceNameWithId;

        Notification::send($notifiable, new DatabaseNotification($message));

        Notification::send($notifiable, new EmailNotification(
            MailType::Owner_changed->value,
            $message,
            route('ownerships.show', $ownership->id),
            $resourceNameWithId
        ));

        Cache::forget($notifiable->id.CacheTag::Pending_ownerships->value);
    }

    /**
     * Get all the ownership relations.
     *
     * @return MorphMany
     */
    public function ownerships(): MorphMany
    {
        return $this->morphMany(Ownership::class, 'ownable');
    }

    /**
     * Return pending ownerships.
     *
     * @return MorphMany
     */
    public function pendingOwnerships(): MorphMany
    {
        return $this->ownerships()->where('status', OwnershipStatus::Pending);
    }

    /**
     * Get the ownership of the resource.
     *
     * @return MorphOne
     */
    public function acceptedOwnership(): MorphOne
    {
        return $this->morphOne(Ownership::class, 'ownable')
            ->where('status', OwnershipStatus::Accepted);
    }
}

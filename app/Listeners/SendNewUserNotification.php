<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Enums\MailType;
use App\Events\InternalRemarksUpdated;
use App\Models\InternalRemark;

use App\Models\User;
use App\Notifications\DatabaseNotification;
use App\Notifications\EmailNotification;
use App\Services\CacheService;
use App\Services\MailService;
use App\Support\ModelHelper;
use Illuminate\Support\Facades\Notification;

class SendNewUserNotification
{
    protected InternalRemarksUpdated $event;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @param InternalRemarksUpdated $event
     */
    public function handle(InternalRemarksUpdated $event): void
    {
        $this->event = $event;

        $userIds = [];

        if (! empty($this->event->users)) {
            $userIds = array_map('intval', $this->event->users);
        }

        if (! empty($this->event->roles)) {
            $usersWithRole = User::inThisCompany()->role(Role::whereIn('id', $this->event->roles)->pluck('name')->all())->pluck('id')->all();
            $userIds = array_merge($userIds, $usersWithRole);
        }

        $userIds = array_unique($userIds);

        $this->sendNotifications($userIds);
        $this->createInternalRemark($userIds);

        CacheService::clearUserNotificationCache($userIds);
    }

    /**
     * Create notification in the system, send email and save email in the system.
     *
     * @param  array $userIds
     * @return void
     */
    protected function sendNotifications(array $userIds): void
    {
        $users = User::whereIn('id', $userIds)->get();

        $resourceNameWithId = ModelHelper::getModelNameWithId($this->event->model);
        $message = auth()->user()->full_name.__(' has added remark to ').$resourceNameWithId;

        Notification::send($users, new DatabaseNotification($message));
        $editedInternalRemarksNotification = new EmailNotification(
            MailType::Internal_remarks->value,
            $message,
            ModelHelper::getEditRoute($this->event->model),
            $resourceNameWithId
        );
        Notification::send($users, $editedInternalRemarksNotification);

        (new MailService())->saveMailToSystem(
            $editedInternalRemarksNotification->toMail($users[0])->render(),
            $users->pluck('id')->toArray(),
            $this->event->model,
            'Email Notification'
        );
    }

    /**
     * Save internal remark in system.
     *
     * @param  array $userIds
     * @return void
     */
    protected function createInternalRemark(array $userIds): void
    {
        InternalRemark::create([
            'creator_id'      => auth()->id(),
            'remarkable_type' => get_class($this->event->model),
            'remarkable_id'   => $this->event->model->id,
            'note'            => $this->event->note,
        ])->users()->sync($userIds);
    }
}

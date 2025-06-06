<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Enums\CacheTag;
use App\Enums\MailType;
use App\Events\RedFlagDetected;
use App\Models\User;
use App\Notifications\DatabaseNotification;
use App\Notifications\EmailNotification;
use App\Services\MailService;
use App\Support\ModelHelper;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;

class SendRedFlagNotification
{
    protected RedFlagDetected $event;

    protected $users;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        $this->users = User::inThisCompany()->role('Management')->get();
    }

    /**
     * Handle the event.
     *
     * @param RedFlagDetected $event
     */
    public function handle(RedFlagDetected $event): void
    {
        $this->event = $event;

        Notification::send($this->users, new DatabaseNotification($this->event->message));

        $mailNotification = new EmailNotification(
            MailType::Red_flag->value,
            $this->event->message,
            $this->event->route,
            ModelHelper::getModelNameWithId($this->event->model)
        );
        Notification::send($this->users, $mailNotification);

        (new MailService())->saveMailToSystem(
            $mailNotification->toMail($this->users[0])->render(),
            $this->users,
            $this->event->model,
            'Red Flag Detected',
        );

        $this->clearUserNotificationCache();
    }

    /**
     * Clear cache so notification count number is correct.
     *
     * @return void
     */
    protected function clearUserNotificationCache(): void
    {
        foreach ($this->users as $user) {
            Cache::tags(CacheTag::User_notifications->value)->forget($user->id);
        }
    }
}

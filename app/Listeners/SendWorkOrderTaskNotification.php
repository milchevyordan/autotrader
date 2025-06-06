<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Enums\MailType;
use App\Events\AssignedToWorkOrderTask;
use App\Models\User;
use App\Notifications\DatabaseNotification;
use App\Notifications\EmailNotification;
use App\Services\CacheService;
use App\Services\MailService;
use App\Support\ModelHelper;
use Illuminate\Support\Facades\Notification;

class SendWorkOrderTaskNotification
{
    protected AssignedToWorkOrderTask $event;

    /**
     * Handle the event.
     *
     * @param AssignedToWorkOrderTask $event
     */
    public function handle(AssignedToWorkOrderTask $event): void
    {
        $this->event = $event;
        $assignedUserId = (int) $event->task->assigned_to_id;

        $this->sendNotification($assignedUserId);

        CacheService::clearUserNotificationCache([$assignedUserId]);
    }

    /**
     * Create notification in the system, send email and save email in the system.
     *
     * @param  int  $userId
     * @return void
     */
    protected function sendNotification(int $userId): void
    {
        $user = User::find($userId);
        $workOder = $this->event->task->workOrder()->first();

        $resourceNameWithId = ModelHelper::getModelNameWithId($workOder);
        $message = auth()->user()->full_name.__(' has assigned you task in ').$resourceNameWithId;

        Notification::send($user, new DatabaseNotification($message));
        $assignedToWorkOrderTaskNotification = new EmailNotification(
            MailType::Work_order_task->value,
            $message,
            ModelHelper::getEditRoute($workOder),
            'Work order #'.$this->event->task->work_order_id
        );
        Notification::send($user, $assignedToWorkOrderTaskNotification);

        (new MailService())->saveMailToSystem(
            $assignedToWorkOrderTaskNotification->toMail($user)->render(),
            collect([$user]),
            $workOder,
            'Email Notification'
        );
    }
}

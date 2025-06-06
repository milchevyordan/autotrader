<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Services\EmailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailNotification extends Notification
{
    use Queueable;

    private int $type;

    private string $message;

    private string $route;

    private string $resourceNameWithId;

    /**
     * Create a new notification instance.
     *
     * @param int    $type
     * @param string $message
     * @param string $route
     * @param string $resourceNameWithId
     */
    public function __construct(int $type, string $message, string $route, string $resourceNameWithId)
    {
        $this->type = $type;
        $this->message = $message;
        $this->route = $route;
        $this->resourceNameWithId = $resourceNameWithId;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  object             $notifiable
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  object      $notifiable
     * @return MailMessage
     */
    public function toMail(object $notifiable): MailMessage
    {
        $templateLines = EmailTemplateService::getByNotificationClass($this->type);

        $mailMessage = (new MailMessage());

        if ($templateLines->text_top) {
            $mailMessage->line(__($templateLines->text_top));
        }

        $mailMessage->line($this->message);

        $mailMessage->action('View '.$this->resourceNameWithId, secure_url($this->route));

        if ($templateLines->text_top) {
            $mailMessage->line(__($templateLines->text_bottom));
        }

        return $mailMessage;
    }
}

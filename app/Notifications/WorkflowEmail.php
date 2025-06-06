<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Services\Files\FileManager;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class WorkflowEmail extends Notification
{
    use Queueable;

    /**
     * @var string
     */
    private string $subject;

    /**
     * @var string
     */
    private string $text;

    /**
     * @var null|Collection
     */
    private ?Collection $attachments = null;

    /**
     * Create a new notification instance.
     *
     * @param string      $subject
     * @param string      $text
     * @param ?Collection $attachments
     */
    public function __construct(string $subject, string $text, ?Collection $attachments = null)
    {
        $this->subject = $subject;
        $this->text = $text;
        $this->subject = $subject;
        $this->attachments = $attachments;
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
     * @param object $notifiable
     */
    public function toMail(object $notifiable): MailMessage
    {
        /**
         * @var \App\Models\User $sender
         */
        $sender = Auth::user();

        $greeting = "Hello, {$notifiable->full_name}";

        $mailMessage = new MailMessage();

        $mailMessage
            ->greeting($greeting)
            ->subject($this->subject)
            ->line($this->text)
            ->salutation("Best regards, {$sender->full_name}");

        if (! $this->attachments) {
            return $mailMessage;
        }

        foreach ($this->attachments as $attachment) {
            $fileData = (new FileManager())->getFileNameAndPath($attachment['path']);

            $mailMessage->attach($fileData['filePath'], [
                'as' => $fileData['fileOriginalName'],
            ]);
        }

        return $mailMessage;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  object               $notifiable
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
        ];
    }
}

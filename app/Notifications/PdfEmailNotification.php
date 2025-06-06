<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Enums\MailType;
use App\Services\EmailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PdfEmailNotification extends Notification
{
    use Queueable;

    /**
     * Resource name and id.
     *
     * @var string
     */
    public $subject;

    /**
     * Original file name, the way the user uploaded it.
     *
     * @var string
     */
    public string $fileName;

    /**
     * Our custom file name.
     *
     * @var string
     */
    public string $filePath;

    /**
     * Custom text placed on top of mail.
     *
     * @var null|string
     */
    public string|null $customText;

    /**
     * Custom button route and name of button as associative array.
     *
     * @var null|array
     */
    public array|null $customButton;

    /**
     * Create a new notification instance.
     *
     * @param string  $subject
     * @param string  $fileName
     * @param string  $filePath
     * @param ?string $customText
     * @param ?array  $customButton
     */
    public function __construct(string $subject, string $fileName, string $filePath, ?string $customText = null, ?array $customButton = null)
    {
        $this->subject = $subject;
        $this->fileName = $fileName;
        $this->filePath = $filePath;
        $this->customText = $customText;
        $this->customButton = $customButton;
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
        $templateLines = EmailTemplateService::getByNotificationClass(MailType::Pdf->value);

        return (new MailMessage())->from(auth()->user()->email, auth()->user()->name)
            ->subject($this->subject)
            ->markdown('mail.pdf', [
                'subject'      => $this->subject,
                'textTop'      => $templateLines->text_top ?? '',
                'textBottom'   => $templateLines->text_bottom ?? '',
                'customText'   => $this->customText,
                'customButton' => $this->customButton,
            ])
            ->attach($this->filePath, [
                'as' => $this->fileName,
            ]);
    }
}

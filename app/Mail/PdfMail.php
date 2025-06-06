<?php

declare(strict_types=1);

namespace App\Mail;

use App\Enums\MailType;
use App\Services\EmailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;

class PdfMail extends Mailable implements ShouldQueue
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
     * Create a new message instance.
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
     * Build the message.
     *
     * @return $this
     */
    public function build(): static
    {
        $templateLines = EmailTemplateService::getByNotificationClass(MailType::Pdf->value);

        return $this;
    }
}

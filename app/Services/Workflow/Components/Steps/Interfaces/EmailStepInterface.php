<?php

declare(strict_types=1);

namespace App\Services\Workflow\Components\Steps\Interfaces;

interface EmailStepInterface
{
    /**
     * @return null|string
     */
    public function getEmailRecipient(): ?string;

    /**
     * @return string
     */
    public function getEmailSubject(): string;

    /**
     * @return string
     */
    public function getEmailTemplateText(): string;
}

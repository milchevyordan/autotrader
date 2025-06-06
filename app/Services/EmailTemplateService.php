<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\MailType;
use App\Models\EmailTemplate;
use App\Services\DataTable\DataTable;

class EmailTemplateService
{
    /**
     * Return email template datatable shown in settings panel.
     *
     * @return DataTable
     */
    public static function getIndexMethodTable(): DataTable
    {
        return (new DataTable(
            EmailTemplate::inThisCompany()->select(EmailTemplate::$defaultSelectFields)
        ))
            ->setColumn('action', __('Action'))
            ->setColumn('mail_type', __('Mail Type'), true, true)
            ->setColumn('name', __('Name'), true, true)
            ->setColumn('updated_at', __('Last Update'), true, true)
            ->setEnumColumn('type', MailType::class);
    }

    /**
     * Return appropriate email template based on provided type.
     *
     * @param  int   $type
     * @return mixed
     */
    public static function getByNotificationClass(int $type): mixed
    {
        return EmailTemplate::inThisCompany()->where('mail_type', $type)->first();
    }
}

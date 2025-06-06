<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Company;
use App\Models\Mail;
use App\Models\MailContent;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\DB;

class MailService extends Service
{
    /**
     * Per page pagination for infinite scroll.
     *
     * @var int
     */
    private int $perPage = 20;

    /**
     * Get perPage value.
     *
     * @return int
     */
    private function getPerPage(): int
    {
        return $this->perPage;
    }

    /**
     * Return chunk collection of in box mails.
     *
     * @return EloquentCollection
     */
    public function getInBoxMails(): EloquentCollection
    {
        $perPage = $this->getPerPage();
        $inboxPage = request()->input('inbox_page', 1);
        $user = auth()->user();

        return Mail::where(function ($q) use ($user) {
            $q->where('receivable_type', User::class)
                ->where('receivable_id', $user->id);
        })->orWhere(function ($q) use ($user) {
            $q->where('receivable_type', Company::class)
                ->where('receivable_id', $user->company_id);
        })
            ->with(['content', 'creator:id,email', 'receivable:id,email'])
            ->latest()
            ->skip(($inboxPage - 1) * $perPage)
            ->take($perPage)
            ->get();
    }

    /**
     * Return chunk collection of out box mails.
     *
     * @return EloquentCollection
     */
    public function getOutBoxMails(): EloquentCollection
    {
        $perPage = $this->getPerPage();
        $outboxPage = request()->input('outbox_page', 1);

        return Mail::where('creator_id', auth()->id())
            ->with(['content', 'receivable:id,email'])
            ->latest()
            ->skip(($outboxPage - 1) * $perPage)
            ->take($perPage)
            ->get();
    }

    /**
     * Save the already sent email in the system.
     *
     * @param              $content
     * @param EloquentCollection|SupportCollection $receivers
     * @param Model $model
     * @param string $subject
     * @param null|string $attachedFileName
     * @return void
     */
    public function saveMailToSystem($content, EloquentCollection|SupportCollection $receivers, Model $model, string $subject, ?string $attachedFileName = null): void
    {
        $mailContentId = MailContent::create([
            'content' => $content,
        ])->id;

        $mailInserts = [];
        foreach ($receivers as $receiver) {
            $mailInserts[] = [
                'creator_id'                => auth()->id(),
                'receivable_type'           => $receiver::class,
                'receivable_id'             => $receiver->id,
                'mailable_type'             => $model::class,
                'mailable_id'               => $model->id,
                'subject'                   => $subject,
                'mail_content_id'           => $mailContentId,
                'attached_file_unique_name' => $attachedFileName,
                'created_at'                => now(),
            ];
        }

        DB::table('mails')->insert($mailInserts);
    }
}

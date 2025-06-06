<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Mail;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasMails
{
    /**
     * Return mails with creator and receiver relation.
     */
    public function mails(): MorphMany
    {
        return $this->morphMany(Mail::class, 'mailable')->with(['content', 'creator:id,email', 'receivable:id,email']);
    }
}

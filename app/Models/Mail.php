<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasCreator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Mail extends Model
{
    use HasCreator;

    public const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'creator_id',
        'receivable_type',
        'receivable_id',
        'mail_content_id',
        'mailable_type',
        'mailable_id',
        'subject',
        'attached_file_unique_name',
    ];

    /**
     * Return the receiver that mail is connected to.
     *
     * @return MorphTo
     */
    public function receivable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Return resource that mail is connected to.
     *
     * @return MorphTo
     */
    public function mailable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get mail content.
     *
     * @return BelongsTo
     */
    public function content(): BelongsTo
    {
        return $this->belongsTo(MailContent::class, 'mail_content_id');
    }
}

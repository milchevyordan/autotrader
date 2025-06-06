<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\QuoteInvitationStatus;
use App\Traits\HasCreator;
use App\Traits\HasCustomer;
use App\Traits\HasRoleBasedQueries;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteInvitation extends Model
{
    use HasCreator;
    use HasRoleBasedQueries;
    use HasCustomer;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'quote_id',
        'customer_id',
    ];

    /**
     * Array holding fields that should be selected in the default datatable structure.
     *
     * @var string[]
     */
    public static array $defaultSelectFields = ['id', 'creator_id', 'quote_id', 'customer_id', 'status', 'created_at', 'updated_at'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => QuoteInvitationStatus::class,
    ];

    /**
     * Return quote invitation's quote.
     *
     * @return BelongsTo
     */
    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class, 'quote_id');
    }
}

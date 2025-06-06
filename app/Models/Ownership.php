<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OwnershipStatus;
use App\Traits\HasCreator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Ownership extends Model
{
    use HasCreator;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'ownable_id',
        'ownable_type',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => OwnershipStatus::class,
    ];

    /**
     * Specify what fields should not be included in change logs.
     *
     * @var array|string[]
     */
    public array $omittedInChangeLog = [
        'creator_id',
        'ownable_type',
        'ownable_id',
        'status',
    ];

    /**
     * Array holding fields that should be selected in the default datatable structure.
     *
     * @var array|string[]
     */
    public static array $defaultSelectFields = ['id', 'creator_id', 'ownable_type', 'ownable_id', 'status', 'created_at', 'updated_at'];

    /**
     * @return MorphTo
     */
    public function ownable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user that owns this ownership.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return bool
     */
    public function isCancelled(): bool
    {
        return OwnershipStatus::Cancelled == $this->status;
    }

    /**
     * @param  OwnershipStatus $status
     * @return void
     */
    public function changeStatus(OwnershipStatus $status): void
    {
        $this->status = $status;
        $this->save();
    }
}

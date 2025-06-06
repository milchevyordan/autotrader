<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PasswordResetToken extends Model
{
    /**
     * Table associated with this relation
     *
     * @var string
     */
    public $table = 'password_reset_tokens';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = ['email', 'token', 'created_at'];

    protected $primaryKey = 'email';

    public $incrementing = false;

    /**
     * Relation to the user
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'email', 'email');
    }

    protected $casts = [
        'created_at' => 'datetime',
    ];
}

<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasCreator;
use App\Traits\MapByColum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Variant extends Model
{
    use HasFactory;
    use HasCreator;
    use MapByColum;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'make_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Return variant's make.
     *
     * @return BelongsTo
     */
    public function make(): BelongsTo
    {
        return $this->belongsTo(Make::class);
    }

    /**
     * Array holding fields that should be selected in work builder dataTable structure.
     *
     * @var array|string[]
     */
    public static array $defaultSelectFields = ['id', 'creator_id', 'make_id', 'name', 'created_at', 'updated_at'];
}

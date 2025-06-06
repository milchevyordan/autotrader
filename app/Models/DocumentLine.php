<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\Price;
use App\Enums\DocumentLineType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentLine extends Model
{
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'document_id',
        'name',
        'vat_percentage',
        'price_exclude_vat',
        'vat',
        'price_include_vat',
        'documentable_id',
        'type',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price_exclude_vat' => Price::class,
        'vat'               => Price::class,
        'price_include_vat' => Price::class,
        'type'              => DocumentLineType::class,
    ];

    /**
     * Specify what fields should not be included in change logs.
     *
     * @var array|string[]
     */
    public array $omittedInChangeLog = [
        'document_id',
    ];

    /**
     * Get the document that owns the line.
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}

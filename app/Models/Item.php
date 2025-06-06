<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\Price;
use App\Enums\ItemType;
use App\Enums\UnitType;
use App\Traits\HasCreator;
use App\Traits\MapByColum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use HasFactory;
    use HasCreator;
    use MapByColum;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'unit_type',
        'type',
        'is_vat',
        'vat_percentage',
        'shortcode',
        'description',
        'purchase_price',
        'sale_price',
    ];

    /**
     * Specify what fields should not be included in change logs.
     *
     * @var array|string[]
     */
    public array $omittedInChangeLog = [
        'creator_id',
        'unit_type',
        'type',
        'is_vat',
        'vat_percentage',
        'description',
        'purchase_price',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_vat'         => 'boolean',
        'in_output'      => 'boolean',
        'unit_type'      => UnitType::class,
        'type'           => ItemType::class,
        'purchase_price' => Price::class,
        'sale_price'     => Price::class,
    ];

    /**
     * Array holding fields that should be selected in the default datatable structure.
     *
     * @var array|string[]
     */
    public static array $defaultSelectFields = ['id', 'creator_id', 'type', 'shortcode', 'description', 'purchase_price', 'sale_price', 'created_at'];

    /**
     * Return service levels that include that item.
     *
     * @return BelongsToMany
     */
    public function serviceLevels(): BelongsToMany
    {
        return $this->belongsToMany(ServiceLevel::class, 'item_service_level', 'item_id', 'service_level_id')->withPivot('service_level_id', 'in_output');
    }
}

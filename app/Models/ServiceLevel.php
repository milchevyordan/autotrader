<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\Price;
use App\Enums\Damage;
use App\Enums\ImportOrOriginType;
use App\Enums\PaymentCondition;
use App\Enums\ServiceLevelType;
use App\Traits\HasChangeLogs;
use App\Traits\HasCreator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceLevel extends Model
{
    use HasFactory;
    use HasCreator;
    use HasChangeLogs;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'type',
        'type_of_sale',
        'transport_included',
        'damage',
        'payment_condition',
        'payment_condition_free_text',
        'discount',
        'discount_in_output',
        'rdw_company_number',
        'login_autotelex',
        'api_japie',
        'bidder_name_autobid',
        'bidder_number_autobid',
        'api_vwe',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'transport_included' => 'boolean',
        'discount_in_output' => 'boolean',
        'damage'             => Damage::class,
        'payment_condition'  => PaymentCondition::class,
        'discount'           => Price::class,
        'type'               => ServiceLevelType::class,
        'type_of_sale'       => ImportOrOriginType::class,
    ];

    /**
     * Array holding fields that should be selected in the default datatable structure.
     *
     * @var array|string[]
     */
    public static array $defaultSelectFields = ['id', 'creator_id', 'type', 'name', 'created_at', 'updated_at'];

    /**
     * The section names that are allowed for additional services.
     *
     * @return BelongsToMany
     */
    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'item_service_level', 'service_level_id', 'item_id')->withPivot('service_level_id', 'in_output');
    }

    /**
     * The section names that are allowed for additional services.
     *
     * @return HasMany
     */
    public function additionalServices(): HasMany
    {
        return $this->hasMany(ServiceLevelService::class, 'service_level_id', 'id');
    }

    /**
     * Return all companies that the service level belongs to.
     *
     * @return BelongsToMany
     */
    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'company_service_level');
    }

    /**
     * Return all sales orders made with that service level.
     *
     * @return HasMany
     */
    public function salesOrders(): HasMany
    {
        return $this->hasMany(SalesOrder::class);
    }

    /**
     * Return all service orders made with that service level.
     *
     * @return HasMany
     */
    public function serviceOrders(): HasMany
    {
        return $this->hasMany(ServiceOrder::class);
    }

    /**
     * Return all quotes made with that service level.
     *
     * @return HasMany
     */
    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }
}

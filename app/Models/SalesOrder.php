<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\Price;
use App\Casts\Week;
use App\Enums\Currency;
use App\Enums\Damage;
use App\Enums\ImportOrOriginType;
use App\Enums\PaymentCondition;
use App\Enums\SalesOrderStatus;
use App\Traits\HasCreator;
use App\Traits\HasCustomer;
use App\Traits\HasDocuments;
use App\Traits\HasFiles;
use App\Traits\HasInternalRemarks;
use App\Traits\HasMails;
use App\Traits\HasOwner;
use App\Traits\HasServiceLevel;
use App\Traits\HasStatuses;
use App\Traits\HasVehicles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesOrder extends Model
{
    use HasFiles;
    use HasVehicles;
    use HasCreator;
    use HasOwner;
    use SoftDeletes;
    use HasDocuments;
    use HasInternalRemarks;
    use HasServiceLevel;
    use HasMails;
    use HasStatuses;
    use HasCustomer;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'customer_company_id',
        'customer_id',
        'seller_id',
        'reference',
        'type_of_sale',
        'transport_included',
        'vat_deposit',
        'service_level_id',
        'vat_percentage',
        'total_vehicles_purchase_price',
        'total_costs',
        'total_sales_price_service_items',
        'total_sales_margin',
        'total_fee_intermediate_supplier',
        'total_sales_price_exclude_vat',
        'total_sales_excl_vat_with_items',
        'total_vat',
        'total_bpm',
        'total_sales_price_include_vat',
        'total_sales_price',
        'is_brutto',
        'calculation_on_sales_order',
        'currency_rate',
        'down_payment',
        'down_payment_amount',
        'currency',
        'delivery_week',
        'damage',
        'payment_condition',
        'payment_condition_free_text',
        'additional_info_conditions',
        'discount',
        'discount_in_output',
        'total_registration_fees',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_brutto'                       => 'boolean',
        'calculation_on_sales_order'      => 'boolean',
        'transport_included'              => 'boolean',
        'vat_deposit'                     => 'boolean',
        'discount_in_output'              => 'boolean',
        'down_payment'                    => 'boolean',
        'down_payment_amount'             => Price::class,
        'total_sales_price'               => Price::class,
        'total_vehicles_purchase_price'   => Price::class,
        'total_costs'                     => Price::class,
        'total_sales_price_service_items' => Price::class,
        'total_sales_margin'              => Price::class,
        'total_fee_intermediate_supplier' => Price::class,
        'total_sales_price_exclude_vat'   => Price::class,
        'total_sales_excl_vat_with_items' => Price::class,
        'total_vat'                       => Price::class,
        'total_bpm'                       => Price::class,
        'total_sales_price_include_vat'   => Price::class,
        'discount'                        => Price::class,
        'total_registration_fees'         => Price::class,
        'payment_condition'               => PaymentCondition::class,
        'damage'                          => Damage::class,
        'type_of_sale'                    => ImportOrOriginType::class,
        'status'                          => SalesOrderStatus::class,
        'currency'                        => Currency::class,
        'delivery_week'                   => Week::class,
    ];

    /**
     * Set number field automatically on creation.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::created(function ($salesOrder) {
            $salesOrder->number = 'SO'.$salesOrder->id;
            $salesOrder->save();
        });
    }

    /**
     * Array holding fields that should be selected in the default datatable structure.
     * // do not remove down_payment_amount and vat_percentage and total_sales_price and is_brutto and total_sales_price_include_vat there is issue for that can be made in a smarter way.
     *
     * @var array|string[]
     */
    public static array $defaultSelectFields = ['id', 'creator_id', 'customer_company_id', 'seller_id', 'status', 'type_of_sale', 'total_sales_price', 'down_payment_amount', 'is_brutto', 'vat_percentage', 'total_sales_price_include_vat', 'created_at', 'updated_at', 'deleted_at'];

    /**
     * Array holding fields that should be selected in the summary card.
     *
     * @var array|string[]
     */
    public static array $summarySelectFields = ['id', 'creator_id', 'customer_company_id', 'customer_id', 'seller_id', 'status', 'type_of_sale', 'vat_deposit', 'vat_percentage', 'down_payment', 'down_payment_amount'];

    /**
     * Array holding fields that should be selected in the summary card.
     *
     * @var array|string[]
     */
    public static array $pdfSelectFields = ['id', 'transport_included', 'delivery_week', 'number', 'damage', 'payment_condition', 'service_level_id'];

    /**
     * Return sales order's seller.
     *
     * @return BelongsTo
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Quote that the sales order is created from.
     *
     * @return HasOne
     */
    public function quote(): HasOne
    {
        return $this->hasOne(Quote::class);
    }
}

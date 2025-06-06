<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\Price;
use App\Casts\Week;
use App\Enums\Damage;
use App\Enums\ImportOrOriginType;
use App\Enums\PaymentCondition;
use App\Enums\QuoteInvitationStatus;
use App\Enums\QuoteStatus;
use App\Traits\HasCreator;
use App\Traits\HasCustomer;
use App\Traits\HasFiles;
use App\Traits\HasInternalRemarks;
use App\Traits\HasMails;
use App\Traits\HasOwner;
use App\Traits\HasServiceLevel;
use App\Traits\HasStatuses;
use App\Traits\HasVehicles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quote extends Model
{
    use HasCreator;
    use HasOwner;
    use HasVehicles;
    use HasServiceLevel;
    use HasFiles;
    use HasMails;
    use HasStatuses;
    use SoftDeletes;
    use HasInternalRemarks;
    use HasCustomer;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'seller_id',
        'name',
        'service_level_id',
        'delivery_week',
        'damage',
        'transport_included',
        'vat_deposit',
        'type_of_sale',
        'payment_condition',
        'payment_condition_free_text',
        'discount',
        'discount_in_output',
        'additional_info_conditions',
        'down_payment',
        'down_payment_amount',
        'vat_percentage',
        'email_text',
        'total_vehicles_purchase_price',
        'total_costs',
        'total_sales_price_service_items',
        'total_sales_margin',
        'total_fee_intermediate_supplier',
        'total_sales_price_include_vat',
        'total_vat',
        'total_sales_price_exclude_vat',
        'total_bpm',
        'is_brutto',
        'calculation_on_quote',
        'total_quote_price',
        'total_quote_price_exclude_vat',
        'total_registration_fees',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_brutto'                       => 'boolean',
        'calculation_on_quote'            => 'boolean',
        'transport_included'              => 'boolean',
        'vat_deposit'                     => 'boolean',
        'discount_in_output'              => 'boolean',
        'reservation_until'               => 'datetime:Y-m-d H:i:s',
        'down_payment'                    => 'boolean',
        'down_payment_amount'             => Price::class,
        'type_of_sale'                    => ImportOrOriginType::class,
        'delivery_week'                   => Week::class,
        'total_sales_price_exclude_vat'   => Price::class,
        'total_vat'                       => Price::class,
        'total_sales_price_include_vat'   => Price::class,
        'total_bpm'                       => Price::class,
        'total_vehicles_purchase_price'   => Price::class,
        'total_costs'                     => Price::class,
        'total_sales_price_service_items' => Price::class,
        'total_sales_margin'              => Price::class,
        'total_fee_intermediate_supplier' => Price::class,
        'total_quote_price'               => Price::class,
        'total_quote_price_exclude_vat'   => Price::class,
        'total_registration_fees'         => Price::class,
        'discount'                        => Price::class,
        'payment_condition'               => PaymentCondition::class,
        'status'                          => QuoteStatus::class,
        'damage'                          => Damage::class,
    ];

    /**
     * Array holding fields that should be selected in the default datatable structure.
     *
     * @var string[]
     */
    public static array $defaultSelectFields = ['id', 'creator_id', 'status', 'name', 'delivery_week', 'created_at', 'updated_at'];

    /**
     * Array holding fields that should be selected in the summary card.
     *
     * @var array|string[]
     */
    public static array $summarySelectFields = ['id', 'creator_id', 'customer_company_id', 'customer_id', 'status', 'name', 'total_quote_price'];

    /**
     * Return send invitations.
     *
     * @return HasMany
     */
    public function quoteInvitations(): HasMany
    {
        return $this->hasMany(QuoteInvitation::class, 'quote_id');
    }

    /**
     * Return the accepted invitation.
     *
     * @return ?QuoteInvitation
     */
    public function acceptedInvitation(): ?QuoteInvitation
    {
        return $this->quoteInvitations->where('status', QuoteInvitationStatus::Accepted)->first();
    }

    /**
     * Return created from quote sales order.
     *
     * @return BelongsTo
     */
    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class, 'sales_order_id');
    }
}

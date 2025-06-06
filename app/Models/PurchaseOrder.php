<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\Price;
use App\Enums\Currency;
use App\Enums\NationalEuOrWorldType;
use App\Enums\Papers;
use App\Enums\PaymentCondition;
use App\Enums\PurchaseOrderStatus;
use App\Enums\SupplierOrIntermediary;
use App\Traits\HasCreator;
use App\Traits\HasFiles;
use App\Traits\HasIntermediary;
use App\Traits\HasInternalRemarks;
use App\Traits\HasMails;
use App\Traits\HasOwner;
use App\Traits\HasStatuses;
use App\Traits\HasSupplier;
use App\Traits\HasVehicles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use HasFactory;
    use HasFiles;
    use HasVehicles;
    use HasCreator;
    use HasSupplier;
    use HasIntermediary;
    use HasOwner;
    use SoftDeletes;
    use HasInternalRemarks;
    use HasMails;
    use HasStatuses;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'supplier_company_id',
        'supplier_id',
        'intermediary_company_id',
        'intermediary_id',
        'purchaser_id',
        'type',
        'transport_included',
        'vat_deposit',
        'vat_deposit_amount',
        'down_payment',
        'vat_percentage',
        'total_purchase_price',
        'total_purchase_price_eur',
        'total_fee_intermediate_supplier',
        'total_purchase_price_exclude_vat',
        'total_transport',
        'total_vat',
        'total_bpm',
        'total_purchase_price_include_vat',
        'currency_rate',
        'down_payment_amount',
        'total_payment_amount',
        'currency_po',
        'sales_restriction',
        'contact_notes',
        'document_from_type',
        'papers',
        'payment_condition',
        'payment_condition_free_text',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'transport_included'               => 'boolean',
        'vat_deposit'                      => 'boolean',
        'vat_deposit_amount'               => Price::class,
        'down_payment'                     => 'boolean',
        'down_payment_amount'              => Price::class,
        'total_payment_amount'             => Price::class,
        'total_purchase_price'             => Price::class,
        'total_purchase_price_eur'         => Price::class,
        'total_fee_intermediate_supplier'  => Price::class,
        'total_purchase_price_exclude_vat' => Price::class,
        'total_transport'                  => Price::class,
        'total_vat'                        => Price::class,
        'total_bpm'                        => Price::class,
        'total_purchase_price_include_vat' => Price::class,
        'type'                             => NationalEuOrWorldType::class,
        'status'                           => PurchaseOrderStatus::class,
        'document_from_type'               => SupplierOrIntermediary::class,
        'papers'                           => Papers::class,
        'payment_condition'                => PaymentCondition::class,
        'currency_po'                      => Currency::class,
    ];

    /**
     * Array holding fields that should be selected in the default datatable structure.
     *
     * @var array|string[]
     */
    public static array $defaultSelectFields = ['id', 'creator_id', 'supplier_company_id', 'purchaser_id', 'status', 'created_at', 'updated_at'];

    /**
     * Array holding fields that should be selected in the summary card.
     *
     * @var array|string[]
     */
    public static array $summarySelectFields = ['id', 'creator_id', 'supplier_company_id', 'supplier_id', 'intermediary_company_id', 'intermediary_id', 'purchaser_id', 'status', 'document_from_type', 'currency_po', 'vat_deposit', 'vat_percentage', 'down_payment', 'down_payment_amount'];

    /**
     * Return purchase order purchaser.
     *
     * @return BelongsTo
     */
    public function purchaser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'purchaser_id');
    }
}

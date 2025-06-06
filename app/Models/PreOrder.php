<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\Price;
use App\Enums\Currency;
use App\Enums\ImportEuOrWorldType;
use App\Enums\PreOrderStatus;
use App\Enums\SupplierOrIntermediary;
use App\Traits\HasChangeLogs;
use App\Traits\HasCreator;
use App\Traits\HasFiles;
use App\Traits\HasIntermediary;
use App\Traits\HasInternalRemarks;
use App\Traits\HasMails;
use App\Traits\HasOwner;
use App\Traits\HasStatuses;
use App\Traits\HasSupplier;
use App\Traits\HasVehicles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PreOrder extends Model
{
    use HasFiles;
    use HasCreator;
    use HasSupplier;
    use HasIntermediary;
    use HasVehicles;
    use SoftDeletes;
    use HasInternalRemarks;
    use HasMails;
    use HasStatuses;
    use HasOwner;
    use HasChangeLogs;

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
        'type',
        'purchaser_id',
        'pre_order_vehicle_id',
        'transport_included',
        'vat_deposit',
        'amount_of_vehicles',
        'down_payment',
        'vat_percentage',
        'currency_rate',
        'total_purchase_price',
        'total_purchase_price_eur',
        'total_fee_intermediate_supplier',
        'total_purchase_price_exclude_vat',
        'total_vat',
        'total_bpm',
        'total_purchase_price_include_vat',
        'down_payment_amount',
        'currency_po',
        'contact_notes',
        'document_from_type',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'transport_included'               => 'boolean',
        'vat_deposit'                      => 'boolean',
        'down_payment'                     => 'boolean',
        'down_payment_amount'              => Price::class,
        'total_purchase_price'             => Price::class,
        'total_purchase_price_eur'         => Price::class,
        'total_fee_intermediate_supplier'  => Price::class,
        'total_purchase_price_exclude_vat' => Price::class,
        'total_vat'                        => Price::class,
        'total_bpm'                        => Price::class,
        'total_purchase_price_include_vat' => Price::class,
        'type'                             => ImportEuOrWorldType::class,
        'status'                           => PreOrderStatus::class,
        'document_from_type'               => SupplierOrIntermediary::class,
        'currency_po'                      => Currency::class,
    ];

    /**
     * Array holding fields that should be selected in the default datatable structure.
     *
     * @var array|string[]
     */
    public static array $defaultSelectFields = ['id', 'creator_id', 'supplier_company_id', 'purchaser_id', 'status', 'type', 'created_at', 'updated_at'];

    /**
     * Array holding fields that should be selected in the summary card.
     *
     * @var array|string[]
     */
    public static array $summarySelectFields = ['id', 'creator_id', 'supplier_company_id', 'supplier_id', 'intermediary_company_id', 'intermediary_id', 'purchaser_id', 'status', 'document_from_type', 'currency_po', 'vat_deposit', 'vat_percentage', 'down_payment', 'down_payment_amount'];

    /**
     * Return pre order's purchaser.
     *
     * @return BelongsTo
     */
    public function purchaser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'purchaser_id');
    }

    /**
     * Return pre order vehicle relation.
     *
     * @return BelongsTo
     */
    public function preOrderVehicle(): BelongsTo
    {
        return $this->belongsTo(PreOrderVehicle::class);
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\Price;
use App\Enums\Currency;
use Illuminate\Database\Eloquent\Model;

class Calculation extends Model
{
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'is_vat',
        'is_locked',
        'intermediate',
        'original_currency',
        'selling_price_supplier',
        'sell_price_currency_euro',
        'vat_percentage',
        'net_purchase_price',
        'fee_intermediate',
        'total_purchase_price',
        'costs_of_damages',
        'transport_inbound',
        'transport_outbound',
        'costs_of_taxation',
        'recycling_fee',
        'purchase_cost_items_services',
        'sale_price_net_including_services_and_products',
        'sale_price_services_and_products',
        'discount',
        'sales_margin',
        'total_costs_with_fee',
        'sales_price_net',
        'vat',
        'sales_price_incl_vat_or_margin',
        'rest_bpm_indication',
        'leges_vat',
        'sales_price_total',
        'gross_bpm',
        'bpm_percent',
        'bpm',
        'currency_exchange_rate',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'original_currency'                              => Currency::class,
        'selling_price_supplier'                         => Price::class,
        'sell_price_currency_euro'                       => Price::class,
        'is_vat'                                         => 'boolean',
        'is_locked'                                      => 'boolean',
        'intermediate'                                   => 'boolean',
        'net_purchase_price'                             => Price::class,
        'fee_intermediate'                               => Price::class,
        'total_purchase_price'                           => Price::class,
        'costs_of_damages'                               => Price::class,
        'transport_inbound'                              => Price::class,
        'transport_outbound'                             => Price::class,
        'costs_of_taxation'                              => Price::class,
        'recycling_fee'                                  => Price::class,
        'purchase_cost_items_services'                   => Price::class,
        'sale_price_net_including_services_and_products' => Price::class,
        'sale_price_services_and_products'               => Price::class,
        'discount'                                       => Price::class,
        'sales_margin'                                   => Price::class,
        'total_costs_with_fee'                           => Price::class,
        'sales_price_net'                                => Price::class,
        'vat'                                            => Price::class,
        'leges_vat'                                      => Price::class,
        'sales_price_incl_vat_or_margin'                 => Price::class,
        'rest_bpm_indication'                            => Price::class,
        'sales_price_total'                              => Price::class,
        'gross_bpm'                                      => Price::class,
        'bpm_percent'                                    => Price::class,
        'bpm'                                            => Price::class,
    ];

    /**
     * Array holding fields that should be selected in purchase order vehicles dataTable.
     *
     * @var array|string[]
     */
    public static array $purchaseOrderSelectFields = ['vehicleable_type', 'vehicleable_id', 'original_currency', 'net_purchase_price', 'fee_intermediate', 'total_purchase_price', 'sales_price_total', 'bpm', 'currency_exchange_rate', 'leges_vat', 'transport_inbound'];

    /**
     * Array holding fields that should be selected in pdf creation.
     *
     * @var array|string[]
     */
    public static array $pdfSelectFields = ['vehicleable_type', 'vehicleable_id', 'sales_price_net', 'rest_bpm_indication', 'transport_inbound'];
}

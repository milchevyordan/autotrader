<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\Price;
use App\Casts\Week;
use App\Enums\Airconditioning;
use App\Enums\AppConnect;
use App\Enums\Camera;
use App\Enums\Coc;
use App\Enums\ColorType;
use App\Enums\Country;
use App\Enums\CruiseControl;
use App\Enums\DigitalCockpit;
use App\Enums\ExteriorColour;
use App\Enums\FuelType;
use App\Enums\Headlights;
use App\Enums\InteriorColour;
use App\Enums\InteriorMaterial;
use App\Enums\KeylessEntry;
use App\Enums\Navigation;
use App\Enums\Optics;
use App\Enums\Panorama;
use App\Enums\PDC;
use App\Enums\SeatHeating;
use App\Enums\SeatMassage;
use App\Enums\SeatsElectricallyAdjustable;
use App\Enums\SecondWheels;
use App\Enums\SportsPackage;
use App\Enums\SportsSeat;
use App\Enums\TintedWindows;
use App\Enums\TowBar;
use App\Enums\Transmission;
use App\Enums\VehicleBody;
use App\Enums\VehicleStatus;
use App\Enums\VehicleStock;
use App\Enums\VehicleType;
use App\Enums\Warranty;
use App\Traits\HasChangeLogs;
use App\Traits\HasCreator;
use App\Traits\HasDocuments;
use App\Traits\HasFiles;
use App\Traits\HasFollowers;
use App\Traits\HasImages;
use App\Traits\HasInternalRemarks;
use App\Traits\HasOwner;
use App\Traits\HasSupplier;
use App\Traits\HasTransports;
use App\Traits\HasWorkflow;
use App\Traits\MapByColum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasImages;
    use HasFiles;
    use HasCreator;
    use HasSupplier;
    use HasWorkflow;
    use SoftDeletes;
    use HasTransports;
    use HasDocuments;
    use HasFollowers;
    use HasInternalRemarks;
    use HasChangeLogs;
    use MapByColum;
    use HasOwner;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'make_id',
        'vehicle_model_id',
        'variant_id',
        'vehicle_model_free_text',
        'variant_free_text',
        'engine_id',
        'engine_free_text',
        'vehicle_group_id',
        'type',
        'body',
        'fuel',
        'current_registration',
        'transmission',
        'transmission_free_text',
        'coc',
        'specific_exterior_color',
        'factory_name_color',
        'specific_interior_color',
        'factory_name_interior',
        'interior_material',
        'panorama',
        'headlights',
        'digital_cockpit',
        'cruise_control',
        'keyless_entry',
        'air_conditioning',
        'vehicle_status',
        'stock',
        'pdc',
        'second_wheels',
        'camera',
        'tow_bar',
        'warranty',
        'navigation',
        'sports_seat',
        'seats_electrically_adjustable',
        'seat_heating',
        'seat_massage',
        'optics',
        'tinted_windows',
        'sports_package',
        'app_connect',
        'kw',
        'hp',
        'kilometers',
        'co2_wltp',
        'co2_nedc',
        'is_ready_to_be_sold',
        'purchase_repaired_damage',
        'option',
        'nl_registration_number',
        'color_type',
        'damage_description',
        'expected_date_of_availability_from_supplier',
        'expected_leadtime_for_delivery_from',
        'expected_leadtime_for_delivery_to',
        'first_registration_date',
        'first_registration_nl',
        'registration_nl',
        'registration_date_approval',
        'last_name_registration',
        'first_name_registration_nl',
        'registration_valid_until',
        'advert_link',
        'vehicle_reference',
        'supplier_reference_number',
        'supplier_given_damages',
        'gross_bpm_new',
        'rest_bpm_as_per_table',
        'calculation_bpm_in_so',
        'bpm_declared',
        'gross_bpm_recalculated_based_on_declaration',
        'gross_bpm_on_registration',
        'rest_bpm_to_date',
        'invoice_bpm',
        'bpm_post_change_amount',
        'depreciation_percentage',
        'supplier_company_id',
        'supplier_id',
        'vin',
        'warranty_free_text',
        'navigation_free_text',
        'app_connect_free_text',
        'panorama_free_text',
        'headlights_free_text',
        'digital_cockpit_free_text',
        'cruise_control_free_text',
        'keyless_entry_free_text',
        'air_conditioning_free_text',
        'pdc_free_text',
        'second_wheels_free_text',
        'camera_free_text',
        'tow_bar_free_text',
        'sports_seat_free_text',
        'seats_electrically_adjustable_free_text',
        'seat_heating_free_text',
        'seat_massage_free_text',
        'optics_free_text',
        'tinted_windows_free_text',
        'highlight_1',
        'highlight_2',
        'highlight_3',
        'highlight_4',
        'highlight_5',
        'highlight_6',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [];

    /**
     * Section where profile images are taken from.
     *
     * @var string
     */
    public string $profileImageSection = 'externalImages';

    /**
     * Array holding fields that should be selected in work builder datatable structure.
     *
     * @var array|string[]
     */
    public static array $defaultSelectFields = ['id', 'vin', 'creator_id', 'make_id', 'vehicle_model_id', 'engine_id', 'hp', 'kw', 'co2_wltp', 'created_at', 'updated_at', 'deleted_at'];

    /**
     * Array holding fields that should be selected in the summary card.
     *
     * @var array|string[]
     */
    public static array $summarySelectFields = ['id', 'vin', 'creator_id', 'make_id', 'vehicle_model_id', 'engine_id', 'transmission', 'color_type', 'hp', 'kilometers', 'kw', 'image_path'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_ready_to_be_sold'                         => 'boolean',
        'purchase_repaired_damage'                    => 'boolean',
        'expected_date_of_availability_from_supplier' => Week::class,
        'first_registration_date'                     => 'datetime:Y-m-d',
        'first_registration_nl'                       => 'datetime:Y-m-d',
        'registration_nl'                             => 'datetime:Y-m-d',
        'registration_date_approval'                  => 'datetime:Y-m-d',
        'last_name_registration'                      => 'datetime:Y-m-d',
        'first_name_registration_nl'                  => 'datetime:Y-m-d',
        'registration_valid_until'                    => 'datetime:Y-m-d',
        'warranty'                                    => Warranty::class,
        'navigation'                                  => Navigation::class,
        'app_connect'                                 => AppConnect::class,
        'type'                                        => VehicleType::class,
        'body'                                        => VehicleBody::class,
        'fuel'                                        => FuelType::class,
        'vehicle_status'                              => VehicleStatus::class,
        'stock'                                       => VehicleStock::class,
        'current_registration'                        => Country::class,
        'coc'                                         => Coc::class,
        'interior_material'                           => InteriorMaterial::class,
        'transmission'                                => Transmission::class,
        'specific_exterior_color'                     => ExteriorColour::class,
        'specific_interior_color'                     => InteriorColour::class,
        'panorama'                                    => Panorama::class,
        'headlights'                                  => Headlights::class,
        'digital_cockpit'                             => DigitalCockpit::class,
        'cruise_control'                              => CruiseControl::class,
        'keyless_entry'                               => KeylessEntry::class,
        'air_conditioning'                            => Airconditioning::class,
        'pdc'                                         => PDC::class,
        'second_wheels'                               => SecondWheels::class,
        'camera'                                      => Camera::class,
        'tow_bar'                                     => TowBar::class,
        'sports_seat'                                 => SportsSeat::class,
        'seats_electrically_adjustable'               => SeatsElectricallyAdjustable::class,
        'seat_heating'                                => SeatHeating::class,
        'seat_massage'                                => SeatMassage::class,
        'optics'                                      => Optics::class,
        'tinted_windows'                              => TintedWindows::class,
        'sports_package'                              => SportsPackage::class,
        'color_type'                                  => ColorType::class,
        'supplier_given_damages'                      => Price::class,
        'gross_bpm_new'                               => Price::class,
        'rest_bpm_as_per_table'                       => Price::class,
        'calculation_bpm_in_so'                       => Price::class,
        'bpm_declared'                                => Price::class,
        'gross_bpm_recalculated_based_on_declaration' => Price::class,
        'gross_bpm_on_registration'                   => Price::class,
        'rest_bpm_to_date'                            => Price::class,
        'invoice_bpm'                                 => Price::class,
        'bpm_post_change_amount'                      => Price::class,
    ];

    /**
     * Return vehicle's group.
     *
     * @return BelongsTo
     */
    public function vehicleGroup(): BelongsTo
    {
        return $this->belongsTo(VehicleGroup::class);
    }

    /**
     * Return vehicle's engine.
     *
     * @return BelongsTo
     */
    public function engine(): BelongsTo
    {
        return $this->belongsTo(Engine::class);
    }

    /**
     * Return vehicle's make.
     *
     * @return BelongsTo
     */
    public function make(): BelongsTo
    {
        return $this->belongsTo(Make::class);
    }

    /**
     * Return vehicle's variant.
     *
     * @return BelongsTo
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(Variant::class);
    }

    /**
     * Return vehicle's model.
     *
     * @return BelongsTo
     */
    public function vehicleModel(): BelongsTo
    {
        return $this->belongsTo(VehicleModel::class);
    }

    /**
     * Return vehicle's connected preorder.
     *
     * @return MorphToMany
     */
    public function preOrder(): MorphToMany
    {
        return $this->morphedByMany(PreOrder::class, 'vehicleable');
    }

    /**
     * Return vehicle's connected purchase order.
     *
     * @return MorphToMany
     */
    public function purchaseOrder(): MorphToMany
    {
        return $this->morphedByMany(PurchaseOrder::class, 'vehicleable');
    }

    /**
     * Return vehicle's connected sales order.
     *
     * @return MorphToMany
     */
    public function salesOrder(): MorphToMany
    {
        return $this->morphedByMany(SalesOrder::class, 'vehicleable');
    }

    /**
     * Return vehicle's connected quotes.
     *
     * @return MorphToMany
     */
    public function quotes(): MorphToMany
    {
        return $this->morphedByMany(Quote::class, 'vehicleable');
    }

    /**
     * Return vehicle's calculation.
     *
     * @return MorphOne
     */
    public function calculation(): MorphOne
    {
        return $this->morphOne(Calculation::class, 'vehicleable');
    }

    /**
     * Return vehicle's connected work order.
     *
     * @return MorphOne
     */
    public function workOrder(): MorphOne
    {
        return $this->morphOne(WorkOrder::class, 'vehicleable');
    }

    /**
     * Relation to transfer company token
     *
     * @return HasOne
     */
    public function transferToken(): HasOne
    {
        return $this->hasOne(VehicleTransferToken::class, 'vehicle_id', 'id');
    }
}

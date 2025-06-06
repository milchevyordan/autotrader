<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\Week;
use App\Enums\Airconditioning;
use App\Enums\AppConnect;
use App\Enums\Camera;
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
use App\Enums\VehicleType;
use App\Enums\Warranty;
use App\Traits\HasCreator;
use App\Traits\HasDocuments;
use App\Traits\HasFiles;
use App\Traits\HasImages;
use App\Traits\HasInternalRemarks;
use App\Traits\HasSupplier;
use App\Traits\HasTransports;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class PreOrderVehicle extends Model
{
    use HasImages;
    use HasFiles;
    use HasDocuments;
    use HasCreator;
    use HasSupplier;
    use HasTransports;
    use SoftDeletes;
    use HasInternalRemarks;

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
        'supplier_company_id',
        'supplier_id',
        'type',
        'body',
        'fuel',
        'vehicle_status',
        'current_registration',
        'interior_material',
        'transmission',
        'specific_exterior_color',
        'specific_interior_color',
        'panorama',
        'headlights',
        'digital_cockpit',
        'cruise_control',
        'keyless_entry',
        'air_conditioning',
        'pdc',
        'second_wheels',
        'camera',
        'tow_bar',
        'seat_heating',
        'seat_massage',
        'optics',
        'tinted_windows',
        'sports_package',
        'color_type',
        'warranty',
        'navigation',
        'sports_seat',
        'seats_electrically_adjustable',
        'app_connect',
        'komm_number',
        'factory_name_color',
        'factory_name_interior',
        'transmission_free_text',
        'vehicle_reference',
        'configuration_number',
        'kilometers',
        'production_weeks',
        'expected_delivery_weeks',
        'expected_leadtime_for_delivery_from',
        'expected_leadtime_for_delivery_to',
        'registration_weeks_from',
        'registration_weeks_to',
        'option',
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
        'sports_package_free_text',
        'highlight_1',
        'highlight_2',
        'highlight_3',
        'highlight_4',
        'highlight_5',
        'highlight_6',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'production_weeks'              => Week::class,
        'expected_delivery_weeks'       => Week::class,
        'warranty'                      => Warranty::class,
        'navigation'                    => Navigation::class,
        'app_connect'                   => AppConnect::class,
        'type'                          => VehicleType::class,
        'body'                          => VehicleBody::class,
        'fuel'                          => FuelType::class,
        'vehicle_status'                => VehicleStatus::class,
        'current_registration'          => Country::class,
        'interior_material'             => InteriorMaterial::class,
        'transmission'                  => Transmission::class,
        'specific_exterior_color'       => ExteriorColour::class,
        'specific_interior_color'       => InteriorColour::class,
        'panorama'                      => Panorama::class,
        'headlights'                    => Headlights::class,
        'digital_cockpit'               => DigitalCockpit::class,
        'cruise_control'                => CruiseControl::class,
        'keyless_entry'                 => KeylessEntry::class,
        'air_conditioning'              => Airconditioning::class,
        'pdc'                           => PDC::class,
        'second_wheels'                 => SecondWheels::class,
        'camera'                        => Camera::class,
        'tow_bar'                       => TowBar::class,
        'sports_seat'                   => SportsSeat::class,
        'seats_electrically_adjustable' => SeatsElectricallyAdjustable::class,
        'seat_heating'                  => SeatHeating::class,
        'seat_massage'                  => SeatMassage::class,
        'optics'                        => Optics::class,
        'tinted_windows'                => TintedWindows::class,
        'sports_package'                => SportsPackage::class,
        'color_type'                    => ColorType::class,
    ];

    /**
     * Array holding fields that should be selected in the default datatable structure.
     *
     * @var array|string[]
     */
    public static array $defaultSelectFields = ['id', 'creator_id', 'make_id', 'vehicle_model_id', 'engine_id', 'created_at', 'updated_at', 'deleted_at'];

    /**
     * Return vehicle engine.
     *
     * @return BelongsTo
     */
    public function engine(): BelongsTo
    {
        return $this->belongsTo(Engine::class);
    }

    /**
     * Return vehicle make.
     *
     * @return BelongsTo
     */
    public function make(): BelongsTo
    {
        return $this->belongsTo(Make::class);
    }

    /**
     * Return vehicle variant.
     *
     * @return BelongsTo
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(Variant::class);
    }

    /**
     * Return vehicle model.
     *
     * @return BelongsTo
     */
    public function vehicleModel(): BelongsTo
    {
        return $this->belongsTo(VehicleModel::class);
    }

    /**
     * Return vehicle calculation.
     *
     * @return MorphOne
     */
    public function calculation(): MorphOne
    {
        return $this->morphOne(Calculation::class, 'vehicleable');
    }

    /**
     * Return vehicle pre order.
     *
     * @return HasOne
     */
    public function preOrder(): HasOne
    {
        return $this->hasOne(PreOrder::class);
    }
}

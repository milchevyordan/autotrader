<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\Airconditioning;
use App\Enums\AppConnect;
use App\Enums\Camera;
use App\Enums\ColorType;
use App\Enums\Country;
use App\Enums\CruiseControl;
use App\Enums\Currency;
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
use App\Rules\FieldRelativeToOther;
use App\Rules\FileOrFileable;
use App\Rules\ImageOrImageable;
use App\Rules\ValidWeek;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePreOrderVehicleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'make_id'                                 => ['required', 'integer'],
            'variant_id'                              => ['nullable', 'integer'],
            'vehicle_model_free_text'                 => ['nullable', 'string', 'max:'.config('app.validation.rules.maxStringLength')],
            'variant_free_text'                       => ['nullable', 'string', 'max:'.config('app.validation.rules.maxStringLength')],
            'engine_id'                               => ['required', 'integer'],
            'engine_free_text'                        => ['nullable', 'string', 'max:'.config('app.validation.rules.maxStringLength')],
            'type'                                    => ['required', Rule::in(VehicleType::values())],
            'vehicle_model_id'                        => ['required', 'integer'],
            'supplier_company_id'                     => ['nullable', 'integer'],
            'supplier_id'                             => ['nullable', 'integer'],
            'body'                                    => ['nullable', Rule::in(VehicleBody::values())],
            'fuel'                                    => ['required', Rule::in(FuelType::values())],
            'vehicle_status'                          => ['nullable', Rule::in(VehicleStatus::values())],
            'current_registration'                    => ['nullable', Rule::in(Country::values())],
            'interior_material'                       => ['nullable', Rule::in(InteriorMaterial::values())],
            'transmission'                            => ['required', Rule::in(Transmission::values())],
            'specific_exterior_color'                 => ['nullable', Rule::in(ExteriorColour::values())],
            'specific_interior_color'                 => ['nullable', Rule::in(InteriorColour::values())],
            'panorama'                                => ['nullable', Rule::in(Panorama::values())],
            'headlights'                              => ['nullable', Rule::in(Headlights::values())],
            'digital_cockpit'                         => ['nullable', Rule::in(DigitalCockpit::values())],
            'cruise_control'                          => ['nullable', Rule::in(CruiseControl::values())],
            'keyless_entry'                           => ['nullable', Rule::in(KeylessEntry::values())],
            'air_conditioning'                        => ['nullable', Rule::in(Airconditioning::values())],
            'pdc'                                     => ['nullable', Rule::in(PDC::values())],
            'second_wheels'                           => ['nullable', Rule::in(SecondWheels::values())],
            'camera'                                  => ['nullable', Rule::in(Camera::values())],
            'tow_bar'                                 => ['nullable', Rule::in(TowBar::values())],
            'seat_heating'                            => ['nullable', Rule::in(SeatHeating::values())],
            'seat_massage'                            => ['nullable', Rule::in(SeatMassage::values())],
            'optics'                                  => ['nullable', Rule::in(Optics::values())],
            'tinted_windows'                          => ['nullable', Rule::in(TintedWindows::values())],
            'sports_package'                          => ['nullable', Rule::in(SportsPackage::values())],
            'color_type'                              => ['nullable', Rule::in(ColorType::values())],
            'warranty'                                => ['nullable', Rule::in(Warranty::values())],
            'navigation'                              => ['nullable', Rule::in(Navigation::values())],
            'sports_seat'                             => ['nullable', Rule::in(SportsSeat::values())],
            'seats_electrically_adjustable'           => ['nullable', Rule::in(SeatsElectricallyAdjustable::values())],
            'app_connect'                             => ['nullable', Rule::in(AppConnect::values())],
            'warranty_free_text'                      => ['nullable', 'string'],
            'navigation_free_text'                    => ['nullable', 'string'],
            'app_connect_free_text'                   => ['nullable', 'string'],
            'panorama_free_text'                      => ['nullable', 'string'],
            'headlights_free_text'                    => ['nullable', 'string'],
            'digital_cockpit_free_text'               => ['nullable', 'string'],
            'cruise_control_free_text'                => ['nullable', 'string'],
            'keyless_entry_free_text'                 => ['nullable', 'string'],
            'air_conditioning_free_text'              => ['nullable', 'string'],
            'pdc_free_text'                           => ['nullable', 'string'],
            'second_wheels_free_text'                 => ['nullable', 'string'],
            'camera_free_text'                        => ['nullable', 'string'],
            'tow_bar_free_text'                       => ['nullable', 'string'],
            'sports_seat_free_text'                   => ['nullable', 'string'],
            'seats_electrically_adjustable_free_text' => ['nullable', 'string'],
            'seat_heating_free_text'                  => ['nullable', 'string'],
            'seat_massage_free_text'                  => ['nullable', 'string'],
            'optics_free_text'                        => ['nullable', 'string'],
            'tinted_windows_free_text'                => ['nullable', 'string'],
            'sports_package_free_text'                => ['nullable', 'string'],
            'highlight_1'                             => ['nullable', 'string', 'max:'.config('app.validation.rules.maxStringLength')],
            'highlight_2'                             => ['nullable', 'string', 'max:'.config('app.validation.rules.maxStringLength')],
            'highlight_3'                             => ['nullable', 'string', 'max:'.config('app.validation.rules.maxStringLength')],
            'highlight_4'                             => ['nullable', 'string', 'max:'.config('app.validation.rules.maxStringLength')],
            'highlight_5'                             => ['nullable', 'string', 'max:'.config('app.validation.rules.maxStringLength')],
            'highlight_6'                             => ['nullable', 'string', 'max:'.config('app.validation.rules.maxStringLength')],
            'komm_number'                             => ['nullable', 'string', 'max:'.config('app.validation.rules.maxStringLength')],
            'factory_name_color'                      => ['nullable', 'string', 'max:'.config('app.validation.rules.maxStringLength')],
            'factory_name_interior'                   => ['nullable', 'string', 'max:'.config('app.validation.rules.maxStringLength')],
            'transmission_free_text'                  => ['nullable', 'string', 'max:550'],
            'vehicle_reference'                       => ['nullable', 'string', 'max:'.config('app.validation.rules.maxStringLength')],
            'configuration_number'                    => ['nullable', 'string', 'max:'.config('app.validation.rules.maxStringLength')],
            'kilometers'                              => ['nullable', 'integer', 'min:0', 'max:'.config('app.validation.rules.maxIntegerValue')],
            'production_weeks'                        => ['nullable', 'array', new ValidWeek()],
            'expected_delivery_weeks'                 => ['nullable', 'array', new ValidWeek()],
            'expected_leadtime_for_delivery_from'     => ['nullable', 'integer', 'min:0', 'max:255', new FieldRelativeToOther('expected_leadtime_for_delivery_to', '<=', 'number')],
            'expected_leadtime_for_delivery_to'       => ['nullable', 'integer', 'min:0', 'max:255', new FieldRelativeToOther('expected_leadtime_for_delivery_from', '>=', 'number')],
            'registration_weeks_from'                 => ['nullable', 'integer', 'min:0', 'max:255', new FieldRelativeToOther('registration_weeks_to', '<=', 'number')],
            'registration_weeks_to'                   => ['nullable', 'integer', 'min:0', 'max:255', new FieldRelativeToOther('registration_weeks_from', '>=', 'number')],
            'currency_exchange_rate'                  => ['nullable', 'numeric', 'min:0'],
            'option'                                  => ['nullable', 'string'],
            'is_vat'                                  => ['required', 'boolean'],
            'is_locked'                               => ['required', 'boolean'],
            'intermediate'                            => ['required', 'boolean'],
            'original_currency'                       => ['nullable', Rule::in(Currency::values())],
            'selling_price_supplier'                  => ['nullable', 'string'],
            'sell_price_currency_euro'                => ['nullable', 'string'],
            'vat_percentage'                          => ['nullable', 'integer', 'between:17,27'],
            'net_purchase_price'                      => ['nullable', 'string'],
            'fee_intermediate'                        => ['required_if_accepted:intermediate', 'nullable', 'string'],
            'total_purchase_price'                    => ['nullable', 'string'],
            'costs_of_damages'                        => ['nullable', 'string'],
            'transport_inbound'                       => ['nullable', 'string'],
            'transport_outbound'                      => ['nullable', 'string'],
            'costs_of_taxation'                       => ['nullable', 'string'],
            'recycling_fee'                           => ['nullable', 'string'],
            'sales_margin'                            => ['nullable', 'string'],
            'total_costs_with_fee'                    => ['nullable', 'string'],
            'sales_price_net'                         => ['nullable', 'string'],
            'vat'                                     => ['nullable', 'string'],
            'sales_price_incl_vat_or_margin'          => ['nullable', 'string'],
            'rest_bpm_indication'                     => ['nullable', 'string'],
            'leges_vat'                               => ['nullable', 'string'],
            'sales_price_total'                       => ['nullable', 'string'],
            'gross_bpm'                               => ['nullable', 'string'],
            'bpm_percent'                             => ['nullable', 'string'],
            'bpm'                                     => ['nullable', 'string'],
            'internal_remark_user_ids'                => ['nullable', 'array'],
            'internal_remark_role_ids'                => ['nullable', 'array'],
            'internal_remark'                         => ['nullable', 'required_with:internal_remark_user_ids,internal_remark_role_ids', 'string'],
            'internalImages.*'                        => [
                new ImageOrImageable(),
            ],
            'externalImages.*' => [
                new ImageOrImageable(),
            ],
            'internalFiles.*' => [
                new FileOrFileable(),
            ],
            'externalFiles.*' => [
                new FileOrFileable(),
            ],
        ];
    }
}

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8"/>
    <title>{{ __('Quote') }} {{ $quote->id }}</title>

    @include('styles.reset')
    @include('styles.pdf')
</head>

<body>
<div class="header-orange w-full">
    <div class="align-vertical text-white">
        @include('components.quote-invitations.title')
    </div>
    <img src="{{ public_path($headerQuoteTransportAndDeclarationImage) }}" alt="header quote transport and declaration image" class="overlay-image">
</div>

<div class="container">
    @foreach ($quote->vehicles as $key => $vehicle)
        @php
            $priceNetUnits =
                \App\Support\CurrencyHelper::convertCurrencyToUnits(
                    $vehicle->calculation->sales_price_net,
                ) + \App\Support\CurrencyHelper::convertCurrencyToUnits($quote->discount) + $salesPriceServiceItemsPerVehicleUnits;
            $priceNet = \App\Support\CurrencyHelper::convertUnitsToCurrency($priceNetUnits);
            $vatUnits = $priceNetUnits * ($vehicle->calculation->vat_percentage / 100);
            $vat = \App\Support\CurrencyHelper::convertUnitsToCurrency($vatUnits);
            $bpmUnits = \App\Support\CurrencyHelper::convertCurrencyToUnits(
                $vehicle->calculation->rest_bpm_indication,
            );
            $regFeesUnits = \App\Support\CurrencyHelper::convertCurrencyToUnits(
                $vehicle->calculation->leges_vat,
            );

            $priceBrutto = \App\Support\CurrencyHelper::convertUnitsToCurrency(
                $priceNetUnits + $vatUnits + $bpmUnits + $regFeesUnits,
            );
        @endphp
        <div class="vehicle-box border-top pt-1 mb-1 break-inside-avoid">
            <table class="w-full">
                <tr>
                    <td class="width-half align-top px-half">
                        @include('components.vehicles.title-sales-order-and-quote')

                        <table class="w-full">
                            <tr>
                                <td class="width-third align-top px-half">
                                    <table class="vehicle-box__images pt-half">
                                        @if ($vehicle?->images->isEmpty())
                                            <tr>
                                                <td>
                                                    <img src="{{ public_path('/images/no-image.webp') }}"
                                                         class="image mb-half" width="25px"
                                                         alt="default image"/>
                                                </td>
                                            </tr>
                                        @else
                                            @foreach ($vehicle?->images as $image)
                                                <tr>
                                                    <td>
                                                        <img class="image mb-half" width="25px"
                                                             src="data:image/png;base64, {{$image['base64'] }}"
                                                             alt="vehicle image"/>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </table>
                                </td>

                                <td class="width-two-thirds align-top px-half text-s comma-separated">
                                    @foreach ($attributes as $item)
                                        @include('components.vehicles.highlight', [
                                            'quoteSingleVehicle' => false,
                                        ])
                                    @endforeach
                                    @foreach (['highlight_1', 'highlight_2', 'highlight_3', 'highlight_4', 'highlight_5', 'highlight_6'] as $highlight)
                                        @unless ($vehicle->$highlight)
                                            @continue
                                        @endunless

                                        <span>{{ $vehicle->$highlight }}</span>
                                    @endforeach
                                </td>
                            </tr>
                        </table>
                    </td>

                    <td class="width-half align-top px-half font-7and44">
                        <table class="w-full">
                            <tr>
                                <td class="width-half align-top px-half">
                                    <table>
                                        @if (!empty($vehicle->nl_registration_number))
                                            <tr class="border-bottom">
                                                <td class="p-vehicle-information">{{ __('Registration nr') }}</td>
                                                <td class="p-vehicle-information fw-bold">{{ $vehicle->nl_registration_number }}</td>
                                            </tr>
                                        @else
                                            <tr class="border-bottom">
                                                <td class="p-vehicle-information">{{ __('VIN nr') }}</td>
                                                <td class="p-vehicle-information fw-bold">{{ $vehicle->vin }}</td>
                                            </tr>
                                        @endif

                                        <tr class="border-bottom">
                                            <td class="p-vehicle-information"> {{ __('1st registration') }}</td>
                                            <td class="p-vehicle-information fw-bold">
                                                {{ $vehicle->first_registration_date ? \Carbon\Carbon::parse($vehicle->first_registration_date)->format('m-Y') : '' }}
                                            </td>
                                        </tr>

                                        <tr class="border-bottom">
                                            <td class="p-vehicle-information"> {{ __('Mileage') }}</td>
                                            <td class="p-vehicle-information fw-bold">
                                                {{ $vehicle->kilometers ? $vehicle->kilometers . ' km' : '' }}
                                            </td>
                                        </tr>

                                        <tr class="border-bottom">
                                            <td class="p-vehicle-information">{{ __('Vehicle nr') }}</td>
                                            <td class="p-vehicle-information fw-bold">{{ $vehicle->id }}</td>
                                        </tr>

                                        <tr class="border-bottom">
                                            <td class="p-vehicle-information">{{ __('Fuel') }} {{ __('Type') }}</td>
                                            <td class="p-vehicle-information fw-bold">
                                                {{ \App\Support\StringHelper::replaceUnderscores($vehicle->fuel?->name) }}
                                            </td>
                                        </tr>

                                        <tr class="border-bottom">
                                            <td class="p-vehicle-information">{{ __('Body') }} {{ __('Type') }}</td>
                                            <td class="p-vehicle-information fw-bold">{{ $vehicle->body?->name }}</td>
                                        </tr>

                                        <tr class="border-bottom">
                                            <td class="p-vehicle-information">{{ __('Color') }}</td>
                                            <td class="p-vehicle-information fw-bold">
                                                {{ $vehicle->specific_exterior_color?->name }} {{ $vehicle->color_type?->name }} {{ $vehicle->factory_name_color }}
                                            </td>
                                        </tr>

                                        <tr class="border-bottom">
                                            <td class="p-vehicle-information">{{ __('Interior') }}</td>
                                            <td class="p-vehicle-information fw-bold">
                                                {{ $vehicle->specific_interior_color?->name }}
                                                {{ $vehicle->factory_name_interior }}
                                                {{ $vehicle->interior_material?->translatedName() }}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td class="p-vehicle-information">{{ __('Available') }}</td>
                                            <td class="p-vehicle-information fw-bold">
                                                {{ \App\Services\WeekService::generateWeekInputString($vehicle->pivot->delivery_week) }}
                                            </td>
                                        </tr>
                                    </table>
                                </td>

                                <td class="width-half align-top px-half">
                                    <table>
                                        <tr>
                                            <td class="align-right fw-bold" colspan="2">{{ $loop->iteration }}</td>
                                        </tr>
                                        @if($quote->calculation_on_quote && $allItemsAndAdditionals)
                                            <tr class="border-bottom">
                                                <td class="p-vehicle-information">{{ __('Sales Price net') }}</td>
                                                <td class="p-vehicle-information fw-bold no-wrap font-9">
                                                    € {{ $vehicle->calculation->sales_price_net }}</td>
                                            </tr>
                                        @endif
                                        @if($allItemsAndAdditionals)
                                            <tr class="border-bottom">
                                                <td class="p-vehicle-information">{{ __('Services & products') }}</td>
                                                <td class="fw-bold no-wrap">
                                                    € {{ $salesPriceServiceItemsPerVehicle }}</td>
                                            </tr>
                                        @endif
                                        @if($quote->discount && $quote->discount_in_output)
                                            <tr class="border-bottom">
                                                <td class="p-vehicle-information">{{ __('Discount') }}</td>
                                                <td class="fw-bold no-wrap">€ {{ $quote->discount }}</td>
                                            </tr>
                                        @endif
                                        @if($quote->calculation_on_quote || !$quote->is_brutto)
                                            <tr>
                                                <td class="p-vehicle-information">{{ __('Price net') }}</td>
                                                <td class="@if(!$quote->calculation_on_quote) text-price @endif fw-bold no-wrap font-9">
                                                    € {{ $priceNet }}</td>
                                            </tr>
                                        @endif
                                        @if($quote->is_brutto)
                                            @if($quote->calculation_on_quote)
                                                <tr class="border-bottom">
                                                    <td class="p-vehicle-information">VAT
                                                        ({{ $vehicle->calculation->vat_percentage }}
                                                        %)
                                                    </td>
                                                    <td class="fw-bold no-wrap">€ {{ $vat }}</td>
                                                </tr>

                                                <tr class="border-bottom">
                                                    <td class="p-vehicle-information">{{ __('BPM') }}</td>
                                                    <td class="p-vehicle-information no-wrap">
                                                        € {{ $vehicle->calculation->rest_bpm_indication }}</td>
                                                </tr>

                                                <tr class="border-bottom">
                                                    <td class="p-vehicle-information">{{ __('Reg fees (0%)') }}</td>
                                                    <td class="fw-bold no-wrap">
                                                        € {{ $vehicle->calculation->leges_vat }}</td>
                                                </tr>
                                            @endif
                                            <tr class="border-bottom">
                                                <td class="p-vehicle-information">{{ __('Price brutto (in/in)') }}</td>
                                                <td class="text-price fw-bold font-9 no-wrap">€ {{ $priceBrutto }}</td>
                                            </tr>
                                        @endif
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    @endforeach
</div>

<div class="container break-inside-avoid">
    <div class="w-full mt-1 bg-gray rounded text-s">
        <div class="p-1">
            <div class="fw-bold pb-half font-10">{{ __('Quotation Conditions & Remarks') }}</div>
            <div
                class="pt-half">{{ __('Delivery indication week') }} {{ \App\Services\WeekService::generateWeekInputString($quote->delivery_week) }}</div>
            <div class="pt-half">{{ \App\Support\StringHelper::replaceUnderscores($quote->damage?->name) }}</div>
            <div class="pt-half">{{ __('Transport') }}
                {{ $quote->transport_included ? __('Included') : __('Pick up by buyer') }}</div>
            <div
                class="pt-half">{{ \App\Support\StringHelper::replaceUnderscores($quote->payment_condition?->name) }}</div>
            @if($quote->payment_condition?->value == \App\Enums\PaymentCondition::See_additional_information->value)
                <div class="pt-half">{{ $quote->payment_condition_free_text }}</div>
            @endif
            <div class="pt-half">{{$quote->additional_info_conditions}}</div>
        </div>
    </div>
</div>

<div class="container break-inside-avoid">
    <table class="w-full mt-1">
        @if($quote->calculation_on_quote && $allItemsAndAdditionals)
            <tr class="border-bottom fw-bold">
                <td class="p-quarter width-80 font-9">{{ __('Total Sales price net') }}
                    , {{ $vehiclesCount }} {{ __('vehicles') }}
                </td>
                <td class="p-quarter no-wrap font-10">€ {{ $quote->total_sales_price_exclude_vat }}</td>
            </tr>
        @endif
        @foreach ($quote->orderItems as $item)
            @if (!$item->in_output)
                @continue
            @endif

            <tr class="border-bottom">
                <td class="p-quarter width-80 font-9">{{ $item->item?->shortcode }} ({{ $vehiclesCount }}x)</td>
                <td class="p-quarter fw-bold no-wrap font-10">
                    € {{ \App\Support\CurrencyHelper::convertUnitsToCurrency(\App\Support\CurrencyHelper::convertCurrencyToUnits($item->sale_price) * $vehiclesCount) }}</td>
            </tr>
        @endforeach
        @foreach ($quote->orderServices as $service)
            @if (!$service->in_output)
                @continue
            @endif

            <tr class="border-bottom">
                <td class="p-quarter width-80 font-9">{{ $service->name }} ({{ $vehiclesCount }}x)</td>
                <td class="p-quarter fw-bold no-wrap font-10">
                    € {{ \App\Support\CurrencyHelper::convertUnitsToCurrency(\App\Support\CurrencyHelper::convertCurrencyToUnits($service->sale_price) * $vehiclesCount) }}</td>
            </tr>
        @endforeach
        @if($quote->discount && $quote->discount_in_output)
            <tr class="border-bottom">
                <td class="p-quarter width-80 font-9">{{ __('Discount') }} ({{ $vehiclesCount }}x)</td>
                <td class="p-quarter fw-bold no-wrap font-10">
                    € {{ \App\Support\CurrencyHelper::convertUnitsToCurrency(\App\Support\CurrencyHelper::convertCurrencyToUnits($quote->discount) * $vehiclesCount) }}</td>
            </tr>
        @endif
        @if($quote->calculation_on_quote || !$quote->is_brutto)
            <tr class="border-bottom fw-bold">
                <td class="p-quarter width-80 font-9">{{ __('Total Quotation Price net') }}
                    , {{ $vehiclesCount }} {{ __('vehicles') }}</td>
                <td class="p-quarter no-wrap font-10">€ {{ $quote->total_quote_price_exclude_vat }}</td>
            </tr>
        @endif
        @if($quote->is_brutto)
            @if($quote->calculation_on_quote)
                <tr class="border-bottom">
                    <td class="p-quarter width-80 font-9">
                        {{ __('VAT') }} ({{ $quote->vat_percentage }}%)
                    </td>
                    <td class="p-quarter fw-bold no-wrap font-10">€ {{ $quote->total_vat }}</td>
                </tr>
                <tr class="border-bottom">
                    <td class="p-quarter width-80 font-9">{{ __('BPM') }}</td>
                    <td class="p-quarter fw-bold no-wrap font-10">€ {{ $quote->total_bpm }}</td>
                </tr>
                <tr class="border-bottom">
                    <td class="p-quarter width-80 font-9">{{ __('Registration costs (0%)') }}</td>
                    <td class="p-quarter fw-bold no-wrap font-10">€ {{ $quote->total_registration_fees }}</td>
                </tr>
            @endif
            <tr class="fw-bold">
                <td class="p-quarter width-80 font-9">{{ __('Total Quotation price in/in') }}</td>
                <td class="p-quarter no-wrap font-11 text-price">€ {{ $quote->total_quote_price }}</td>
            </tr>
        @endif
    </table>
</div>

<div class="container break-inside-avoid">
    <div class="fw-bold text-center text-s">
        {{ __('Vehicx BV | Provincialeweg 100a | 5334 JK Velddriel | +31 88 42 88 678 | info@vehicx.nl | www.vehicx.nl') }}
    </div>
    <div class="pt-1 font-6">
        {{ __('Price includes import on Dutch license plate and delivery ex Velddriel, unless otherwise indicated. Mileage indicative; normal wear and tear and paintwork repair possible. Indicative repair costs on delivery:
€300,- excl. VAT, unless mentioned otherwise. Specifications and versions may differ from Dutch versions. Sales price includes fees and, when stated, residual BPM, calculated based on stated CO2 emissions
and the current BPM table according to current legislation. Changes in legislation, CO2, BPM table or depreciation methods will be incorporated in the final price. Offer subject to availability and delivery by
supplier. Expected delivery time is indicative and may vary. Vehicx is not liable for supplier delays and assumes no liability for consequential damages or additional costs due to delayed delivery.
Payment for delivery, within 2 days of invoice; ownership remains with Vehicx until full payment. Sale under trading conditions, without warranty. Cancellation may result in 15% cancellation fee. Buyer is
required to insure vehicle from payment or delivery. See our privacy policy at www.vehicx.nl. All information subject to errors and changes.') }}
    </div>
</div>

@include('components.terms-conditions', [
    'pageBreak' => false
])
</body>
</html>

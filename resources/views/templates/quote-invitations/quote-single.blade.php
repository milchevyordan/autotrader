<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8"/>
    <title>{{ __('Quote') }} {{ $quote?->id }}</title>

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
    <table class="w-full pt-half break-inside-avoid">
        <tr>
            <td class="width-70">
                <div class="heading fw-bold p-quarter">
                    @include('components.quote-invitations.vehicle-title')
                </div>

                <table class="w-full py-half">
                    <tr>
                        <td class="width-half align-top font-7and44">
                            <table>
                                <tr class="border-bottom border-top">
                                    <td class="p-vehicle-information">{{ __('Vehicle nr') }}</td>
                                    <td class="p-vehicle-information fw-bold">{{ $vehicle->id }}</td>
                                </tr>

                                <tr class="border-bottom">
                                    <td class="p-vehicle-information">{{ __('Transmission') }}</td>
                                    <td class="p-vehicle-information fw-bold">
                                        {{ $vehicle->transmission_free_text ?? \App\Support\StringHelper::replaceUnderscores($vehicle->transmission?->name) }}
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

                                <tr class="border-bottom">
                                    <td class="p-vehicle-information">{{ __('Interior color') }}</td>
                                    <td class="p-vehicle-information fw-bold">{{ ucfirst($vehicle->specific_interior_color?->name) }}</td>
                                </tr>

                                <tr class="border-bottom">
                                    <td class="p-vehicle-information">{{ __('VIN nr') }}</td>
                                    <td class="p-vehicle-information fw-bold">{{ $vehicle->vin }}</td>
                                </tr>

                                <tr class="border-bottom">
                                    <td class="p-vehicle-information">{{ __('Registration nr') }}</td>
                                    <td class="p-vehicle-information fw-bold">{{ $vehicle->nl_registration_number }}</td>
                                </tr>
                            </table>
                        </td>
                        {{-- / First column --}}

                        {{--  Second column --}}
                        <td class="width-half align-top font-7and44">
                            <table>
                                <tr class="border-bottom border-top">
                                    <td class="p-vehicle-information"> {{ __('1st registration') }}</td>
                                    <td class="p-vehicle-information fw-bold">
                                        {{ $vehicle->first_registration_date ? \Carbon\Carbon::parse($vehicle->first_registration_date)->format('d-m-Y') : '' }}
                                    </td>
                                </tr>

                                <tr class="border-bottom">
                                    <td class="p-vehicle-information"> {{ __('Mileage') }}</td>
                                    <td class="p-vehicle-information fw-bold"> {{ $vehicle->kilometers ? $vehicle->kilometers . ' km' : '' }}</td>
                                </tr>

                                <tr class="border-bottom">
                                    <td class="p-vehicle-information"> {{ __('Power') }}</td>
                                    <td class="p-vehicle-information fw-bold">
                                        @if ($vehicle->kw)
                                            {{ $vehicle->kw }} kW
                                        @endif
                                        {{ $vehicle->kw && $vehicle->hp ? '/' : '' }}
                                        @if ($vehicle->hp)
                                            {{ $vehicle->hp }} HP
                                        @endif
                                    </td>
                                </tr>

                                <tr class="border-bottom">
                                    <td class="p-vehicle-information">{{ __('Fuel') }} {{ __('Type') }}</td>
                                    <td class="p-vehicle-information fw-bold">
                                        {{ \App\Support\StringHelper::replaceUnderscores($vehicle->fuel?->name) }}</td>
                                </tr>

                                <tr class="border-bottom">
                                    <td class="p-vehicle-information">{{ __('CO2-WLTP ') }}</td>
                                    <td class="p-vehicle-information fw-bold">
                                        {{ $vehicle->co2_wltp }}</td>
                                </tr>

                                <tr class="border-bottom">
                                    <td class="p-vehicle-information">{{ __('Listprice NL') }}</td>
                                    <td class="p-vehicle-information fw-bold">{{ \App\Support\CurrencyHelper::showMinusIfEmpty($vehicle->calculation->sales_price_total) }}</td>
                                </tr>

                                <tr class="border-bottom">
                                    <td class="p-vehicle-information">{{ __('Availability') }}</td>
                                    <td class="p-vehicle-information fw-bold">
                                        {{ \App\Services\WeekService::generateWeekInputString($vehicle->pivot->delivery_week ?? $vehicle->expected_date_of_availability_from_supplier) }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>

            {{-- Third column --}}
            <td class="width-30 align-top">
                <table class="vehicle-box_quote_images pl-2">
                    @if ($vehicle?->images->isEmpty())
                        <tr>
                            <td>
                                <img src="{{ public_path('/images/no-image.webp') }}" class="image mb-half" width="25px"
                                     alt="default image"/>
                            </td>
                        </tr>
                    @else
                        @foreach ($vehicle?->images->take(2) as $image)
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
            {{-- / Third column --}}
        </tr>
    </table>

    @isset($quote)
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

        <table class="w-full font-7and44 pt-2 break-inside-avoid">
            @if($quote->calculation_on_quote && $allItemsAndAdditionals)
                <tr class="border-bottom fw-bold">
                    <td class="width-75 p-quarter">{{ __('Sales price net') }}</td>
                    <td class="p-quarter no-wrap font-9">€ {{ $quote->total_sales_price_exclude_vat }}</td>
                </tr>
            @endif
            @foreach ($quote->orderItems as $item)
                @if (!$item->in_output)
                    @continue
                @endif

                <tr class="border-bottom">
                    <td class="width-75 p-quarter">{{ $item->item?->shortcode }}</td>
                    <td class="p-quarter fw-bold no-wrap">€ {{ $item->sale_price }}</td>
                </tr>
            @endforeach
            @foreach ($quote->orderServices as $service)
                @if (!$service->in_output)
                    @continue
                @endif

                <tr class="border-bottom">
                    <td class="width-75 p-quarter">{{ $service->name }}</td>
                    <td class="p-quarter fw-bold no-wrap">€ {{ $service->sale_price }}</td>
                </tr>
            @endforeach
            @if($quote->discount && $quote->discount_in_output)
                <tr class="border-bottom">
                    <td class="width-75 p-quarter">{{ __('Discount') }}</td>
                    <td class="p-quarter fw-bold no-wrap">€ {{ $quote->discount }}</td>
                </tr>
            @endif
            @if($quote->calculation_on_quote || !$quote->is_brutto)
                <tr class="border-bottom fw-bold">
                    <td class="width-75 p-quarter">{{ __('Total net') }}</td>
                    <td class="p-quarter text-price no-wrap font-9">€ {{ $quote->total_quote_price_exclude_vat }}</td>
                </tr>
            @endif
            @if($quote->is_brutto)
                @if($quote->calculation_on_quote)
                    <tr class="border-bottom">
                        <td class="width-75 p-quarter">VAT ({{ $quote->vat_percentage }}%)</td>
                        <td class="p-quarter fw-bold no-wrap">€ {{ $quote->total_vat }}</td>
                    </tr>
                    <tr class="border-bottom">
                        <td class="width-75 p-quarter">{{ __('BPM') }}</td>
                        <td class="p-quarter fw-bold no-wrap">€ {{ $quote->total_bpm }}</td>
                    </tr>
                    <tr class="border-bottom">
                        <td class="width-75 p-quarter">{{ __('Registration fees (0%)') }}</td>
                        <td class="p-quarter fw-bold no-wrap">€ {{ $quote->total_registration_fees }}</td>
                    </tr>
                @endif
                <tr class="fw-bold">
                    <td class="width-75 p-quarter fw-bold">{{ __('Total price brutto') }}</td>
                    <td class="width-75 p-quarter text-price no-wrap font-11">€ {{ $quote->total_quote_price }}</td>
                </tr>
            @endif
        </table>

        <div class="w-full pt-2 break-inside-avoid text-s">
            <div class="fw-bold py-1 font-10">{{ __('Conditions & Remarks') }}</div>
            <div class="py-quarter">{{ \App\Support\StringHelper::replaceUnderscores($quote->damage?->name) }}</div>
            <div class="py-quarter">{{ __('Transport') }} {{ $quote->transport_included ? __('Included') : __('Pick up by buyer') }}</div>
            <div class="py-quarter">{{ \App\Support\StringHelper::replaceUnderscores($quote->payment_condition?->name) }}</div>
            @if($quote->payment_condition?->value == \App\Enums\PaymentCondition::See_additional_information->value)
                <div class="py-quarter">{{ $quote->payment_condition_free_text }}</div>
            @endif
            <div class="py-quarter">{{$quote->additional_info_conditions}}</div>
        </div>
    @endisset

    <table class="w-full mt-3 py-half bg-quote-options rounded break-inside-avoid">
        <tr class="fw-bold font-10">
            <td class="px-half">{{ __('Options / Highlights') }}</td>
        </tr>
        <tr>
            <td class="p-1">
                <table class="font-7and44 fw-bold w-full" style="border-collapse: collapse">
                    @php
                        $visibleCount = 0;
                    @endphp
                    <tr>
                        @foreach ($attributes as $item)
                            @unless (
                                ($vehicle->{$item['attribute']} && $vehicle->{$item['attribute']}->name != 'No') ||
                                    (is_null($vehicle->{$item['attribute']}) && $vehicle->{$item['attribute_free_text']}))
                                @continue
                            @endunless

                            @if ($visibleCount > 0 && $visibleCount % 4 === 0)
                    </tr>
                    <tr>
                        @endif

                        @include('components.vehicles.highlight', [
                            'quoteSingleVehicle' => true,
                        ])

                        @php
                            $visibleCount++;
                        @endphp
                        @endforeach
                        @foreach (['highlight_1', 'highlight_2', 'highlight_3', 'highlight_4', 'highlight_5', 'highlight_6'] as $highlight)
                            @unless ($vehicle->$highlight)
                                @continue
                            @endunless

                            @if ($visibleCount > 0 && $visibleCount % 4 === 0)
                    </tr>
                    <tr>
                        @endif

                        <td class="width-quarter"><div class="border-bottom-white p-half">{{ $vehicle->$highlight }}</div></td>

                        @php
                            $visibleCount++;
                        @endphp
                        @endforeach
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div>
        @if ($vehicle->option)
            <div class="pt-2 text-s">
                <div class="pb-half">{{ __('Specifications') }}</div>

                @php
                    // Split the options string by newline characters
                    $options = explode("\n", $vehicle->option);
                @endphp

                @foreach ($options as $option)
                    <div>{{ trim($option) }}</div> <!-- Display each option on a new line -->
                @endforeach
            </div>
        @endif
    </div>

    <div class="images-short-conditions page-wrapper">
        @if($vehicle->images->where('section', 'externalImages')->toArray())
            <table class="w-full pt-3">
                @foreach (array_chunk($vehicle->images->where('section', 'externalImages')->toArray(), 3) as $chunk)
                    <tr>
                        @foreach ($chunk as $image)
                            <td class="width-third p-1">
                                <img class="image mb-half" src="data:image/png;base64, {{$image['base64'] }}" alt="vehicle image"/>
                            </td>
                        @endforeach
                        @for ($i = count($chunk); $i < 3; $i++)
                            <td class="width-third p-1"></td> <!-- Empty cells to maintain layout -->
                        @endfor
                    </tr>
                @endforeach
            </table>
        @endif


                <div class="bottom-text fw-bold text-center text-s">
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
    </div>
</div>



@include('components.terms-conditions',  [
    'pageBreak' => true
])
</body>
</html>

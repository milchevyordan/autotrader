<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <title>{{ __('Sales order') }} {{ $salesOrder->id }}</title>

    @include('styles.reset')
    @include('styles.pdf')
</head>

<body>

<div class="header-gray-background w-full">
    <div class="align-vertical-sales-order">
        <div class="heading font-28">{{ __('SALES CONTRACT') }}</div>
        <div class="text-s pt-5">{{ __('Date') }}: {{ \Carbon\Carbon::now()->format('d-m-Y') }}</div>
        <div class="text-s">{{ __('Sales Contact nr') }}: {{ $salesOrder->number }}</div>
    </div>
    <img src="{{ public_path($headerPrePurchaseSalesOrderImage) }}" alt="header pre purchase sales order image" class="overlay-image">
</div>

<div class="container text-s break-inside-avoid">
    <table class="w-full">
        <tr>
            {{-- First column --}}
            <td class="width-fifteen align-top px-half">
                @include('components.customer-company', [
                    'topText' => __('By signing this Sales Contract (Agreement) customer below (Buyer)'),
                    'company' => $salesOrder->customerCompany,
                    'bottomText' => __('the following vehicles and/or items')
                ])
            </td>

            {{-- Second column column --}}
            <td class="width-fifteen align-top px-half">
                @include('components.user-company', [
                    'topText' => __('is ordering from the company below (Seller)'),
                    'model' => $salesOrder,
                    'spacing' => false,
                ])
            </td>
        </tr>
    </table>
</div>

<div class="container">
    @foreach ($salesOrder->vehicles as $key => $vehicle)
        @php
            $priceNetUnits =
                \App\Support\CurrencyHelper::convertCurrencyToUnits(
                    $vehicle->calculation->sales_price_net,
                ) + \App\Support\CurrencyHelper::convertCurrencyToUnits($salesOrder->discount) + $salesPriceServiceItemsPerVehicleUnits;
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
                                        <tr class="border-bottom">
                                            <td class="p-vehicle-information">{{ __('Vehicle nr') }}</td>
                                            <td class="p-vehicle-information fw-bold">{{ $vehicle->id }}</td>
                                        </tr>

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
                                        @if($salesOrder->calculation_on_sales_order && $allItemsAndAdditionals)
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
                                        @if($salesOrder->discount && $salesOrder->discount_in_output)
                                            <tr class="border-bottom">
                                                <td class="p-vehicle-information">{{ __('Discount') }}</td>
                                                <td class="fw-bold no-wrap">€ {{ $salesOrder->discount }}</td>
                                            </tr>
                                        @endif
                                        @if($salesOrder->calculation_on_sales_order || !$salesOrder->is_brutto)
                                            <tr class="border-bottom">
                                                <td class="p-vehicle-information">{{ __('Price net') }}</td>
                                                <td class="text-price fw-bold no-wrap font-9">€ {{ $priceNet }}</td>
                                            </tr>
                                        @endif
                                        @if($salesOrder->is_brutto)
                                            @if($salesOrder->calculation_on_sales_order)
                                                <tr class="border-bottom">
                                                    <td class="p-vehicle-information">VAT ({{ $vehicle->calculation->vat_percentage }}
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
    @include('components.sales-orders.conditions-and-remarks', [
        'netto' => true
    ])
</div>

<div class="container page-break">
    @include('components.sales-orders.total_financial_information')

    @include('components.sales-orders.signatures', [
        'buyerName' => $salesOrder->customerCompany?->name,
    ])
</div>

<div class="break-inside-avoid page-break">
    <div class="header-gray-background w-full">
        <div class="align-vertical-declaration-receipt-of-goods">
            <div class="heading font-28">{{ __('DECLARATION') }}</div>
            <div class="heading font-28">{{ __('RECEIPT OF GOODS') }}</div>
            <div class="text-s pt-3">{{ __('Part of Sales Contact nr') }}: {{ $salesOrder->number }}</div>
        </div>
        <img src="{{ public_path($headerQuoteTransportAndDeclarationImage) }}" alt="header quote transport and declaration image" class="overlay-image">
    </div>

    <div class="container">
        <div class="pt-2 fw-bold text-s">
            {{ __('The undersigned') }},
        </div>

        <div class="pt-2 fw-bold text-s">
            @include('components.sales-orders.declaration-customer-company')
        </div>

        <div class="pt-2 fw-bold text-s">
            {{ __('hereby declares that on date of receipt') }}: ______________________________
        </div>

        <div class="pt-2 fw-bold text-s">
            {{ __('the following vehicle(s) was (were) received in good condition') }}:
        </div>

        @include('components.sales-orders.accepted-vehicles')

        <div class="pt-2 fw-bold text-s">
            {{ __('The above vehicle(s) was (were) delivered and received at the specified location on the stated date. This declaration is issued as proof of receipt for the intra-community supply exempt from VAT.') }}
            :
        </div>

        @include('components.sales-orders.signatures', [
            'buyerName' => $salesOrder->customerCompany?->name
        ])
    </div>

</div>

<div class="break-inside-avoid page-break">
    <div class="header-gray-background w-full">
        <div class="align-vertical-declaration-of-vat-payment">
            <div class="heading font-28">{{ __('DECLARATION') }}</div>
            <div class="heading font-28">{{ __('OF VAT PAYMENT') }}</div>
            <div class="heading font-18">{{ __('IN THE COUNTRY OF RECEIPT') }}</div>
            <div class="text-s pt-1">{{ __('Part of Sales Contact nr') }}: {{ $salesOrder->number }}</div>
        </div>
        <img src="{{ public_path($headerQuoteTransportAndDeclarationImage) }}" alt="header quote transport and declaration image" class="overlay-image">
    </div>

    <div class="container">
        <div class="pt-2 text-s fw-bold">
            {{ __('The undersigned') }},
        </div>

        <div class="pt-2 text-s fw-bold">
            @include('components.sales-orders.declaration-customer-company')
        </div>

        <div class="pt-2 text-s fw-bold">
            {{ __('hereby declares that the vehicle(s) with the following specification(s)') }}:
        </div>

        @include('components.sales-orders.accepted-vehicles')

        <div class="pt-2 text-s fw-bold">
            {{ __('will be registered in the aforementioned country of receipt. In accordance with applicable VAT regulations, VAT due on this transaction will be paid in the country of destination.') }}
        </div>

        <div class="pt-2 text-s fw-bold">
            {{ __('This declaration serves as confirmation of VAT obligations in the country of receipt and supports the intra-community supply exempt from VAT.') }}
        </div>

        @include('components.sales-orders.signatures', [
            'buyerName' => $salesOrder->customerCompany?->name
        ])
    </div>
</div>


<div class="break-inside-avoid">
    @include('components.sales-orders.payment-information')
</div>

@include('components.terms-conditions', [
    'pageBreak' => true
])
</body>
</html>

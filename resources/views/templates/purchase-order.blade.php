<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <title>{{ __('Purchase Order') }} {{ $purchaseOrder->id }}</title>

    @include('styles.reset')
    @include('styles.pdf')
</head>

<body>

<div class="header-gray-background w-full">
    <div class="align-vertical-purchase-order">
        <div class="heading font-28">{{ __('PURCHASE ORDER') }}</div>
        <div class="text-s pt-5">{{ __('Date') }}: {{ \Carbon\Carbon::now()->format('d-m-Y') }}</div>
        <div class="text-s">{{ __('Purchase Order nr') }}: PR{{ $purchaseOrder->id }}</div>
    </div>
    <img src="{{ public_path($headerPrePurchaseSalesOrderImage) }}" alt="Header Pre Purchase Sales Order Image" class="overlay-image">
</div>

<div class="container text-s break-inside-avoid">
    <table class="w-full">
        <tr>
            {{-- First column --}}
            <td class="width-fifteen align-top px-half">
                @include('components.customer-company', [
                    'topText' => __('By signing this Purchase Order (Agreement) the supplier below (Seller) commits to delivering'),
                    'company' => $purchaseOrder->supplierCompany,
                    'bottomText' => __('the following vehicles and/or items')
                ])
            </td>

            {{-- Second column column --}}
            <td class="width-fifteen align-top px-half">
                @include('components.user-company', [
                    'topText' => __('to the company below (Buyer)'),
                    'model' => $purchaseOrder,
                    'spacing' => false,
                ])
            </td>
        </tr>
    </table>
</div>

<div class="container text-s">
    @foreach($purchaseOrder->vehicles as $vehicle)
        <div class="break-inside-avoid py-2">
            <div class="heading fw-bold px-1">
                @include('components.vehicles.title')
            </div>

            <table class="w-full font-7 pt-half">
                <tr>
                    {{--First column--}}
                    <td class="width-quarter align-top px-half">
                        <table>
                            <tr>
                                <td class="px-half">{{ __('VIN') }}</td>
                                <td class="px-half fw-bold">{{ $vehicle->vin }}</td>
                            </tr>

                            <tr>
                                <td class="px-half">{{ __('Registration nr') }}</td>
                                <td class="px-half fw-bold">{{ $vehicle->nl_registration_number }}</td>
                            </tr>

                            <tr>
                                <td class="px-half">{{ __('Ref.') }} {{ $purchaseOrder->creator->company?->name }}</td>
                                <td class="px-half fw-bold">{{ $vehicle->id }}</td>
                            </tr>

                            <tr>
                                <td class="px-half">{{ __('Ref. Seller') }}</td>
                                <td class="px-half fw-bold">{{ $vehicle->vehicle_reference }}</td>
                            </tr>
                        </table>
                    </td>
                    {{--First column--}}

                    {{--Second column--}}
                    <td class="width-quarter align-top px-half">
                        <table>
                            <tr>
                                <td class="px-half"> {{ __('1st registration') }}</td>
                                <td class="px-half fw-bold">{{ $vehicle->first_registration_date ? \Carbon\Carbon::parse($vehicle->first_registration_date)->format('d-m-Y') : '' }}</td>
                            </tr>

                            <tr>
                                <td class="px-half"> {{ __('Mileage') }}</td>
                                <td class="px-half fw-bold">{{ $vehicle->kilometers ? $vehicle->kilometers . ' km' : '' }}</td>
                            </tr>

                            <tr>
                                <td class="px-half">{{ __('Color') }}</td>
                                <td class="px-half fw-bold">{{ $vehicle->specific_exterior_color?->name }}</td>
                            </tr>

                            <tr>
                                <td class="px-half">{{ __('Fuel type') }}</td>
                                <td class="px-half fw-bold">{{ \App\Support\StringHelper::replaceUnderscores($vehicle->fuel?->name) }}</td>
                            </tr>
                        </table>
                    </td>
                    {{--Second column--}}

                    {{--Third column--}}
                    <td class="width-quarter align-top px-half">
                        <table>
                            <tr>
                                <td class="px-half"> {{ __('Max. damages') }}</td>
                                <td class="px-half fw-bold">{{ $vehicle->supplier_given_damages }}</td>
                            </tr>
                            <tr>
                                <td class="px-half"> {{ __('Repaired damage') }}</td>
                                <td class="px-half fw-bold">{{ \App\Support\StringHelper::booleanRepresentation($vehicle->purchase_repaired_damage) }}</td>
                            </tr>
                            <tr>
                                <td class="px-half"> {{ __('Warranty') }}</td>
                                <td class="px-half fw-bold">
                                    @include('components.vehicles.highlight', [
                                        'quoteSingleVehicle' => false,
                                        'item' =>['attribute' => 'warranty', 'attribute_free_text' => 'warranty_free_text', 'label' => __('Warranty')],
                                    ])
                                </td>
                            </tr>
                            <tr>
                                <td class="px-half"> {{ __('2nd set wheels ') }}</td>
                                <td class="px-half fw-bold">
                                    @include('components.vehicles.highlight', [
                                        'quoteSingleVehicle' => false,
                                        'item' => ['attribute' => 'second_wheels', 'attribute_free_text' => 'second_wheels_free_text', 'label' => __('Second wheels')],
                                    ])
                                </td>
                            </tr>
                        </table>
                    </td>
                    {{--Third column--}}

                    {{--Fourth column--}}
                    <td class="width-quarter align-top">
                        <table>
                            <tr>
                                <td class="px-half">{{ __('Delivery') }}</td>
                                <td class="fw-bold no-wrap">{{ \App\Services\WeekService::generateWeekInputString($vehicle->expected_date_of_availability_from_supplier) }}</td>
                            </tr>
                            <tr>
                                <td class="px-half">{{ __('Sales price net') }}</td>
                                <td class="fw-bold no-wrap align-right">
                                    € {{ $vehicle->calculation->sales_price_net }} </td>
                            </tr>
                            <tr>
                                <td class="px-half">{{ __('BPM') }}</td>
                                <td class="fw-bold no-wrap align-right">{{ \App\Support\CurrencyHelper::showMinusIfEmpty($vehicle->calculation->rest_bpm_indication) }}</td>
                            </tr>
                            <tr>
                                <td class="px-half">{{ __('Transport') }}</td>
                                <td class="fw-bold no-wrap align-right">{{ \App\Support\CurrencyHelper::showMinusIfEmpty($vehicle->calculation->transport_inbound) }}</td>
                            </tr>
                        </table>
                    </td>
                    {{--/ Fourth column--}}
                </tr>
            </table>
        </div>

        @if(!$loop->last)
            <div class="border-bottom">

            </div>
        @endif
    @endforeach
</div>

<div class="container break-inside-avoid">
    <div class="w-full bg-gray rounded text-s py-1half">
        <div class="heading px-half">{{ __('Purchase Order details') }}</div>

        <table class="w-full mt-1 font-9">
            <tr>
                {{--First column--}}
                <td class="width-quarter align-top">
                    <table>
                        <tr>
                            <td class="px-half">{{ __('Transport') }}</td>
                            <td class="px-half fw-bold">{{ $purchaseOrder->transport_included ? __('By Buyer') : __('By Supplier') }}</td>
                        </tr>
                        <tr>
                            <td class="px-half">{{ __('Papers') }}</td>
                            <td class="px-half fw-bold">{{ \App\Support\StringHelper::replaceUnderscores($purchaseOrder->papers?->name) }}</td>
                        </tr>
                        <tr>
                            <td class="px-half">{{ __('COC') }}</td>
                            <td class="px-half fw-bold">{{ \App\Support\StringHelper::booleanRepresentation($coc) }}</td>
                        </tr>
                    </table>
                </td>
                {{--First column--}}

                {{--Second column--}}
                <td class="width-quarter align-top">
                    <table>
                        <tr>
                            <td class="px-half">{{ __('Down payment') }}</td>
                            <td class="px-half fw-bold">{{ \App\Support\StringHelper::booleanRepresentation($purchaseOrder->down_payment) }}</td>
                        </tr>
                        @if($purchaseOrder->down_payment)
                            <tr>
                                <td class="px-half">{{ __('Down payment amount') }}</td>
                                <td class="px-half fw-bold"> {{$purchaseOrder->down_payment_amount }}</td>
                            </tr>
                        @endif

                        <tr>
                            <td class="px-half">{{ __('Payment') }}</td>
                            <td class="px-half fw-bold">{{ \App\Support\StringHelper::replaceUnderscores($purchaseOrder->payment_condition?->name) }}</td>
                        </tr>
                        @if($purchaseOrder->payment_condition?->value == \App\Enums\PaymentCondition::See_additional_information->value)
                            <td class="px-half">{{ __('Additional information') }}</td>
                            <td class="px-half fw-bold">{{ \App\Support\StringHelper::replaceUnderscores($purchaseOrder->payment_condition_free_text) }}</td>
                        @endif
                        <tr>
                            <td class="px-half">{{ __('Sales restriction') }}</td>
                            <td class="px-half fw-bold">{{ $purchaseOrder->sales_restriction }}</td>
                        </tr>
                    </table>
                </td>
                {{--Second column--}}

                {{--Third column--}}
                <td class="width-quarter align-top">
                    <table>
                        <tr>
                            <td class="px-half"> {{ __('VAT Deposit') }}</td>
                            <td class="px-half fw-bold">{{ \App\Support\StringHelper::booleanRepresentation($purchaseOrder->vat_deposit) }}</td>
                        </tr>
                        <tr>
                            <td class="px-half"> {{ __('VAT percentage') }}</td>
                            <td class="px-half fw-bold">{{ $purchaseOrder->vat_percentage }} @if($purchaseOrder->vat_percentage)
                                    %
                                @endif</td>
                        </tr>
                    </table>
                </td>
                {{--Third column--}}

                {{--Fourth column--}}
                <td class="width-quarter align-top">
                    <table>
                        <tr>
                            <td class="px-half">{{ __('Payment currency') }}</td>
                            <td class="fw-bold no-wrap">{{ $purchaseOrder->currency_po?->translatedName() }}</td>
                        </tr>
                        <tr>
                            <td class="px-half">{{ __('Currency rate') }}</td>
                            <td class="fw-bold no-wrap">{{ $purchaseOrder->currency_rate ?? '-' }}</td>
                        </tr>
                    </table>
                </td>
                {{--/ Fourth column--}}
            </tr>
        </table>
    </div>
</div>


@include('components.contact-details', [
    'company' => $purchaseOrder->creator->company
])

@include('components.pre-purchase-orders.contact-people', [
    'company' => $purchaseOrder->creator->company
])

@include('components.pre-purchase-orders.contact-details-seller')

@include('components.pre-purchase-orders.delivery-terms-and-conditions')

<div class="container break-inside-avoid">
    <table class="w-full text-s py-3">
        <tr class="fw-bold">
            <td class="pt-half">{{ __('Total Vehicle price net') }}
                ({{ $purchaseOrder->vehicles->count() }} {{ $purchaseOrder->vehicles->count() > 1 ? __('vehicles') : __('vehicle') }})
            </td>
            <td class="pt-half no-wrap">{{ \App\Support\CurrencyHelper::showMinusIfEmpty($purchaseOrder->total_purchase_price_exclude_vat) }}</td>
        </tr>

        <tr class="fw-bold">
            <td class="pt-half">{{ __('Total Transport') }}?</td>
            <td class="pt-half no-wrap">{{ \App\Support\CurrencyHelper::showMinusIfEmpty($purchaseOrder->total_transport) }}</td>
        </tr>

        <tr>
            <td class="pt-half">{{ __('VAT') }} @if($purchaseOrder->vat_percentage)
                    {{ $purchaseOrder->vat_percentage }}%
                @endif</td>
            <td class="pt-half no-wrap">{{ \App\Support\CurrencyHelper::showMinusIfEmpty($purchaseOrder->total_vat) }}</td>
        </tr>

        <tr>
            <td class="pt-half">{{ __('BPM') }}?</td>
            <td class="pt-half no-wrap">{{ \App\Support\CurrencyHelper::showMinusIfEmpty($purchaseOrder->total_bpm) }}</td>
        </tr>

        <tr class="fw-bold">
            <td class="pt-half">{{ __('Total Purchase Order price') }}?</td>
            <td class="pt-half no-wrap text-price">{{ \App\Support\CurrencyHelper::showMinusIfEmpty($purchaseOrder->total_purchase_price_include_vat) }}</td>
        </tr>
    </table>

    <div class="border-bottom">

    </div>
</div>

<div class="container break-inside-avoid text-s">
    @include('components.pre-purchase-orders.signatures', [
        'buyerName' => $purchaseOrder->creator?->company->name,
        'sellerName' => $purchaseOrder->supplierCompany?->name,
    ])
</div>
</body>
</html>

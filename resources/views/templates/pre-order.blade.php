<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <title>{{ __('Pre Order') }} {{ $preOrder->id }}</title>
    @include('styles.reset')
    @include('styles.pdf')
</head>

<body>

<div class="header-gray-background w-full">
    <div class="align-vertical-pre-order">
        <div class="heading font-28">{{ __('PRE ORDER') }}</div>
        <div class="heading font-22">{{ __('ORDER TO BUILD') }}</div>
        <div class="text-s pt-2">{{ __('Date') }}: {{ \Carbon\Carbon::now()->format('d-m-Y') }}</div>
        <div class="text-s">{{ __('Pre Order nr') }}: PR{{ $preOrder->id }}</div>
    </div>
    <img src="{{ public_path($headerPrePurchaseSalesOrderImage) }}" alt="orange logo" class="overlay-image">
</div>

<div class="container text-s break-inside-avoid">
    <table class="w-full">
        <tr>
            {{-- First column --}}
            <td class="width-half align-top px-half">
                @include('components.customer-company', [
                    'topText' => __('By signing this Pre Order (Agreement) the supplier below (Seller) commits to delivering'),
                    'company' => $preOrder->supplierCompany,
                    'bottomText' => __('the following vehicles and/or items')
                ])
            </td>

            {{-- Second column column --}}
            <td class="width-half align-top px-half">
                @include('components.user-company', [
                    'topText' => __('to the company below (Buyer)'),
                    'model' => $preOrder,
                    'spacing' => false,
                ])
            </td>
        </tr>
    </table>
</div>

<div class="container pt-1 mb-1 break-inside-avoid text-s">
    <div class="heading fw-bold px-1">
        @include('components.vehicles.title', [
            'vehicle' => $preOrder->preOrderVehicle
        ])
    </div>

    <table class="pt-half w-full">
        <tr>
            {{--First column--}}
            <td class="width-quarter align-top px-half">
                <table>
                    <tr>
                        <td class="px-half">{{ __('Ref.') }} {{ $preOrder->creator->company?->name }}</td>
                        <td class="px-half fw-bold">{{ $preOrder->preOrderVehicle->id }}</td>
                    </tr>

                    <tr>
                        <td class="px-half"> {{ __('Configuration nr') }}</td>
                        <td class="px-half fw-bold">
                            {{ $preOrder->preOrderVehicle->configuration_number }}
                        </td>
                    </tr>

                    <tr>
                        <td class="px-half"> {{ __('1st registration') }}</td>
                        <td class="px-half fw-bold">{{ \App\Support\StringHelper::booleanRepresentation($preOrder->preOrderVehicle->current_registration) }}</td>
                    </tr>

                    <tr>
                        <td class="px-half"> {{ __('Mileage') }}</td>
                        <td class="px-half fw-bold">{{ $preOrder->preOrderVehicle->kilometers }}</td>
                    </tr>
                </table>
            </td>
            {{--First column--}}

            {{--Second column--}}
            <td class="width-quarter align-top px-half">
                <table>
                    <tr>
                        <td class="px-half">{{ __('Color') }}</td>
                        <td class="px-half fw-bold">{{ $preOrder->preOrderVehicle->specific_exterior_color?->name }} {{ $preOrder->preOrderVehicle->color_type?->name }} {{ $preOrder->preOrderVehicle->factory_name_color }}</td>
                    </tr>

                    <tr>
                        <td class="px-half">{{ __('Interior') }}</td>
                        <td class="px-half fw-bold">
                            {{ $preOrder->preOrderVehicle->specific_interior_color?->name }}
                            {{ $preOrder->preOrderVehicle->factory_name_interior }}
                            {{ $preOrder->preOrderVehicle->interior_material?->translatedName() }}</td>
                    </tr>

                    <tr>
                        <td class="px-half">{{ __('Fuel type') }}</td>
                        <td class="px-half fw-bold">{{ \App\Support\StringHelper::replaceUnderscores($preOrder->preOrderVehicle->fuel?->name) }}</td>
                    </tr>

                    <tr>
                        <td class="px-half">{{ __('Body type') }}</td>
                        <td class="px-half fw-bold">{{ $preOrder->preOrderVehicle->body?->name }}</td>
                    </tr>
                </table>
            </td>
            {{--Second column--}}

            {{--Third column--}}
            <td class="width-quarter align-top px-half">
                <table>
                    <tr>
                        <td class="px-half"> {{ __('Production week') }}</td>
                        <td class="px-half fw-bold">{{ \App\Services\WeekService::generateWeekInputString($preOrder->preOrderVehicle->production_weeks) }}</td>
                    </tr>
                    <tr>
                        <td class="px-half"> {{ __('Lead time weeks') }}</td>
                        <td class="px-half fw-bold">{{ \App\Services\WeekService::generateLeadtimeInputString($preOrder->preOrderVehicle->expected_leadtime_for_delivery_from, $preOrder->preOrderVehicle->expected_leadtime_for_delivery_to) }}</td>
                    </tr>
                    <tr>
                        <td class="px-half"> {{ __('Registr. weeks') }}</td>
                        <td class="px-half fw-bold">{{ \App\Services\WeekService::generateLeadtimeInputString($preOrder->preOrderVehicle->registration_weeks_from, $preOrder->preOrderVehicle->registration_weeks_to) }}</td>
                    </tr>
                    <tr>
                        <td class="px-half"> {{ __('Exp. delivery week') }}</td>
                        <td class="px-half fw-bold">{{ \App\Services\WeekService::generateWeekInputString($preOrder->preOrderVehicle->expected_delivery_weeks) }}</td>
                    </tr>
                </table>
            </td>
            {{--Third column--}}

            {{--Fourth column--}}
            <td class="width-quarter align-top">
                <table>
                    <tr>
                        <td class="px-half">{{ __('Amount of vehicle') }}</td>
                        <td class="fw-bold no-wrap align-right">{{ $preOrder->amount_of_vehicles }}</td>
                    </tr>
                    <tr>
                        <td class="px-half">{{ __('Sales price net') }}</td>
                        <td class="fw-bold no-wrap align-right">€ {{ $preOrder->preOrderVehicle->calculation->sales_price_net }}</td>
                    </tr>
                    <tr>
                        <td class="px-half">{{ __('BPM') }}</td>
                        <td class="fw-bold no-wrap align-right">{{ \App\Support\CurrencyHelper::showMinusIfEmpty($preOrder->preOrderVehicle->calculation->rest_bpm_indication) }}</td>
                    </tr>
                    <tr>
                        <td class="px-half">{{ __('Transport') }}</td>
                        <td class="fw-bold no-wrap align-right">{{ \App\Support\CurrencyHelper::showMinusIfEmpty($preOrder->preOrderVehicle->calculation->transport_inbound) }}</td>
                    </tr>
                </table>
            </td>
            {{--/ Fourth column--}}
        </tr>
    </table>

    <table class="mt-half w-full">
        <tr>
            <td class="width-ten align-top px-1">{{ __('Options') }}</td>
            <td class="align-top px-1 fw-bold">
                @foreach ($attributes as $item)
                    @include('components.vehicles.highlight', [
                        'quoteSingleVehicle' => false,
                        'vehicle' => $preOrder->preOrderVehicle
                    ])
                @endforeach
                @foreach (['highlight_1', 'highlight_2', 'highlight_3', 'highlight_4', 'highlight_5', 'highlight_6'] as $highlight)
                    @unless ($preOrder->preOrderVehicle->$highlight)
                        @continue
                    @endunless

                    <span>{{ $preOrder->preOrderVehicle->$highlight }}</span>
                @endforeach
            </td>
        </tr>
    </table>
</div>

<div class="container break-inside-avoid">
    <div class="w-full mt-3 bg-gray rounded text-s py-1">
        <div class="heading fw-bold px-half">{{ __('Pre Order details') }}</div>

        <table class="w-full mt-1">
            <tr>
                {{--First column--}}
                <td class="width-quarter align-top">
                    <table>
                        <tr>
                            <td class="px-half">{{ __('Transport') }}</td>
                            <td class="px-half fw-bold">{{ $preOrder->transport_included ? __('Included') : __('By Supplier') }}</td>
                        </tr>
                        <tr>
                            <td class="px-half">{{ __('Papers') }}</td>
                            <td class="px-half fw-bold"> -</td>
                        </tr>
                        <tr>
                            <td class="px-half">{{ __('COC') }}</td>
                            <td class="px-half fw-bold"> -</td>
                        </tr>
                    </table>
                </td>
                {{--First column--}}

                {{--Second column--}}
                <td class="width-quarter align-top">
                    <table>
                        <tr>
                            <td class="px-half">{{ __('Down payment') }}</td>
                            <td class="px-half fw-bold">{{ \App\Support\StringHelper::booleanRepresentation($preOrder->down_payment) }}</td>
                        </tr>
                        <tr>
                            <td class="px-half">{{ __('Payment') }}</td>
                            <td class="px-half fw-bold"> -</td>
                        </tr>
                        <tr>
                            <td class="px-half">{{ __('Sales restriction') }}</td>
                            <td class="px-half fw-bold"> -</td>
                        </tr>
                    </table>
                </td>
                {{--Second column--}}

                {{--Third column--}}
                <td class="width-quarter align-top">
                    <table>
                        <tr>
                            <td class="px-half"> {{ __('VAT Deposit') }}</td>
                            <td class="px-half fw-bold">{{ \App\Support\StringHelper::booleanRepresentation($preOrder->vat_deposit) }}</td>
                        </tr>
                        <tr>
                            <td class="px-half"> {{ __('VAT percentage') }}</td>
                            <td class="px-half fw-bold">{{ $preOrder->vat_percentage }} @if($preOrder->vat_percentage)
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
                            <td class="fw-bold no-wrap">{{ $preOrder->currency_po?->translatedName() }}</td>
                        </tr>
                        <tr>
                            <td class="px-half">{{ __('Currency rate') }}</td>
                            <td class="fw-bold no-wrap">{{ $preOrder->currency_rate }}</td>
                        </tr>
                    </table>
                </td>
                {{--/ Fourth column--}}
            </tr>
        </table>
    </div>
</div>

@include('components.contact-details', [
    'company' => $preOrder->creator->company
])

@include('components.pre-purchase-orders.contact-people', [
    'company' => $preOrder->creator->company
])

@include('components.pre-purchase-orders.contact-details-seller')

@include('components.pre-purchase-orders.delivery-terms-and-conditions')

<div class="container break-inside-avoid">
    <table class="w-full text-s py-3">
        <tr class="fw-bold">
            <td class="pt-half">{{ __('Total Vehicle price net (1 vehicle)') }}</td>
            <td class="pt-half no-wrap">{{ \App\Support\CurrencyHelper::showMinusIfEmpty($preOrder->total_purchase_price_exclude_vat) }}</td>
        </tr>

        <tr class="fw-bold">
            <td class="pt-half">{{ __('Total Transport') }}?</td>
            <td class="pt-half no-wrap">{{ \App\Support\CurrencyHelper::showMinusIfEmpty($preOrder->preOrderVehicle->calculation->transport_inbound) }}</td>
        </tr>

        <tr>
            <td class="pt-half">{{ __('VAT') }} @if($preOrder->vat_percentage)
                    {{ $preOrder->vat_percentage }}%
                @endif</td>
            <td class="pt-half no-wrap">{{ \App\Support\CurrencyHelper::showMinusIfEmpty($preOrder->total_vat) }}</td>
        </tr>

        <tr>
            <td class="pt-half">{{ __('BPM') }}?</td>
            <td class="pt-half no-wrap">{{ \App\Support\CurrencyHelper::showMinusIfEmpty($preOrder->total_bpm) }}</td>
        </tr>

        <tr class="fw-bold">
            <td class="pt-half">{{ __('Total Pre Order price') }}?</td>
            <td class="pt-half no-wrap text-price">{{ \App\Support\CurrencyHelper::showMinusIfEmpty($preOrder->total_purchase_price_include_vat) }}</td>
        </tr>
    </table>

    <div class="border-bottom">

    </div>
</div>

<div class="container break-inside-avoid text-s border-top">
    @include('components.pre-purchase-orders.signatures', [
        'buyerName' => $preOrder->creator?->company->name,
        'sellerName' => $preOrder->supplierCompany?->name,
    ])
</div>
</body>
</html>

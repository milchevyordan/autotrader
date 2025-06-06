<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8"/>
    <title>{{ __('Document') }} {{ $document->id }}</title>

    @include('styles.reset')
    @include('styles.pdf')
</head>

<body>

<div class="header-white w-full">
    <table class="w-full over-overlay-image">
        <tr>
            {{-- First column --}}
            <td class="width-70 align-top">
                <div class="align-vertical">
                    <div class="heading font-28">{{ __('PROFORMA') }}</div>
                    <div class="heading font-18">{{ __('INVOICE') }}</div>
                    <div class="text-s pt-half">{{ __('Debtor number') }}
                        : {{ $document->customerCompany?->debtor_number_accounting_system }}</div>
                    <div class="text-s">{{ __('Invoice Date') }}: {{ \Carbon\Carbon::now()->format('d-m-Y') }}</div>
                    <div class="text-s">
                        {{ __('Due date') }}:
                        @switch($document->payment_condition->value)
                            @case(App\Enums\PaymentCondition::Payment_two_days_after_invoice->value)
                                {{ \Carbon\Carbon::now()->addDays(2)->format('d-m-Y') }}
                                @break

                            @case(App\Enums\PaymentCondition::Payment_one_week_after_invoice->value)
                                {{ \Carbon\Carbon::now()->addWeek()->format('d-m-Y') }}
                                @break

                            @case(App\Enums\PaymentCondition::Payment_two_weeks_after_invoice->value)
                                {{ \Carbon\Carbon::now()->addWeeks(2)->format('d-m-Y') }}
                                @break

                            @default
                                {{ \Carbon\Carbon::now()->format('d-m-Y') }}
                                @break
                        @endswitch
                    </div>
                </div>
            </td>

            {{-- Second column column --}}
            <td class="align-top pt-4 text-s">
                @include('components.user-company', [
                    'topText' => '',
                    'model' => $document,
                    'spacing' => true
                ])
            </td>
        </tr>
    </table>
    <img src="{{ public_path($headerDocumentsImage) }}" alt="header documents image" class="overlay-image">
</div>

<div class="container text-s break-inside-avoid">
    @include('components.customer-company', [
        'topText' => __('Proforma invoice issued to'),
        'company' => $document->customerCompany,
        'bottomText' => __('for the following vehicles and/or items'),
    ])
</div>

<div class="container text-s">
    <div class="pb-2 border-bottom font-6">
        <table class="w-full">
            <tr class="align-top">
                <td class="width-75 text-left p-quarter fw-bold">{{ __('Description') }}</td>
                <td class="width-12and5 text-left p-quarter fw-bold">{{ __('VAT') }}</td>
                <td class="width-12and5 text-left p-quarter fw-bold">{{ __('Price net') }}</td>
            </tr>
        </table>
    </div>

    @switch($document->documentable_type->value)
        @case(\App\Enums\DocumentableType::Pre_order_vehicle->value)
        @case(\App\Enums\DocumentableType::Vehicle->value)
        @case(\App\Enums\DocumentableType::Service_vehicle->value)
            @foreach ($document->lines as $line)
                @php
                    $notMain = $line->type->value != App\Enums\DocumentLineType::Main->value;
                    $vehicle = $selectedDocumentables->where('id', $line->documentable_id)->first();
                @endphp
                <div class="py-1 border-bottom align-top break-inside-avoid">
                    <div class="p-quarter font-">
                        @if(!$notMain && $vehicle)<span class="fw-bold">{{ $line->name }}</span>
                            / @include('components.vehicles.title', ['vehicle' => $vehicle, 'boldInitialText' => true])
                        @endif
                    </div>
                    <table class="w-full">
                        <tr>
                            <td class="width-37and5">
                                <table>
                                    @if(!$notMain && $vehicle)
                                        @if($document->documentable_type->value == \App\Enums\DocumentableType::Vehicle->value)
                                            <tr>
                                                <td class="p-quarter">{{ __('Vehicle nr') }}</td>
                                                <td class="p-quarter fw-bold">{{ $vehicle->id }}</td>
                                            </tr>

                                            @if($vehicle->first_registration_date)
                                                <tr>
                                                    <td class="p-quarter">{{ __('1st registration') }}</td>
                                                    <td class="p-quarter fw-bold">{{ \Carbon\Carbon::parse($vehicle->first_registration_date)->format('d.m.Y') }}</td>
                                                </tr>
                                            @endif

                                            @if($vehicle->nl_registration_number)
                                                <tr>
                                                    <td class="p-quarter">{{ __('Registration nr') }}</td>
                                                    <td class="p-quarter fw-bold">{{ $vehicle->nl_registration_number }}</td>
                                                </tr>
                                            @endif
                                        @elseif($document->documentable_type->value == \App\Enums\DocumentableType::Pre_order_vehicle->value)
                                            <tr>
                                                <td class="p-quarter">{{ __('Vehicle nr') }}</td>
                                                <td class="p-quarter fw-bold">{{ $vehicle->id }}</td>
                                            </tr>

                                            @if($vehicle->vehicle_status)
                                                <tr>
                                                    <td class="p-quarter">{{ __('Vehicle status at purchase') }}</td>
                                                    <td class="p-quarter fw-bold">{{ \App\Support\StringHelper::replaceUnderscores($vehicle->vehicle_status->name) }}</td>
                                                </tr>
                                            @endif

                                            @if($vehicle->vehicle_reference)
                                                <tr>
                                                    <td class="p-quarter">{{ __('Vehicle reference (custom)') }}</td>
                                                    <td class="p-quarter fw-bold">{{ $vehicle->vehicle_reference }}</td>
                                                </tr>
                                            @endif
                                        @else
                                            <tr>
                                                <td class="p-quarter">{{ __('Vehicle nr') }}</td>
                                                <td class="p-quarter fw-bold">{{ $vehicle->id }}</td>
                                            </tr>

                                            @if($vehicle->first_registration_date)
                                                <tr>
                                                    <td class="p-quarter">{{ __('1st registration') }}</td>
                                                    <td class="p-quarter fw-bold">{{ \Carbon\Carbon::parse($vehicle->first_registration_date)->format('d.m.Y') }}</td>
                                                </tr>
                                            @endif

                                            @if($vehicle->current_registration)
                                                <tr>
                                                    <td class="p-quarter">{{ __('Current registration') }}</td>
                                                    <td class="p-quarter fw-bold">{{ $vehicle->current_registration->name }}</td>
                                                </tr>
                                            @endif
                                        @endif
                                    @else
                                        <span class="fw-bold">{{ $line->name }}</span>
                                    @endif
                                </table>
                            </td>

                            <td class="width-37and5 align-top">
                                @if(!$notMain && $vehicle)
                                    @if($document->documentable_type->value == \App\Enums\DocumentableType::Vehicle->value)
                                        <table>
                                            @if($vehicle->vin)
                                                <tr>
                                                    <td class="p-half">{{ __('VIN nr') }}</td>
                                                    <td class="p-half fw-bold">{{ $vehicle->vin }}</td>
                                                </tr>
                                            @endif

                                            @if($vehicle->kilometers)
                                                <tr>
                                                    <td class="p-half">{{ __('Mileage') }}</td>
                                                    <td class="p-half fw-bold">{{ $vehicle->kilometers }}</td>
                                                </tr>
                                            @endif

                                            @if($vehicle->specific_exterior_color)
                                                <tr>
                                                    <td class="p-half">{{ __('Color') }}</td>
                                                    <td class="p-half fw-bold">{{ \App\Support\StringHelper::replaceUnderscores($vehicle->specific_exterior_color->name) }}</td>
                                                </tr>
                                            @endif
                                        </table>
                                    @elseif($document->documentable_type->value == \App\Enums\DocumentableType::Pre_order_vehicle->value)
                                        <table>
                                            @if($vehicle->configuration_number)
                                                <tr>
                                                    <td class="p-half">{{ __('Configuration Number') }}</td>
                                                    <td class="p-half fw-bold">{{ $vehicle->configuration_number }}</td>
                                                </tr>
                                            @endif

                                            @if($vehicle->komm_number)
                                                <tr>
                                                    <td class="p-half">{{ __('Komm Number') }}</td>
                                                    <td class="p-half fw-bold">{{ $vehicle->komm_number }}</td>
                                                </tr>
                                            @endif

                                            @if($vehicle->specific_exterior_color)
                                                <tr>
                                                    <td class="p-half">{{ __('Color') }}</td>
                                                    <td class="p-half fw-bold">{{ \App\Support\StringHelper::replaceUnderscores($vehicle->specific_exterior_color->name) }}</td>
                                                </tr>
                                            @endif
                                        </table>
                                    @else
                                        <table>
                                            @if($vehicle->vin)
                                                <tr>
                                                    <td class="p-half">{{ __('VIN nr') }}</td>
                                                    <td class="p-half fw-bold">{{ $vehicle->vin }}</td>
                                                </tr>
                                            @endif

                                            @if($vehicle->kilometers)
                                                <tr>
                                                    <td class="p-half">{{ __('Mileage') }}</td>
                                                    <td class="p-half fw-bold">{{ $vehicle->kilometers }}</td>
                                                </tr>
                                            @endif
                                        </table>
                                    @endif
                                @else
                                    @if($document->documentable_type->value == \App\Enums\DocumentableType::Pre_order_vehicle->value)
                                        <span class="fw-bold">{{ $vehicle?->configuration_number }}</span>
                                    @else
                                        <span class="fw-bold">{{ $vehicle?->vin }}</span>
                                    @endif
                                @endif
                            </td>
                            @include('components.documents.vat-percentage-and-price')
                        </tr>
                    </table>
                </div>
            @endforeach
            @break

        @case(\App\Enums\DocumentableType::Sales_order_down_payment->value)
        @case(\App\Enums\DocumentableType::Sales_order->value)
            @foreach ($document->lines as $line)
                @php
                    $notMain = $line->type->value != App\Enums\DocumentLineType::Main->value;
                    $salesOrder = $selectedDocumentables->where('id', $line->documentable_id)->first();
                @endphp
                <div class="py-1 border-bottom align-top break-inside-avoid">
                    <table class="w-full">
                        <tr>
                            <td class="width-37and5">
                                @if(!$notMain && $salesOrder)
                                    <table>
                                        @if($salesOrder->number)
                                            <tr>
                                                <td class="p-quarter">{{ __('Sales order number') }}</td>
                                                <td class="p-quarter fw-bold">{{ $salesOrder->number }}</td>
                                            </tr>
                                        @endif

                                        @if($salesOrder->delivery_week)
                                            <tr>
                                                <td class="p-quarter">{{ __('Delivery indication week') }}</td>
                                                <td class="p-quarter fw-bold">{{ \App\Services\WeekService::generateWeekInputString($salesOrder->delivery_week) }}</td>
                                            </tr>
                                        @endif

                                        <tr>
                                            <td class="p-quarter">{{ __('Transport') }}</td>
                                            <td class="p-quarter fw-bold">{{ $salesOrder->transport_included ? __('Included') : __('Pick up by buyer') }}</td>
                                        </tr>
                                    </table>
                                @else
                                    <span class="fw-bold">{{ $line->name }}</span>
                                @endif
                            </td>

                            <td class="width-37and5 align-top">
                                @if(!$notMain && $salesOrder)
                                    <table>
                                        @if($salesOrder->damage)
                                            <tr>
                                                <td class="p-quarter">{{ __('Damage') }}</td>
                                                <td class="p-quarter fw-bold">{{ \App\Support\StringHelper::replaceUnderscores($salesOrder->damage->name) }}</td>
                                            </tr>
                                        @endif

                                        @if($salesOrder->payment_condition)
                                            <tr>
                                                <td class="p-quarter">{{ __('Payment condition') }}</td>
                                                <td class="p-quarter fw-bold">{{ \App\Support\StringHelper::replaceUnderscores($salesOrder->payment_condition->name) }}</td>
                                            </tr>
                                        @endif

                                        @if($salesOrder->serviceLevel)
                                            <tr>
                                                <td class="p-quarter">{{ __('Service Level') }}</td>
                                                <td class="p-quarter fw-bold">{{ $salesOrder->serviceLevel->name }}</td>
                                            </tr>
                                        @endif
                                    </table>
                                @else
                                    <span class="fw-bold">{{ $salesOrder?->number }}</span>
                                @endif
                            </td>
                            @include('components.documents.vat-percentage-and-price')
                        </tr>
                    </table>
                </div>
            @endforeach
            @break

        @case(\App\Enums\DocumentableType::Service_order->value)
        @case(\App\Enums\DocumentableType::Work_order->value)
            @foreach ($document->lines as $line)
                <table class="w-full py-1 border-bottom align-top break-inside-avoid">
                    <tr>
                        <td class="align-top width-75 py-1 p-quarter fw-bold" colspan="2">
                            {{ $line->name }}
                        </td>

                        @include('components.documents.vat-percentage-and-price')
                    </tr>
                </table>
            @endforeach
            @break
    @endswitch

    <table class="w-full fw-bold font-9 pt-1 break-inside-avoid">
        <tr>
            <td class="width-75 py-half"></td>
            <td class="width-12and5 align-top py-half"><span class="p-quarter">{{ __('Total net') }}</span></td>
            <td class="width-12and5 no-wrap align-top py-half"><span class="p-quarter">{{ \App\Support\CurrencyHelper::showMinusIfEmpty($document->total_price_exclude_vat) }}</span></td>
        </tr>
        <tr>
            <td class="width-75 py-half"></td>
            <td class="width-12and5 align-top py-half"><span class="p-quarter">{{ __('Rest BPM') }}</span></td>
            <td class="width-12and5 no-wrap align-top py-half"><span class="p-quarter">{{ \App\Support\CurrencyHelper::showMinusIfEmpty($totalBpmAndLeges['totalBpm']) }}</span></td>
        </tr>
        <tr>
            <td class="width-75 py-half"></td>
            <td class="width-12and5 align-top py-half"><span class="p-quarter">{{ __('Leges (VAT)') }}</span></td>
            <td class="width-12and5 no-wrap align-top py-half"><span class="p-quarter">{{ \App\Support\CurrencyHelper::showMinusIfEmpty($totalBpmAndLeges['totalLeges']) }}</span></td>
        </tr>
        <tr>
            <td class="width-75 py-half"></td>
            <td class="width-12and5 align-top py-half"><span class="p-quarter">{{ __('VAT') }}</span></td>
            <td class="width-12and5 no-wrap align-top py-half"><span class="p-quarter">{{ \App\Support\CurrencyHelper::showMinusIfEmpty($document->total_vat) }}</span></td>
        </tr>
        <tr>
            <td class="width-75 py-half"></td>
            <td class="width-12and5 align-top py-half"><span class="p-quarter">{{ __('Total payable') }}</span></td>
            <td class="width-12and5 no-wrap align-top py-half"><span class="p-quarter">{{ \App\Support\CurrencyHelper::showMinusIfEmpty($document->total_price_include_vat) }}</span></td>
        </tr>
    </table>
    <div class="font-9">
        <div>
            {{ $document->notes }}
        </div>
        <div>
            {{ \App\Support\StringHelper::replaceUnderscores($document->payment_condition?->name) }}
        </div>
        @if($document->payment_condition?->value == \App\Enums\PaymentCondition::See_additional_information->value)
            <div>
                {{ $document->payment_condition_free_text }}
            </div>
        @endif
    </div>
</div>

<div class="container font-9">
    {{ __('Thank you in advance for paying within the due date, with reference to the invoice number and VIN.') }}
</div>

</body>
</html>

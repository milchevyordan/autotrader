<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8"/>
    <title>{{ __('Transport Request') }} {{ $transportOrder->id }}</title>

    @include('styles.reset')
    @include('styles.pdf')
</head>

<body>

<div class="header-gray-background w-full text-s">
    <table class="w-full over-overlay-image">
        <tr>
            {{-- First column --}}
            <td class="width-70 align-top">
                <div class="align-vertical-transport-order">
                    <div class="heading font-28">{{ __('TRANSPORT') }}</div>
                    <div class="heading font-28">{{ __('REQUEST') }}</div>
                    <div class="text-s pt-1and5">{{ __('Date') }}: {{ \Carbon\Carbon::now()->format('d-m-Y') }}</div>
                    <div class="text-s">{{ __('Transport Request nr') }}: TO{{ $transportOrder->id }}</div>
                </div>
            </td>

            {{-- Second column column --}}
            <td class="align-top pt-2 text-s">
                @include('components.user-company', [
                    'topText' => '',
                    'model' => $transportOrder,
                    'spacing' => true,
                ])
            </td>
        </tr>
    </table>
    <img src="{{ public_path($headerQuoteTransportAndDeclarationImage) }}" alt="header quote transport and declaration image" class="overlay-image">
</div>

<div class="container text-s">
    <div class="mt-2 text-s">
        {{ __('Please provide us with your offer, including leadtimes and conditions for the following transport:') }}
    </div>
</div>

<div class="container break-inside-avoid">
    <div class="w-full bg-gray rounded p-half">
        <table class="w-full text-s">
            <tr>
                <th class="text-left" colspan="2">{{ __('Summary') }}</th>
                <th class="text-left">
                    @if($addressesArray['pickUp']['addressesCount'])
                        {{ $addressesArray['pickUp']['addressesCount'] }} {{ __('Pickup') }} {{ $addressesArray['pickUp']['addressesCount'] > 1 ?  __('addresses') : __('address') }}
                        ,
                    @endif
                    @if($addressesArray['delivery']['addressesCount'])
                        {{ $addressesArray['delivery']['addressesCount'] }} {{ __('Delivery') }} {{ $addressesArray['delivery']['addressesCount'] > 1 ?  __('addresses') : __('address') }}
                    @endif
                </th>
                <td class="text-left">{{ __('Vehicles') }}</td>
                <td class="text-left">{{ __('Price') }}</td>
                <td class="text-left">{{ __('Per vehicle') }}</td>
            </tr>

            @foreach($addressesArray['addresses'] as $address)
                <tr>
                    <td class="text-left fw-bold">{{ $loop->iteration }}</td>
                    <td class="text-left">{{ __('Pickup') }}</td>
                    <td class="text-left fw-bold">{{ $address['pickUpAddress'] }}</td>
                    <td class="text-left fw-bold">{{ $address['vehiclesCount'] }}</td>
                    <td class="text-left fw-bold" colspan="2"></td>
                </tr>
                <tr>
                    <td class="py-half"></td>
                    <td class="text-left py-half">{{ __('Delivery') }}</td>
                    <td class="text-left py-half">{{ $address['deliveryAddress'] }}</td>
                    <td class="py-half" colspan="3"></td>
                </tr>
            @endforeach
            <tr class="border-top font-9and80 fw-bold">
                <td class="pt-half" colspan="2">{{ __('Total') }}</td>
                <td class="pt-half"></td>
                <td class="text-left pt-half">{{ $selectedTransportablesCount }}</td>
                <td class="text-left pt-half" colspan="2"></td>
            </tr>
        </table>
    </div>
</div>

<div class="container">
    @foreach($addressesArray['addresses'] as $address)
        <div class="border-bottom p-half break-inside-avoid mt-1">
            <table class="w-full">
                <tr>
                    {{-- First column --}}
                    <td class="width-half align-top">
                        <table>
                            <tr>
                                <td class="width-1 fw-bold font-9and80">{{ $loop->iteration }}</td>
                                <td class="px-half font-7and44">{{ __('Pickup address') }}:</td>
                                <td class="px-half font-6and86">{{ $address['pickUpAddress'] }}</td>
                            </tr>
                            <tr>
                                <td class="px-half" colspan="2"></td>
                                <td class="px-half font-6and86">{{ $transportOrder->pick_up_location_free_text }}</td>
                            </tr>
                            <tr>
                                <td class="px-half"></td>
                                <td class="px-half font-6and86">{{ $transportOrder->pick_up_notes }}</td>
                            </tr>
                            <tr>
                                <td class="px-half font-7and44">{{ __('Earliest Pickup') }}</td>
                                <td class="px-half font-6and86 text-price">{{ $transportOrder->pick_up_after_date?->format('d-m-Y, H:i') }}</td>
                            </tr>
                        </table>
                    </td>

                    {{-- Second column column --}}
                    <td class="width-half align-top">
                        <table>
                            <tr>
                                <td class="px-half font-7and44">{{ __('Delivery address') }}:</td>
                                <td class="px-half font-6and86">{{ $address['deliveryAddress'] }}</td>
                            </tr>
                            <tr>
                                <td class="px-half"></td>
                                <td class="px-half font-6and86">{{ $transportOrder->delivery_location_free_text }}</td>
                            </tr>
                            <tr>
                                <td class="px-half"></td>
                                <td class="px-half font-6and86">{{ $transportOrder->delivery_notes }}</td>
                            </tr>
                            <tr>
                                <td class="px-half font-7and44">{{ __('Delivery deadline') }}</td>
                                <td class="px-half font-6and86 text-price">{{ $transportOrder->deliver_before_date?->format('d-m-Y, H:i') }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <table class="w-full pt-1">
                <tr class="font-6and86">
                    <td class="text-left">{{ __('Nr.') }}</td>
                    <td class="text-left">{{ __('Ref. Vehicx') }}</td>
                    <td class="text-left">{{ __('Make/ Model') }}</td>
                    @if($transportOrder->vehicle_type->value != \App\Enums\TransportableType::Pre_order_vehicles->value)
                        <td class="text-left">{{ __('VIN') }}</td>
                        <td class="text-left">{{ __('Registration nr') }}</td>
                    @endif
                    <td class="text-left">{{ __('Remarks') }}</td>
                </tr>
                @foreach($address['vehicles'] as $vehicle)
                    <tr class="font-7and44">
                        <th class="text-left">{{ $loop->iteration }}</th>
                        <th class="text-left">{{ $vehicle->id }}</th>
                        <th class="text-left">{{ $vehicle->make?->name }} {{ $vehicle->vehicleModel?->name }}</th>
                        @if($transportOrder->vehicle_type->value != \App\Enums\TransportableType::Pre_order_vehicles->value)
                            <th class="text-left">{{ $vehicle->vin }}</th>
                            <th class="text-left">{{ $vehicle->nl_registration_number }}</th>
                        @endif
                        <th class="text-left">{{ $vehicle->pivot->location_free_text }}</th>
                    </tr>
                @endforeach
            </table>
        </div>
    @endforeach
</div>

@include('components.transport-orders.transport-terms-and-conditions')
</body>
</html>

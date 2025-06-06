<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8"/>
    <title>{{ __('Transport Order') }} {{ $transportOrder->id }}</title>

    @include('styles.reset')
    @include('styles.pdf')
</head>
<body>
<div class="header-gray-background w-full">
    <table class="w-full over-overlay-image">
        <tr>
            {{-- First column --}}
            <td class="width-70 align-top">
                <div class="align-vertical-transport-order">
                    <div class="heading font-28">{{ __('TRANSPORT') }}</div>
                    <div class="heading font-28">{{ __('ORDER') }}</div>
                    <div class="text-s pt-1and5">{{ __('Date') }}: {{ \Carbon\Carbon::now()->format('d-m-Y') }}</div>
                    <div class="text-s">{{ __('Transport Order nr') }}: TO{{ $transportOrder->id }}</div>
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

<div class="container font-8and82 break-inside-avoid">
    @include('components.customer-company', [
        'topText' => __('By signing or executing this Transport Order (Agreement) the carrier below (Transport Company)'),
        'company' => $transportOrder->transportCompany,
        'spacing' => false,
        'bottomText' => __('commits to transport the following vehicles')
    ])
</div>

<div class="container break-inside-avoid">
    <div class="w-full bg-gray rounded p-half">
        <table class="w-full font-8and82">
            <tr>
                <th class="text-left py-half" colspan="2">{{ __('Summary') }}</th>
                <th class="text-left py-half">
                    @if($addressesArray['pickUp']['addressesCount'])
                        {{ $addressesArray['pickUp']['addressesCount'] }} {{ __('Pickup') }} {{ $addressesArray['pickUp']['addressesCount'] > 1 ?  __('addresses') : __('address') }}
                        @if($addressesArray['delivery']['addressesCount'])
                            ,
                        @endif
                    @endif
                    @if($addressesArray['delivery']['addressesCount'])
                        {{ $addressesArray['delivery']['addressesCount'] }} {{ __('Delivery') }} {{ $addressesArray['delivery']['addressesCount'] > 1 ?  __('addresses') : __('address') }}
                    @endif
                </th>
                <td class="text-left py-half">{{ __('Vehicles') }}</td>
                <td class="text-left py-half">{{ __('Price') }}</td>
                <td class="text-left py-half">{{ __('Per vehicle') }}</td>
            </tr>

            @foreach($addressesArray['addresses'] as $address)
                <tr>
                    <td class="text-left fw-bold">{{ $loop->iteration }}</td>
                    <td class="text-left ">{{ __('Pickup') }}</td>
                    <td class="text-left fw-bold">{{ $address['pickUpAddress'] }}</td>
                    <td class="text-left fw-bold">{{ $address['vehiclesCount'] }}</td>
                    <td class="text-left fw-bold">€ {{ $address['price'] }}</td>
                    <td class="text-left fw-bold">€ {{ $address['averagePrice'] }}</td>
                </tr>
                <tr>
                    <td class="py-half"></td>
                    <td class="text-left py-half">{{ __('Delivery') }}</td>
                    <td class="text-left py-half fw-bold">{{ $address['deliveryAddress'] }}</td>
                    <td class="py-half" colspan="3"></td>
                </tr>
            @endforeach
            <tr class="border-top font-9and80 fw-bold">
                <td class="pt-half" colspan="2">{{ __('Total') }}</td>
                <td class="pt-half"></td>
                <td class="text-left pt-half">{{ $selectedTransportablesCount }}</td>
                <td class="text-left pt-half">€ {{ $transportOrder->total_transport_price }}</td>
                <td class="text-left pt-half">€ {{ $addressesArray['averageVehicleTransportPrice'] }}</td>
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
                                <td class="px-half font-7and44">  {{ __('Pickup address') }}:</td>
                                <td class="px-half font-6and86">{{ $address['pickUpAddress'] }}</td>
                            </tr>
                            <tr>
                                <td class="width-1"></td>
                                <td class="px-half font-7and44"></td>
                                <td class="px-half font-6and86">{{ $transportOrder->pick_up_location_free_text }}</td>
                            </tr>
                            <tr>
                                <td class="width-1"></td>
                                <td class="px-half font-7and44"></td>
                                <td class="px-half font-6and86">{{ $transportOrder->pick_up_notes }}</td>
                            </tr>
                            <tr>
                                <td class="width-1"></td>
                                <td class="px-half font-7and44 pt-1">{{ __('Contact person') }}</td>
                                <td class="px-half font-6and86 pt-1">{{ $address['pickUpCompany']?->logisticsContact?->full_name }}</td>
                            </tr>
                            <tr>
                                <td class="width-1"></td>
                                <td class="px-half font-7and44">{{ __('E-mail') }}</td>
                                <td class="px-half font-6and86">{{ $address['pickUpCompany']?->logisticsContact?->email }}</td>
                            </tr>
                            <tr>
                                <td class="width-1"></td>
                                <td class="px-half font-7and44">{{ __('Telephone') }}</td>
                                <td class="px-half font-6and86">{{ $address['pickUpCompany']?->logisticsContact?->mobile }}</td>
                            </tr>
                            <tr>
                                <td class="width-1"></td>
                                <td class="px-half font-7and44 pt-1">{{ __('Pickup times') }}</td>
                                <td class="px-half font-6and86 pt-1 text-price">{{ $address['pickUpCompany']?->logistics_times }}</td>
                            </tr>
                            <tr>
                                <td class="width-1"></td>
                                <td class="px-half font-7and44">{{ __('Pickup remarks') }}</td>
                                <td class="px-half font-6and86 text-price">{{ $address['pickUpCompany']?->logistics_remarks }}</td>
                            </tr>
                            <tr>
                                <td class="width-1"></td>
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
                                <td class="px-half font-7and44"></td>
                                <td class="px-half font-6and86">{{ $transportOrder->delivery_location_free_text }}</td>
                            </tr>
                            <tr>
                                <td class="px-half font-7and44"></td>
                                <td class="px-half font-6and86">{{ $transportOrder->delivery_notes }}</td>
                            </tr>
                            <tr>
                                <td class="p-half font-7and44">{{ __('Contact person') }}</td>
                                <td class="p-half font-6and86">{{ $address['deliveryCompany']?->logisticsContact?->full_name }}</td>
                            </tr>
                            <tr>
                                <td class="px-half font-7and44">{{ __('E-mail') }}</td>
                                <td class="px-half font-6and86">{{ $address['deliveryCompany']?->logisticsContact?->email }}</td>
                            </tr>
                            <tr>
                                <td class="px-half font-7and44">{{ __('Telephone') }}</td>
                                <td class="px-half font-6and86">{{ $address['deliveryCompany']?->logisticsContact?->mobile }}</td>
                            </tr>
                            <tr>
                                <td class="px-half font-7and44 pt-1">{{ __('Logistics times') }}</td>
                                <td class="px-half font-6and86 pt-1 text-price">{{ $address['deliveryCompany']?->logistics_times }}</td>
                            </tr>
                            <tr>
                                <td class="px-half font-7and44">{{ __('Delivery remarks') }}</td>
                                <td class="px-half font-6and86 text-price">{{ $address['deliveryCompany']?->logistics_remarks }}</td>
                            </tr>
                            <tr>
                                <td class="px-half font-7and44">{{ __('Delivery deadline') }}</td>
                                <td class="px-half font-6and86 text-price">{{ $transportOrder->deliver_before_date?->format('d-m-Y, H:i') }}</td>
                            </tr>

                            <tr>
                                <td class="px-half font-7and44">{{ __('Planned delivery') }}</td>
                                <td class="px-half font-6and86 text-price">{{ $transportOrder->planned_delivery_date?->format('d-m-Y, H:i') }}</td>
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
                    <td class="text-left">{{ __('Price') }}</td>
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
                        <th class="text-left">€ {{ $vehicle->pivot->price }}</th>
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

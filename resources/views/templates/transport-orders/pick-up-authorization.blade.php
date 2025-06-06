<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8"/>
    <title>{{ __('Transport Order Pick Up Authorization') }} {{ $transportOrder->id }}</title>

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
                    <div class="heading font-28">{{ __('AUTHORISATION') }}</div>
                    <div class="heading font-22">{{ __('FOR VEHICLE PICKUP') }}</div>
                    <div class="text-s pt-1and5">{{ __('Date') }}: {{ \Carbon\Carbon::now()->format('d-m-Y') }}</div>
                    <div class="text-s">{{ __('Authorization nr') }}: A{{ $transportOrder->id }}</div>
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
    <div class="mt-2">
        <span class="fw-bold">{{ $transportOrder->creator->company?->name }}</span>, {{ __('the issuer of this authorisation, grants the Authorised Company and/or Authorised Person permission to collect and transport the
vehicles listed below on its behalf.') }}
    </div>

    <table class="w-full mt-2">
        <tr>
            {{-- First column --}}
            <td class="width-third align-top">
                <div class="py-quarter">
                    {{ __('Authorised Company:') }}
                </div>

                @include('components.customer-company', [
                    'topText' => '',
                    'company' => $transportOrder->transportCompany,
                    'bottomText' => '',
                ])
            </td>

            {{-- Second column column --}}
            <td class="align-top">
                <div class="py-quarter">
                    {{ __('Authorised Person:') }}
                </div>

                <table>
                    <tr>
                        <td class="py-half">{{ __('Name') }}:</td>
                        <td class="py-half">__________________________________</td>
                    </tr>
                    <tr>
                        <td class="py-half">{{ __('Address') }}:</td>
                        <td class="py-half">__________________________________</td>
                    </tr>
                    <tr>
                        <td class="py-half">{{ __('Identification no.') }}:</td>
                        <td class="py-half">__________________________________</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>

<div class="container">
    <table class="w-full break-inside-avoid">
        <tr class="font-6and86">
            <td class="text-left">{{ __('Nr.') }}</td>
            <td class="text-left">{{ __('Ref. Vehicx') }}</td>
            <td class="text-left">{{ __('Make/ Model/ Engine') }}</td>
            <td class="text-left">{{ __('VIN') }}</td>
            @if($transportOrder->vehicle_type->value != \App\Enums\TransportableType::Pre_order_vehicles->value)
                <td class="text-left">{{ __('Registration nr') }}</td>
            @endif
        </tr>

        @foreach($selectedTransportables as $vehicle)
            <tr class="font-7and84">
                <th class="text-left">{{ $loop->iteration }}</th>
                <th class="text-left">{{ $vehicle->id }}</th>
                <th class="text-left">
                    {{ $vehicle->make?->name }}
                    {{ $vehicle->vehicleModel?->name }}
                    @if($transportOrder->vehicle_type->value != \App\Enums\TransportableType::Service_vehicles->value)
                        {{ $vehicle->engine?->name }}
                    @endif
                </th>
                <th class="text-left">{{ $vehicle->vin }}</th>
                @if($transportOrder->vehicle_type->value != \App\Enums\TransportableType::Pre_order_vehicles->value)
                    <th class="text-left">{{ $vehicle->nl_registration_number }}</th>
                @endif
            </tr>
        @endforeach
    </table>

    <table class="w-full mt-2 font-8and82 break-inside-avoid">
        <tr>
            {{-- First column --}}
            <td class="width-half align-top">
                <table>
                    <tr>
                        <td class="p-half">{{ __('Pickup address') }}:</td>
                        <td class="p-half fw-bold">{{ $transportOrder->pickUpLocation?->address }}</td>
                    </tr>
                    <tr>
                        <td class="p-half">
                        <td class="p-half fw-bold">{{ $transportOrder->pick_up_location_free_text }}</td>
                    </tr>
                </table>
            </td>

            {{-- Second column column --}}
            <td class="width-half align-top">
                <table>
                    <tr>
                        <td class="p-half">{{ __('Contactperson') }}:</td>
                        <td class="p-half fw-bold">{{ $authCompany->logisticsContact?->full_name }}</td>
                    </tr>
                    <tr>
                        <td class="p-half">{{ __('E-mail') }}:</td>
                        <td class="p-half fw-bold">{{ $authCompany->logisticsContact?->email }}</td>
                    </tr>
                    <tr>
                        <td class="p-half">{{ __('Telephone') }}:</td>
                        <td class="p-half fw-bold">{{ $authCompany->logisticsContact?->mobile }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="mt-2 font-8and82">
        {{ __('The') }} <span class="fw-bold">{{ __('Vehicle registration documents part I and II, the COC and both Keys') }}</span> {{ __('should be handed over to the Autorised Person upon collection.') }}
    </div>
</div>

<div class="container font-8and82 break-inside-avoid">
    @include('components.transport-orders.signatures', [
        'issuer' => $transportOrder->creator?->company->name,
    ])
</div>
</body>
</html>

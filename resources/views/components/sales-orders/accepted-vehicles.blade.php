<table class="w-full pt-2 text-s">
    @php
        $visibleCount = 0;
    @endphp
    <tr>
        @foreach ($salesOrder->vehicles as $vehicle)
            @if ($visibleCount > 0 && $visibleCount % 2 === 0)
    </tr>
    <tr>
        @endif

        <td class="width-quarter py-half">
            <table>
                <tr>
                    <td class="px-half fw-bold">{{ $loop->iteration }}</td>
                    <td class="px-half">{{ __('Vehicle make and model') }}</td>
                    <td class="px-half fw-bold">{{ $vehicle->make?->name }} {{ $vehicle->vehicleModel?->name }} {{ $vehicle->vehicle_model_free_text }}</td>
                </tr>
                @if($vehicle->vin)
                    <tr>
                        <td class="px-half"></td>
                        <td class="px-half">{{ __('Chassis number') }}</td>
                        <td class="px-half fw-bold">{{ $vehicle->vin }}</td>
                    </tr>
                @endif
                @if($vehicle->nl_registration_number)
                    <tr>
                        <td class="px-half"></td>
                        <td class="px-half">{{ __('Registration number (if applicable)') }}</td>
                        <td class="px-half fw-bold">{{ $vehicle->nl_registration_number }}</td>
                    </tr>
                @endif
            </table>
        </td>

        @php
            $visibleCount++; // Increment the counter for each visible item
        @endphp
        @endforeach
    </tr>
</table>

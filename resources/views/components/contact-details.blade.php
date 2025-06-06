<div class="container break-inside-avoid mt-2 w-full text-s">
    <div class="fw-bold p-half heading">{{ __('Contact details') }} {{ $company?->name }}</div>

    <table class="w-full mt-1">
        @foreach($company?->addresses?->where('type', \App\Enums\CompanyAddressType::Logistics) as $deliveryAddress)
            <tr>
                <td class="width-fifteen p-quarter">
                    {{ __('Delivery address') }}
                    @if($loop->iteration > 1)
                        <span>{{ $loop->iteration }}</span>
                    @endif
                </td>
                <td class="p-quarter fw-bold">
                    {{ $deliveryAddress->address }}
                </td>
                @if($deliveryAddress->remarks)
                    <td class="p-quarter fw-bold">
                        {{ $deliveryAddress->remarks }}
                    </td>
                @endif
            </tr>
        @endforeach
        <tr>
            <td class="width-fifteen p-quarter">
                {{ __('Logistics times') }}
            </td>
            <td class="p-quarter fw-bold">
                {{ $company?->logistics_times }}
            </td>
        </tr>
        <tr>
            <td class="width-fifteen p-quarter">
                {{ __('Delivery remarks') }}
            </td>
            <td class="p-quarter fw-bold">
                {{ $company?->logistics_remarks }}
            </td>
        </tr>
    </table>
</div>

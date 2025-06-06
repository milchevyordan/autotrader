<table class="w-full text-s mt-1 break-inside-avoid">
    @if($salesOrder->calculation_on_sales_order && $allItemsAndAdditionals)
        <tr class="border-bottom fw-bold">
            <td class="p-half width-80 font-9">{{ __('Total Sales price net') }}, {{ $vehiclesCount }} {{ __('vehicles') }}
            </td>
            <td class="p-half no-wrap font-10">€ {{ $salesOrder->total_sales_price_exclude_vat }}</td>
        </tr>
    @endif
    @foreach ($salesOrder->orderItems as $item)
        @if (!$item->in_output)
            @continue
        @endif

        <tr class="border-bottom">
            <td class="p-half width-80 font-9">{{ $item->item?->shortcode }} ({{ $vehiclesCount }}x)</td>
            <td class="p-half no-wrap font-10 fw-bold">
                € {{ \App\Support\CurrencyHelper::convertUnitsToCurrency(\App\Support\CurrencyHelper::convertCurrencyToUnits($item->sale_price) * $vehiclesCount) }}</td>
        </tr>
    @endforeach
    @foreach ($salesOrder->orderServices as $service)
        @if (!$service->in_output)
            @continue
        @endif

        <tr class="border-bottom">
            <td class="p-half width-80 font-9">{{ $service->name }} ({{ $vehiclesCount }}x)</td>
            <td class="p-half no-wrap font-10 fw-bold">€ {{ \App\Support\CurrencyHelper::convertUnitsToCurrency(\App\Support\CurrencyHelper::convertCurrencyToUnits($service->sale_price) * $vehiclesCount) }}</td>
        </tr>
    @endforeach
    @if($salesOrder->discount && $salesOrder->discount_in_output)
        <tr class="border-bottom">
            <td class="p-half width-80 font-9">{{ __('Discount') }} ({{ $vehiclesCount }}x)</td>
            <td class="p-half no-wrap font-10 fw-bold">
                € {{ \App\Support\CurrencyHelper::convertUnitsToCurrency(\App\Support\CurrencyHelper::convertCurrencyToUnits($salesOrder->discount) * $vehiclesCount) }}</td>
        </tr>
    @endif
    @if($salesOrder->calculation_on_sales_order || !$salesOrder->is_brutto)
        <tr class="border-bottom fw-bold">
            <td class="p-half width-80 font-9">{{ __('Total Sales Price net') }}, {{ $vehiclesCount }} {{ __('vehicles') }}
            </td>
            <td class="@if(!$salesOrder->calculation_on_sales_order) text-price @endif p-half no-wrap font-15">€ {{ $salesOrder->total_sales_price_exclude_vat }}</td>
        </tr>
    @endif
    @if($salesOrder->is_brutto)
        @if($salesOrder->calculation_on_sales_order)
            <tr class="border-bottom">
                <td class="p-half width-80 font-9">
                    {{ __('VAT') }} ({{ $salesOrder->vat_percentage }}%)
                </td>
                <td class="p-half no-wrap font-10 fw-bold">€ {{ $salesOrder->total_vat }}</td>
            </tr>
            <tr class="border-bottom">
                <td class="p-half width-80 font-9">{{ __('BPM') }}</td>
                <td class="p-half no-wrap font-10 fw-bold">€ {{ $salesOrder->total_bpm }}</td>
            </tr>
            <tr class="border-bottom">
                <td class="p-half width-80 font-9">{{ __('Registration costs (0%)') }}</td>
                <td class="p-half no-wrap font-10 fw-bold">€ {{ $salesOrder->total_registration_fees }}</td>
            </tr>
        @endif
        <tr class="fw-bold">
            <td class="p-half width-80 font-9">{{ __('Total Order price in/in') }}</td>
            <td class="p-half no-wrap font-11 text-price">€ {{ $salesOrder->total_sales_price }}</td>
        </tr>
    @endif
</table>

<div class="w-full mt-1 bg-gray rounded text-s">
    <div class="p-1">
        <div class="fw-bold pb-half font-10">{{ __('Conditions & Remarks') }}</div>
        @if($netto)
            <div>{{ __('Intra-Community Supply under 0% VAT Rate') }}</div>
            <div class="pt-half">{{ __('The buyer hereby declares') }}:
                <ol class="pl-2">
                    <li class="pt-half">
                        {{ __('To have a valid VAT number and to be a VAT-registered business in the country of destination.') }}
                    </li>
                    <li class="pt-half">
                        {{ __('To ensure that VAT due will be paid in the country of destination in accordance with local VAT regulations.') }}
                    </li>
                    <li class="pt-half">
                        {{ __('To register the vehicle in the country of destination.') }}
                    </li>
                </ol>
            </div>
        @endif
        <div
            class="pt-half">{{ __('Delivery indication week') }} {{ \App\Services\WeekService::generateWeekInputString($salesOrder->delivery_week) }}</div>
        <div class="pt-half">{{ \App\Support\StringHelper::replaceUnderscores($salesOrder->damage?->name) }}</div>
        <div class="pt-half">{{ __('Transport') }}
            {{ $salesOrder->transport_included ? __('Included') : __('Pick up by buyer') }}</div>
        <div class="pt-half">{{ \App\Support\StringHelper::replaceUnderscores($salesOrder->payment_condition?->name) }}</div>
        @if($salesOrder->payment_condition?->value == \App\Enums\PaymentCondition::See_additional_information->value)
            <div class="pt-half">{{ $salesOrder->payment_condition_free_text }}</div>
        @endif
    </div>
</div>

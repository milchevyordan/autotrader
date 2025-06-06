<div class="w-full pt-3 text-s break-inside-avoid py-1">
    <div>{{ __('Buyer`s signature') }}:</div>
    <div class="pt-half">{{ $buyerName }}</div>

    <div style="padding-left: 350px">
        <img src="{{ public_path($signatureImage) }}" alt="signature image" class="payment-image">
    </div>

    <div>{{ __('Name of signatory') }}:</div>
    <div class="pt-half">{{ __('Position') }}:</div>
    <div class="pt-half">{{ __('Date') }}:</div>
</div>

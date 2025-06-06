<table class="w-full">
    <tr>
        {{-- First column --}}
        <td class="width-half align-top px-half">
            <div class="p-half">
                {{ __('Buyer`s signature') }}:
            </div>
            <div class="p-half">
                {{ $buyerName }}
            </div>
        </td>

        {{-- Second column column --}}
        <td class="width-half align-top px-half">
            <div class="p-half">
                {{ __('Seller`s signature') }}:
            </div>
            <div class="p-half">
                {{ $sellerName }}:
            </div>
        </td>
    </tr>

    <tr>
        {{-- First column --}}
        <td>
        </td>

        {{-- Second column column --}}
        <td>
            <img src="{{ public_path($signatureImage) }}" alt="signature image" class="payment-image">
        </td>
    </tr>

    <tr>
        {{-- First column --}}
        <td class="width-half align-top px-half">
            <div class="p-half">
                {{ __('Name of signatory') }}:
            </div>
            <div class="p-half">
                {{ __('Place') }}:
            </div>
            <div class="p-half">
                {{ __('Date') }}:
            </div>
        </td>

        {{-- Second column column --}}
        <td class="width-half align-top px-half">
            <div class="p-half">
                {{ __('Name of signatory') }}:
            </div>
            <div class="p-half">
                {{ __('Place') }}:
            </div>
            <div class="p-half">
                {{ __('Date') }}:
            </div>
        </td>
    </tr>
</table>

<div class="h-150">
</div>

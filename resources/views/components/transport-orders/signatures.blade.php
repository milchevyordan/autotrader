<table class="w-full mt-3">
    <tr>
        {{-- First column --}}
        <td class="width-half align-top px-half">
            <div class="p-half">
                {{ __('Signature of the Issuer') }}:
            </div>
            <div class="p-half">
                {{ $issuer }}
            </div>
        </td>

        {{-- Second column column --}}
        <td class="width-half align-top px-half">
            <div class="p-half">
                {{ __('Signature of the Authorised') }}:
            </div>
        </td>
    </tr>
</table>

<div style="padding-left: 350px">
    <img src="{{ public_path($signatureImage) }}" alt="signature image" class="payment-image">
</div>

<table class="w-full">
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

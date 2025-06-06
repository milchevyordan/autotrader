<div class="font-6">
    {{ $topText }}
</div>

<div class="heading pt-1">
    {{ $company?->name }}
</div>

<div>
    {{ $company?->address }}
</div>

<div>
    {{ $company?->postal_code }} {{ $company?->city }}
</div>

<div>
    {{ $company?->country?->name }}
</div>

<div>
    {{ __('VAT nr.') }} {{ $company?->vat_number }}
</div>

<div class="font-6 pt-1">
    {{ $bottomText }}
</div>

<div class="text-xs">
    {{ __('is ordering from the company below (Seller)') }}
</div>

<div class="fw-bold pt-1">
    {{ $salesOrder->creator->company?->name }}
</div>

<div class="pt-half">
    {{ $salesOrder->creator->company?->address }}
</div>

<div class="pt-half">
    {{ $salesOrder->creator->company?->postal_code }} {{ $salesOrder->creator->company?->city }}
</div>

<div class="pt-half">
    {{ $salesOrder->creator->company?->country?->name }}
</div>

<div class="pt-half">
    {{ __('KvK nr.') }} {{ $salesOrder->creator->company?->kvk_number }}
</div>

<div class="pt-half">
    {{ __('VAT nr.') }} {{ $salesOrder->creator->company?->vat_number }}
</div>

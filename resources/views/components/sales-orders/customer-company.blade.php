<div class="text-xs">
    {{ __('By signing this Sales Contract (Agreement) customer below (Buyer)') }}
</div>

<div class="fw-bold pt-1">
    {{ $salesOrder->customerCompany->name }}
</div>

<div class="pt-half">
    {{ $salesOrder->customerCompany->address }}
</div>

<div class="pt-half">
    {{ $salesOrder->customerCompany->postal_code }} {{ $salesOrder->customerCompany->city }}
</div>

<div class="pt-half">
    {{ $salesOrder->customerCompany->country?->name }}
</div>

<div class="pt-half">
    {{ __('VAT nr.') }} {{ $salesOrder->customerCompany?->vat_number }}
</div>

<div class="pt-1 text-xs">
    {{ __('the following vehicles and/or items') }}
</div>

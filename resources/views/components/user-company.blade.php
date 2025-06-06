<div class="font-6">
    {{ $topText }}
</div>

<div class="heading pt-1">
    {{ $model->creator->company?->name }}
</div>

<div>
    {{ $model->creator->company?->address }}
</div>

<div>
    {{ $model->creator->company?->postal_code }} {{ $model->creator->company?->city }}
</div>

<div>
    {{ $model->creator->company?->country?->name }}
</div>


<div class="{{ $spacing ? 'pt-1' : '' }}">
    {{ __('KvK nr.') }} {{ $model->creator->company?->kvk_number }}
</div>

<div>
    {{ __('VAT nr.') }} {{ $model->creator->company?->vat_number }}
</div>

<div class="{{ $spacing ? 'pt-1' : '' }}">
    {{ __('IBAN') }} {{ $model->creator->company?->iban }}
</div>

<div>
    {{ __('BIC') }} {{ $model->creator->company?->swift_or_bic }}
</div>

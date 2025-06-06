{{ $salesOrder->customerCompany?->name }}
, {{ __('located at') }} {{ $salesOrder->customerCompany?->address }}
, {{ $salesOrder->customerCompany?->postal_code }} {{ $salesOrder->customerCompany?->city }}
, {{ $salesOrder->customerCompany?->country?->name }} {{ __('with VAT nr.') }}  {{ $salesOrder->customerCompany?->vat_number }}

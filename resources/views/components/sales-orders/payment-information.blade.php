<div class="header-gray-background w-full">
    <div class="align-vertical-payment-information">
        <div class="heading font-28">{{ __('PAYMENT') }}</div>
        <div class="heading font-28">{{ __('INFORMATION') }}</div>
        <div class="text-s pt-2">{{ __('Part of Sales Contact nr') }}: {{ $salesOrder->number }}</div>
    </div>
    <img src="{{ public_path($headerQuoteTransportAndDeclarationImage) }}" alt="header quote transport and declaration image" class="overlay-image">
</div>

<div class="container">
    <div class="pt-3 text-s">
        {{ __('Thank you for your business!') }}
    </div>

    <div class="pt-3 text-s">
        {{ __('Please find the payment details below') }}:
    </div>

    <table class="pt-2">
        <tr>
            <td class="p-half text-s">{{ __('Bank Name') }}:</td>
            <td class="p-half fw-bold font-16">{{ $salesOrder->creator->company?->bank_name }}</td>
        </tr>

        <tr>
            <td class="p-half text-s">{{ __('Account Name') }}:</td>
            <td class="p-half fw-bold font-16">{{ $salesOrder->creator->company?->name }}</td>
        </tr>

        <tr>
            <td class="p-half text-s">{{ __('IBAN') }}:</td>
            <td class="p-half fw-bold font-16">{{ $salesOrder->creator->company?->iban }}</td>
        </tr>

        <tr>
            <td class="p-half text-s">{{ __('SWIFT/BIC') }}:</td>
            <td class="p-half fw-bold font-16">{{ $salesOrder->creator->company?->swift_or_bic }}</td>
        </tr>

        <tr>
            <td class="p-half text-s">{{ __('Reference') }}:</td>
            <td class="p-half fw-bold font-16">{{ __('Invoice Number or Order Number') }} ({{ $salesOrder->id }}) + VIN</td>
        </tr>
    </table>


    <div class="pt-3 text-s">
        {{ __('Kindly note the payment terms as follows') }}:

        <ul class="pl-2">
            <li class="pt-1 fw-bold">{{ __('Vehicle and/or BPM invoices: within 2 days of the invoice date') }}</li>
            <li class="pt-1 fw-bold">{{ __('Other items and services: within 14 days of the invoice date') }}</li>
        </ul>
    </div>

    <div class="pt-1 text-s">
        {{ __('Important: Delivery or pickup of vehicles is only possible once full payment has been verified by our bank.') }}
    </div>

    <div class="pt-3 text-s">
        {{ __('Should you have any questions, please feel free to contact us.') }}
    </div>

    <div class="pt-3 text-s">
        {{ __('Kind regards,') }}
    </div>

    <div class="pt-3 text-s">
        {{ __('The Vehicx Team') }}
    </div>
</div>

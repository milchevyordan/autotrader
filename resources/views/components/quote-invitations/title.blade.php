<div class="heading font-28">{{ __('QUOTATION') }}</div>
<div class="text-s pt-1 heading">{{ $company->name }}</div>
<div class="text-s">{{ auth()->user()->name }}</div>
<div class="text-s pb-1">{{ $company->address }}</div>
<div class="text-s">{{ __('Date') }}: {{ \Carbon\Carbon::now()->format('d-m-Y, H:i') }}</div>
@isset($quote)
    <div class="text-s">{{ __('Quotation nr') }}: {{ $quote->id }}</div>
@endisset
@isset($customerName)
    <div class="text-s">{{ __('Customer') }}: {{ $customerName }}</div>
@endisset

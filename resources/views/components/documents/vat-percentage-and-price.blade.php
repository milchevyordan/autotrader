<td class="width-12and5 p-quarter fw-bold no-wrap align-top">{{ $line->vat_percentage ?? 0 }}%</td>
<td class="width-12and5 p-quarter fw-bold no-wrap align-top">€ {{ $line->price_include_vat }} @if(isset($creditInvoice) && $creditInvoice) -/- @endif</td>

<tr class="heading">
    <td colspan="2">{{ __('Items') }}</td>
</tr>

<tr class="information">
    <td colspan="2">
        <table cellpadding="0" cellspacing="0">
            <tr class="thead">
                <td>#</td>
                <td>{{ __('Type') }}</td>
                <td>{{ __('Shortcode') }}</td>
                <td>{{ __('Sale Price') }}</td>
                <td>{{ __('In Output') }}</td>
            </tr>

            @foreach($model->orderItems as $item)
                <tr>
                    <td>{{ $item->id }}</td>
                    <td>{{ $item->item?->type->name }}</td>
                    <td>{{ $item->item?->shortcode }}</td>
                    <td>{{ $item->sale_price }}</td>
                    <td>{{ \App\Support\StringHelper::booleanRepresentation($item->in_output) }}</td>
                </tr>
            @endforeach
        </table>
    </td>
</tr>

<tr class="heading">
    <td colspan="2">{{ __('Additional Services') }}</td>
</tr>

<tr class="information">
    <td colspan="2">
        <table cellpadding="0" cellspacing="0">
            <tr class="thead">
                <td>{{ __('Name') }}</td>
                <td>{{ __('Sale Price') }}</td>
                <td>{{ __('In Output') }}</td>
            </tr>

            @foreach($model->orderServices as $orderService)
                <tr>
                    <td>{{ $orderService->name }}</td>
                    <td>{{ $orderService->sale_price }}</td>
                    <td>{{ \App\Support\StringHelper::booleanRepresentation($orderService->in_output) }}</td>
                </tr>
            @endforeach
        </table>
    </td>
</tr>

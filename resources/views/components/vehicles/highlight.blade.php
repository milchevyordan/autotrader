@if($vehicle->{$item['attribute']} && $vehicle->{$item['attribute']}->name != 'No')
    @if($quoteSingleVehicle)
        <td class="width-quarter">
            <div class="p-half border-bottom-white">{{ \App\Support\StringHelper::replaceUnderscores($vehicle->{$item['attribute']}->name) }}</div>
        </td>
    @else
        <span>
            {{ \App\Support\StringHelper::replaceUnderscores($vehicle->{$item['attribute']}->name) }} @if(!$loop->last),@endif
        </span>
    @endif
@elseif(is_null($vehicle->{$item['attribute']}) && $vehicle->{$item['attribute_free_text']})
    @if($quoteSingleVehicle)
        <td class="width-quarter">
            <div class="p-half border-bottom-white">{{ $vehicle->{$item['label']} }} {{ $vehicle->{$item['attribute_free_text']} }}</div>
        </td>
    @else
        <span>
            {{ $vehicle->{$item['label']} }} {{ $vehicle->{$item['attribute_free_text']} }}
        @if(!$loop->last),@endif
        </span>
    @endif
@endif

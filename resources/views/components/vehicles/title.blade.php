@if(isset($boldInitialText) && $boldInitialText)
    <span class="fw-bold">
@endif

{{ $vehicle->make?->name }}
{{ $vehicle->vehicleModel?->name }}
{{ $vehicle->vehicle_model_free_text }}

@if(isset($boldInitialText) && $boldInitialText)
    </span>
@endif

 {{ $vehicle->variant?->name }}
 {{ $vehicle->variant_free_text }}
 {{ $vehicle->engine?->name }}
 {{ $vehicle->engine_free_text }}
 @if ($vehicle->kw)
     {{ $vehicle->kw }} kW
 @endif
 @if ($vehicle->hp)
     ({{ $vehicle->hp }} PS)
 @endif
@if($vehicle->transmission)
 {{ \App\Support\StringHelper::replaceUnderscores($vehicle->transmission?->name ?? '') }}
@endif
 {{ $vehicle->transmission_free_text }}

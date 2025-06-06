<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8"/>
    <title>{{ __('Vehicle') }} {{ $vehicle->id }}</title>

    @include('styles.reset')
    @include('styles.pdf')
</head>

<body>
<div class="container" style="margin-top: -40px !important;">
    <table class="w-full">
        <tr>
            {{-- First column --}}
            <td class="width-two-thirds px-half">
                <table>
                    <tr>
                        <td class="pt-half font-8and82">{{ __('Date') }}</td>
                        <td class="pt-half fw-bold font-9and80 align-right">{{ \Carbon\Carbon::now()->format('d-m-Y') }}</td>
                    </tr>

                    <tr>
                        <td class="pt-half font-8and82">{{ __('Vehicle Id') }}</td>
                        <td class="pt-half fw-bold text-xl font-19and5 align-right">{{ $vehicle->id }}</td>
                    </tr>
                </table>
            </td>
            {{-- / First column --}}

            {{-- Second column --}}
            <td>
                <img src="{{ public_path($stickerImage) }}" width="266px" alt="sticker image"/>
            </td>
            {{-- / Second column --}}
        </tr>
    </table>

    <table class="w-full border-bottom-black border-top-black heading font-17and55 pt-1">
        <tr>
            <td class="width-third py-quarter">
                {{ $vehicle->customerName }}
            </td>

            <td class="width-third py-quarter">
                {{ $vehicle->make?->name }} {{ $vehicle->vehicleModel?->name }}
            </td>

            <td class="width-third py-quarter">
                {{ $vehicle->specific_exterior_color?->name }}
            </td>
        </tr>
    </table>

    <div class="heading font-175and5 text-center">{{ $lastFourVinCharacters }}</div>

    <div class="w-full pt-1 fw-bold">
        <div>
            <span class="px-half font-9and80">{{ __('Expected counter reading') }}:</span>
            <span class="px-half font-11and70">{{ $vehicle->kilometers }}</span>
        </div>
        <table>
            <tr>
                {{--First column--}}
                <td class="width-half">
                    <table>
                        <tr>
                            <td class="px-half font-9and80">{{ __('Odometer reading') }}:</td>
                            <td class="px-half">__________________________________</td>
                        </tr>
                        <tr>
                            <td class="px-half font-9and80">{{ __('Taken by') }}:</td>
                            <td class="px-half">__________________________________</td>
                        </tr>
                    </table>
                </td>
                {{--First column--}}

                {{--Second column--}}
                <td class="width-half">
                    <table>
                        <tr>
                            <td class="px-half font-9and80">{{ __('Date of arrival') }}:</td>
                            <td class="px-half">__________________________________</td>
                        </tr>
                        <tr>
                            <td class="px-half font-9and80">{{ __('Date of issue') }}:</td>
                            <td class="px-half">__________________________________</td>
                        </tr>
                    </table>
                </td>
                {{--Second column--}}
            </tr>
        </table>
        <div>
            <div class="px-half font-9and80">
                {{ __('Notes') }}:
            </div>
            <div class="text-center py-half">
                _____________________________________________________________________________
            </div>
            <div class="text-center py-half">
                _____________________________________________________________________________
            </div>
            <div class="text-center py-half">
                _____________________________________________________________________________
            </div>
        </div>
    </div>

    <div class="sticker-div">
        <table class="sticker-table">
            <tr>
                @foreach([__('License plate part 1'), __('License plate part 2'), __('COC')] as $place)
                    @include('components.transport-orders.sticker')
                @endforeach
            </tr>
            <tr>
                @foreach([__('1st Key'), __('2nd Key'), __('2nd set of wheels')] as $place)
                    @include('components.transport-orders.sticker')
                @endforeach
            </tr>
            <tr>
                @foreach([__('Intake + photos'), __('Work order completed'), __('Control')] as $place)
                    @include('components.transport-orders.sticker')
                @endforeach
            </tr>
        </table>
    </div>
</div>
</body>
</html>

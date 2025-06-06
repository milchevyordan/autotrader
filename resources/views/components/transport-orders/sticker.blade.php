<td class="width-third">
    <div class="m-quarter border sticker">
        <table class="w-full px-quarter">
            <tr>
                <td class="width-half font-8and82">
                    <div>
                        {{ $vehicle->customerName }}
                    </div>
                    <div>
                        {{ $vehicle->make?->name }} {{ $vehicle->vehicleModel?->name }}
                    </div>
                    <div>
                        {{ $vehicle->specific_exterior_color?->name }}
                    </div>
                    <div class="fw-bold pt-half">
                        {{ $place }}
                    </div>
                </td>
                <td class="width-half text-right">
                    <div class="font-19and5 fw-bold">
                        {{ $lastFourVinCharacters }}
                    </div>
                    <div class="pt-half">
                        <img src="{{ public_path($stickerImage) }}" width="132px" alt="sticker image"/>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</td>

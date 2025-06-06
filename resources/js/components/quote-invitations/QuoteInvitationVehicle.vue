<script setup lang="ts">
import { ColorType } from "@/enums/ColorType";
import { FuelType } from "@/enums/FuelType";
import { Transmission } from "@/enums/Transmission";
import { Vehicle } from "@/types";
import { findEnumKeyByValue, getImage, replaceEnumUnderscores } from "@/utils";

defineProps<{
    vehicle: Vehicle;
}>();
</script>

<template>
    <div class="row flex shadow-md p-4 m-4 text-sm font-light">
        <div class="col max-w-[150px] mx-4">
            <img
                class="object-cover rounded"
                :src="getImage(vehicle.image_path)"
                alt="vehicle image"
            />
        </div>

        <div class="col">
            <h3 class="font-bold text-md">
                {{ vehicle.make?.name }} {{ vehicle.vehicle_model?.name }}
                {{ vehicle.engine?.name }}
            </h3>

            <div class="flex gap-4 justify-between">
                <div class="col">
                    {{ __("Kilometers") }}
                </div>
                <div class="col">
                    {{ vehicle.kilometers }}
                </div>
            </div>

            <div class="flex gap-4 justify-between">
                <div class="col">
                    {{ __("Transmission") }}
                </div>
                <div class="col">
                    {{ findEnumKeyByValue(Transmission, vehicle.transmission) }}
                </div>
            </div>

            <div class="flex gap-4 justify-between">
                <div class="col">{{ __("Power") }} / {{ __("Fuel") }}</div>
                <div class="col">
                    {{ vehicle.kw }} |
                    {{
                        replaceEnumUnderscores(
                            findEnumKeyByValue(FuelType, vehicle.fuel)
                        )
                    }}
                </div>
            </div>

            <div class="flex gap-4 justify-between">
                <div class="col">
                    {{ __("Color") }}
                </div>
                <div class="col">
                    {{ findEnumKeyByValue(ColorType, vehicle.color_type) }}
                </div>
            </div>
        </div>
    </div>
</template>

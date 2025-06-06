<script setup lang="ts">
import { Link } from "@inertiajs/vue3";

import WeekRangePicker from "@/components/html/WeekRangePicker.vue";
import InformationRow from "@/components/html/InformationRow.vue";
import { ColorType } from "@/enums/ColorType";
import { Transmission } from "@/enums/Transmission";
import { AdditionalService, OrderItem, Vehicle, WeekPicker } from "@/types";
import { findEnumKeyByValue, getImage, replaceEnumUnderscores } from "@/utils";

defineProps<{
    resource: Vehicle;
    orderItems?: OrderItem[];
    orderServices?: AdditionalService[];
    deliveryWeek?: WeekPicker;
}>();
</script>

<template>
    <div class="rounded-t-lg overflow-hidden pb-2">
        <img
            class="w-auto max-h-72"
            :src="getImage(resource.image_path)"
            alt="vehicle image"
        />
    </div>

    <div class="p-4 border-b border-[#E9E7E7] font-bold">
        {{ __("Vehicle") }} #{{ resource.id }}
    </div>

    <InformationRow v-if="resource.make" :title="__('Make')">
        {{ resource.make.name }}
    </InformationRow>

    <InformationRow v-if="resource.vehicle_model" :title="__('Model')">
        {{ resource.vehicle_model.name }}
    </InformationRow>

    <InformationRow v-if="resource.engine" :title="__('Engine')">
        {{ resource.engine.name }}
    </InformationRow>

    <InformationRow :title="__('Kilometers')">
        {{ resource.kilometers }}
    </InformationRow>

    <InformationRow :title="__('Transmission')">
        {{
            replaceEnumUnderscores(
                findEnumKeyByValue(Transmission, resource.transmission)
            )
        }}
    </InformationRow>

    <InformationRow :title="__('Power kW / HP')">
        {{ resource.kw }} / {{ resource.hp }}
    </InformationRow>

    <InformationRow :title="__('Color')">
        {{ findEnumKeyByValue(ColorType, resource.color_type) }}
    </InformationRow>

    <InformationRow v-if="deliveryWeek" :title="__('Delivery week')">
        <WeekRangePicker
            :model-value="deliveryWeek"
            disabled
            :placeholder="__('Delivery Week')"
            name="delivery_week"
        />
    </InformationRow>

    <InformationRow :title="__('Sales price net')">
        {{ resource.calculation.sales_price_net }}
    </InformationRow>

    <div
        v-if="$can('edit-vehicle')"
        class="px-4 py-3 flex items-center justify-between"
    >
        <div class="text-[#6D6D73]">
            {{ __("Vehicle") }}
        </div>

        <Link
            :href="route('vehicles.edit', resource.id)"
            class="border border-[#008FE3] text-[#008FE3] px-3 py-0.5 rounded-md hover:opacity-60 active:scale-95 transition"
        >
            {{ __("Go to") }}
        </Link>
    </div>
</template>

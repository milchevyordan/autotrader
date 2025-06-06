<script setup lang="ts">
import { Vehicle } from "@/types";
import { Link } from "@inertiajs/vue3";

const props = defineProps<{
    vehicle: Vehicle;
}>();
</script>

<template>
    <div class="shadow-lg p-4 md:p-8">
        <Link
            :href="route('vehicles.edit', vehicle.id)"
            class="underline text-blue-500 hover:text-blue-700"
        >
            <h3 class="text-lg font-semibold">
                #{{ vehicle.id }} - {{ vehicle.make?.name }}
                {{ vehicle.vehicle_model?.name }}
                {{ vehicle.vehicle_model_free_text ?? "" }}
                {{ vehicle.variant?.name }}
            </h3>
        </Link>

        <h4 class="text-md font-semibold"></h4>

        <p class="text-gray-700">
            {{
                vehicle.damage_description ||
                __("No damage description available.")
            }}
        </p>
    </div>
</template>

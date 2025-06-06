<script setup lang="ts">
import { Link } from "@inertiajs/vue3";

import IconCart from "@/icons/Cart.vue";
import IconPencilSquare from "@/icons/PencilSquare.vue";
import { ServiceVehicle } from "@/types";

defineProps<{
    item: ServiceVehicle;
}>();
</script>

<template>
    <Link
        v-if="$can('edit-service-vehicle') && !item.deleted_at"
        :href="route('service-vehicles.edit', item.id)"
        class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition inline-flex items-center"
    >
        <IconPencilSquare classes="w-4 h-4 text-slate-600" />
    </Link>
    <Link
        v-if="$can('view-workflow') && item.workflow && !item.deleted_at"
        :href="route('workflows.show', item.workflow.id)"
        class="border border-[#008FE3] bg-[#008FE3] text-white rounded-md p-1 active:scale-90 transition"
    >
        <IconCart stroke="1.8" classes="w-4 h-4" />
    </Link>
</template>

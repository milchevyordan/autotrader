<script setup lang="ts">
import { Link } from "@inertiajs/vue3";

import IconPencilSquare from "@/icons/PencilSquare.vue";
import { ServiceOrder } from "@/types";

defineProps<{
    item: ServiceOrder;
}>();
</script>

<template>
    <Link
        v-if="$can('edit-service-order') && !item.deleted_at"
        :href="route('service-orders.edit', item.id)"
        class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition inline-flex items-center"
    >
        <IconPencilSquare classes="w-4 h-4 text-slate-600" />
    </Link>
</template>

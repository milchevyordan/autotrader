<script setup lang="ts">
import { Link } from "@inertiajs/vue3";

import IconPencilSquare from "@/icons/PencilSquare.vue";
import { SalesOrder } from "@/types";

defineProps<{
    item: SalesOrder;
}>();
</script>

<template>
    <Link
        v-if="$can('edit-sales-order') && !item.deleted_at"
        :href="route('sales-orders.edit', item.id)"
        class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition inline-flex items-center"
    >
        <IconPencilSquare classes="w-4 h-4 text-slate-600" />
    </Link>
</template>

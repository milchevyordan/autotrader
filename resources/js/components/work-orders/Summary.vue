<script setup lang="ts">
import { Link } from "@inertiajs/vue3";

import InformationRow from "@/components/html/InformationRow.vue";
import { WorkOrderStatus } from "@/enums/WorkOrderStatus";
import { WorkOrder } from "@/types";
import { findEnumKeyByValue, replaceEnumUnderscores } from "@/utils";

defineProps<{
    resource: WorkOrder;
}>();
</script>

<template>
    <div class="p-4 border-b border-[#E9E7E7] font-bold">
        {{ __("Work order") }} #{{ resource.id }}
    </div>

    <InformationRow :title="__('Status')">
        {{
            replaceEnumUnderscores(
                findEnumKeyByValue(WorkOrderStatus, resource.status)
            )
        }}
    </InformationRow>

    <InformationRow :title="__('Creator')">
        {{ resource.creator?.full_name }}
    </InformationRow>

    <InformationRow v-if="resource.total_price" :title="__('Total Price')">
        {{ resource.total_price }}
    </InformationRow>

    <InformationRow :title="__('Number Of Tasks')">
        {{ resource.tasks.length }}
    </InformationRow>

    <div
        v-if="$can('edit-sales-order')"
        class="px-4 py-3 flex items-center justify-between"
    >
        <div class="text-[#6D6D73]">
            {{ __("Work order") }}
        </div>

        <Link
            :href="route('work-orders.edit', resource.id)"
            class="border border-[#008FE3] text-[#008FE3] px-3 py-0.5 rounded-md hover:opacity-60 active:scale-95 transition"
        >
            {{ __("Go to") }}
        </Link>
    </div>
</template>

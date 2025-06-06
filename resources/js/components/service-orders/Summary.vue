<script setup lang="ts">
import { Link } from "@inertiajs/vue3";

import InformationRow from "@/components/html/InformationRow.vue";
import { ServiceOrderStatus } from "@/enums/ServiceOrderStatus";
import { ServiceOrder } from "@/types";
import { findEnumKeyByValue } from "@/utils";

defineProps<{
    resource: ServiceOrder;
}>();
</script>

<template>
    <div class="p-4 border-b border-[#E9E7E7] font-bold">
        {{ __("Service order") }} #{{ resource.id }}
    </div>

    <InformationRow :title="__('Status')">
        {{ findEnumKeyByValue(ServiceOrderStatus, resource.status) }}
    </InformationRow>

    <InformationRow :title="__('Creator')">
        {{ resource.creator?.full_name }}
    </InformationRow>

    <InformationRow :title="__('Customer Company')">
        {{ resource.customer_company.name }}
    </InformationRow>

    <InformationRow
        v-if="resource.customer"
        :title="__('Contact person customer')"
    >
        {{ resource.customer.full_name }}
    </InformationRow>

    <InformationRow :title="__('Sales Person')">
        {{ resource.seller.full_name }}
    </InformationRow>

    <div
        v-if="$can('edit-service-order')"
        class="px-4 py-3 flex items-center justify-between"
    >
        <div class="text-[#6D6D73]">
            {{ __("Service order") }}
        </div>

        <Link
            :href="route('service-orders.edit', resource.id)"
            class="border border-[#008FE3] text-[#008FE3] px-3 py-0.5 rounded-md hover:opacity-60 active:scale-95 transition"
        >
            {{ __("Go to") }}
        </Link>
    </div>
</template>

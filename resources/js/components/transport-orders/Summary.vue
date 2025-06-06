<script setup lang="ts">
import { Link } from "@inertiajs/vue3";

import InformationRow from "@/components/html/InformationRow.vue";
import { TransportOrderStatus } from "@/enums/TransportOrderStatus";
import { TransportType } from "@/enums/TransportType";
import { TransportOrder } from "@/types";
import {
    booleanRepresentation,
    findEnumKeyByValue,
    replaceEnumUnderscores,
} from "@/utils";

defineProps<{
    resource: TransportOrder;
}>();
</script>

<template>
    <div class="p-4 border-b border-[#E9E7E7] font-bold">
        {{ __("Transport order") }} #{{ resource.id }}
    </div>

    <InformationRow :title="__('Status')">
        {{
            replaceEnumUnderscores(
                findEnumKeyByValue(TransportOrderStatus, resource.status)
            )
        }}
    </InformationRow>

    <InformationRow :title="__('Type Of Transport')">
        {{ findEnumKeyByValue(TransportType, resource.transport_type) }}
    </InformationRow>

    <InformationRow :title="__('Creator')">
        {{ resource.creator?.full_name }}
    </InformationRow>

    <InformationRow :title="__('Transport Company Use')">
        {{ booleanRepresentation(resource.transport_company_use) }}
    </InformationRow>

    <InformationRow
        v-if="resource.transport_company_use"
        :title="__('Transport Company')"
    >
        {{ resource.transport_company?.name }}
    </InformationRow>

    <InformationRow v-if="resource.transporter" :title="__('Contact person')">
        {{ resource.transporter?.full_name }}
    </InformationRow>

    <InformationRow
        v-if="resource.total_transport_price"
        :title="__('Total Transport Price')"
    >
        {{ resource.total_transport_price }}
    </InformationRow>

    <div
        v-if="$can('edit-transport-order')"
        class="px-4 py-3 flex items-center justify-between"
    >
        <div class="text-[#6D6D73]">
            {{ __("Transport order") }}
        </div>

        <Link
            :href="route('transport-orders.edit', resource.id)"
            class="border border-[#008FE3] text-[#008FE3] px-3 py-0.5 rounded-md hover:opacity-60 active:scale-95 transition"
        >
            {{ __("Go to") }}
        </Link>
    </div>
</template>

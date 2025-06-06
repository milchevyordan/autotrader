<script setup lang="ts">
import { Link } from "@inertiajs/vue3";

import InformationRow from "@/components/html/InformationRow.vue";
import { ImportOrOriginType } from "@/enums/ImportOrOriginType";
import { SalesOrderStatus } from "@/enums/SalesOrderStatus";
import { SalesOrder } from "@/types";
import {
    booleanRepresentation,
    findEnumKeyByValue,
    replaceEnumUnderscores,
} from "@/utils";

defineProps<{
    resource: SalesOrder;
}>();
</script>

<template>
    <div class="p-4 border-b border-[#E9E7E7] font-bold">
        {{ __("Sales order") }} #{{ resource.id }}
    </div>

    <InformationRow :title="__('Status')">
        {{
            replaceEnumUnderscores(
                findEnumKeyByValue(SalesOrderStatus, resource.status)
            )
        }}
    </InformationRow>

    <InformationRow v-if="resource.customer_company" :title="__('Customer')">
        {{ resource.customer_company.name }}
    </InformationRow>

    <InformationRow
        v-if="resource.customer"
        :title="__('Customer contact person')"
    >
        {{ resource.customer.full_name }}
    </InformationRow>

    <InformationRow v-if="resource.seller" :title="__('Sales Person')">
        {{ resource.seller.full_name }}
    </InformationRow>

    <InformationRow v-if="resource.type_of_sale" :title="__('Type of sale')">
        {{
            replaceEnumUnderscores(
                findEnumKeyByValue(ImportOrOriginType, resource.type_of_sale)
            )
        }}
    </InformationRow>

    <InformationRow :title="__('Down payment')">
        {{ booleanRepresentation(resource.down_payment) }}
    </InformationRow>

    <InformationRow
        v-if="resource.down_payment"
        :title="__('Down payment amount')"
    >
        {{ resource.down_payment_amount }}
    </InformationRow>

    <div
        v-if="$can('edit-sales-order')"
        class="px-4 py-3 flex items-center justify-between"
    >
        <div class="text-[#6D6D73]">
            {{ __("Sales order") }}
        </div>

        <Link
            :href="route('sales-orders.edit', resource.id)"
            class="border border-[#008FE3] text-[#008FE3] px-3 py-0.5 rounded-md hover:opacity-60 active:scale-95 transition"
        >
            {{ __("Go to") }}
        </Link>
    </div>
</template>

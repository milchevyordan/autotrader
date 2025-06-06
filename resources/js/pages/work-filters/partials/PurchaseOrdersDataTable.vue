<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import { route } from "ziggy-js";

import Table from "@/data-table/Table.vue";
import { DataTable } from "@/data-table/types";
import { PurchaseOrderStatus } from "@/enums/PurchaseOrderStatus";
import IconPencilSquare from "@/icons/PencilSquare.vue";
import { Vehicle } from "@/types";
import {
    dateTimeToLocaleString,
    findEnumKeyByValue,
    replaceEnumUnderscores,
} from "@/utils.js";

defineProps<{
    dataTable: DataTable<Vehicle>;
}>();
</script>

<template>
    <Table
        :data-table="dataTable"
        :per-page-options="[5, 10, 15, 20, 50]"
        :global-search="true"
        :advanced-filters="false"
        :row-click-link="
            $can('edit-purchase-order') ? '/purchase-orders/?id/edit' : ''
        "
    >
        <template #cell(status)="{ value, item }">
            <div class="flex gap-1.5">
                {{
                    replaceEnumUnderscores(
                        findEnumKeyByValue(PurchaseOrderStatus, value)
                    )
                }}
            </div>
        </template>

        <template #cell(created_at)="{ value, item }">
            <div class="flex gap-1.5">
                {{ dateTimeToLocaleString(value) }}
            </div>
        </template>

        <template #cell(updated_at)="{ value, item }">
            <div class="flex gap-1.5">
                {{ dateTimeToLocaleString(value) }}
            </div>
        </template>

        <template #cell(action)="{ value, item }">
            <div class="flex gap-1.5">
                <Link
                    v-if="$can('edit-purchase-order')"
                    class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                    :href="route('purchase-orders.edit', item.id)"
                >
                    <IconPencilSquare classes="w-4 h-4 text-[#909090]" />
                </Link>
            </div>
        </template>
    </Table>
</template>

<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import { route } from "ziggy-js";

import Table from "@/data-table/Table.vue";
import { DataTable } from "@/data-table/types";
import IconDocument from "@/icons/Document.vue";
import IconPencilSquare from "@/icons/PencilSquare.vue";
import { ServiceVehicle } from "@/types";
import { dateTimeToLocaleString } from "@/utils";

defineProps<{
    dataTable: DataTable<ServiceVehicle>;
}>();
</script>

<template>
    <Table
        :data-table="dataTable"
        :per-page-options="[5, 10, 15, 20, 50]"
        :global-search="true"
        :row-click-link="
            $can('edit-service-vehicle') ? '/service-vehicles/?id/edit' : ''
        "
    >
        <template #cell(vehicleModel.name)="{ value, item }">
            {{ item.vehicle_model?.name }}
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
                    v-if="$can('edit-service-vehicle')"
                    class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                    :href="route('service-vehicles.edit', item.id)"
                >
                    <IconPencilSquare classes="w-4 h-4 text-[#909090]" />
                </Link>

                <Link
                    v-if="$can('view-workflow') && item.workflow"
                    :href="route('workflows.show', item.workflow.id)"
                    class="border border-[#008FE3] bg-[#008FE3] text-white rounded-md p-1 active:scale-90 transition"
                >
                    <IconDocument classes="w-4 h-4" />
                </Link>
            </div>
        </template>
    </Table>
</template>

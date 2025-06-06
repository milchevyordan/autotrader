<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import { route } from "ziggy-js";

import AdvancedFilters from "@/data-table/components/AdvancedFilters.vue";
import Table from "@/data-table/Table.vue";
import { FilterValues } from "@/data-table/types";
import { DataTable } from "@/data-table/types";
import IconDocument from "@/icons/Document.vue";
import IconPencilSquare from "@/icons/PencilSquare.vue";
import { Vehicle } from "@/types";
import { dateTimeToLocaleString } from "@/utils";

defineProps<{
    dataTable: DataTable<Vehicle>;
}>();

const filterValues: FilterValues = {
    filter: {
        columns: {
            maxkw: "",
            minkw: "",
            maxhp: "",
            minhp: "",
            make: "",
            engine: "",
        },
    },
};
</script>

<template>
    <Table
        :data-table="dataTable"
        :per-page-options="[5, 10, 15, 20, 50]"
        :global-search="true"
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
                    v-if="$can('edit-vehicle')"
                    class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                    :href="route('vehicles.edit', item.id)"
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

        <!-- Example template slot with join  -->
        <template #advancedFilters>
            <AdvancedFilters :filter-values="filterValues">
                <div class="grid grid-cols-2 p-2 items-center px-4">
                    <div class="font-medium text-[15px]">
                        {{ __("Kw") }}
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <input
                            v-model="filterValues.filter.columns.minkw"
                            type="text"
                            placeholder="Min"
                            class="border border-gray-200 text-gray-900 text-sm rounded-md focus:outline-none focus:ring-0 focus:border-gray-300 block w-full py-2 px-2.5 placeholder-gray-400 peer transition hover:bg-gray-50 focus:bg-gray-50 disabled:bg-slate-100"
                        />

                        <input
                            v-model="filterValues.filter.columns.maxkw"
                            type="text"
                            placeholder="Max"
                            class="border border-gray-200 text-gray-900 text-sm rounded-md focus:outline-none focus:ring-0 focus:border-gray-300 block w-full py-2 px-2.5 placeholder-gray-400 peer transition hover:bg-gray-50 focus:bg-gray-50 disabled:bg-slate-100"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-2 p-2 items-center px-4">
                    <div class="font-medium text-[15px]">
                        {{ __("Hp") }}
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <input
                            v-model="filterValues.filter.columns.minhp"
                            type="text"
                            placeholder="Min"
                            class="border border-gray-200 text-gray-900 text-sm rounded-md focus:outline-none focus:ring-0 focus:border-gray-300 block w-full py-2 px-2.5 placeholder-gray-400 peer transition hover:bg-gray-50 focus:bg-gray-50 disabled:bg-slate-100"
                        />

                        <input
                            v-model="filterValues.filter.columns.maxhp"
                            type="text"
                            placeholder="Max"
                            class="border border-gray-200 text-gray-900 text-sm rounded-md focus:outline-none focus:ring-0 focus:border-gray-300 block w-full py-2 px-2.5 placeholder-gray-400 peer transition hover:bg-gray-50 focus:bg-gray-50 disabled:bg-slate-100"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-2 p-2 items-center px-4">
                    <div class="font-medium text-[15px]">
                        {{ __("Make") }}
                    </div>

                    <input
                        v-model="filterValues.filter.columns.make"
                        type="text"
                        :placeholder="__('Make')"
                        class="border border-gray-200 text-gray-900 text-sm rounded-md focus:outline-none focus:ring-0 focus:border-gray-300 block w-full py-2 px-2.5 placeholder-gray-400 peer transition hover:bg-gray-50 focus:bg-gray-50 disabled:bg-slate-100"
                    />
                </div>

                <div class="grid grid-cols-2 p-2 items-center px-4">
                    <div class="font-medium text-[15px]">
                        {{ __("Model") }}
                    </div>

                    <input
                        v-model="filterValues.filter.columns.model"
                        type="text"
                        :placeholder="__('Model')"
                        class="border border-gray-200 text-gray-900 text-sm rounded-md focus:outline-none focus:ring-0 focus:border-gray-300 block w-full py-2 px-2.5 placeholder-gray-400 peer transition hover:bg-gray-50 focus:bg-gray-50 disabled:bg-slate-100"
                    />
                </div>

                <div class="grid grid-cols-2 p-2 items-center px-4">
                    <div class="font-medium text-[15px]">
                        {{ __("Engine") }}
                    </div>

                    <input
                        v-model="filterValues.filter.columns.engine"
                        type="text"
                        :placeholder="__('Engine')"
                        class="border border-gray-200 text-gray-900 text-sm rounded-md focus:outline-none focus:ring-0 focus:border-gray-300 block w-full py-2 px-2.5 placeholder-gray-400 peer transition hover:bg-gray-50 focus:bg-gray-50 disabled:bg-slate-100"
                    />
                </div>
            </AdvancedFilters>
        </template>
    </Table>
</template>

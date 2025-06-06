<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import { route } from "ziggy-js";

import WeekRangePicker from "@/components/html/WeekRangePicker.vue";
import Table from "@/data-table/Table.vue";
import { DataTable } from "@/data-table/types";
import { QuoteStatus } from "@/enums/QuoteStatus";
import IconPencilSquare from "@/icons/PencilSquare.vue";
import { Quote } from "@/types";
import {
    dateTimeToLocaleString,
    findEnumKeyByValue,
    replaceEnumUnderscores,
} from "@/utils.js";

defineProps<{
    dataTable: DataTable<Quote>;
}>();
</script>

<template>
    <Table
        :data-table="dataTable"
        :per-page-options="[5, 10, 15, 20, 50]"
        :global-search="true"
        :advanced-filters="false"
        :row-click-link="$can('edit-quote') ? '/quotes/?id/edit' : ''"
    >
        <template #cell(status)="{ value, item }">
            <div class="flex gap-1.5">
                {{
                    replaceEnumUnderscores(
                        findEnumKeyByValue(QuoteStatus, value)
                    )
                }}
            </div>
        </template>

        <template #cell(delivery_week)="{ value, item }">
            <div class="flex gap-1.5 min-w-[270px]">
                <WeekRangePicker
                    :model-value="value"
                    name="delivery_week"
                    disabled
                />
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
                    v-if="$can('edit-quote')"
                    class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                    :href="route('quotes.edit', item.id)"
                >
                    <IconPencilSquare classes="w-4 h-4 text-[#909090]" />
                </Link>
            </div>
        </template>
    </Table>
</template>

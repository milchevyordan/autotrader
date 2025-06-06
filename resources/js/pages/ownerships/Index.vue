<script setup lang="ts">
import { Head, Link } from "@inertiajs/vue3";

import Header from "@/components/Header.vue";
import Table from "@/data-table/Table.vue";
import { DataTable } from "@/data-table/types";
import { OwnershipStatus } from "@/enums/OwnershipStatus";
import IconEye from "@/icons/Eye.vue";
import AppLayout from "@/layouts/AppLayout.vue";
import { Ownership } from "@/types";
import {classBasename, dateTimeToLocaleString, findEnumKeyByValue} from "@/utils.js";

defineProps<{
    dataTable: DataTable<Ownership>;
    pendingOwnershipIds: Array<number>;
}>();
</script>

<template>
    <Head :title="__('Ownerships')" />

    <AppLayout>
        <Header :text="__('Ownerships')" />

        <Table
            :data-table="dataTable"
            :per-page-options="[5, 10, 15, 20, 50]"
            :global-search="true"
            :advanced-filters="false"
            :selected-row-indexes="pendingOwnershipIds"
            :selected-row-column="'id'"
        >
            <template #cell(status)="{ value, item }">
                <div class="flex gap-1.5">
                    {{ findEnumKeyByValue(OwnershipStatus, value) }}
                </div>
            </template>

            <template #cell(resource)="{ value, item }">
                <div class="flex gap-1.5">
                    {{ classBasename(item.ownable_type) }} #{{
                        item.ownable_id
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
                        v-if="$can('view-ownership')"
                        :href="route('ownerships.show', item)"
                        class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                    >
                        <IconEye classes="w-4 h-4 text-[#909090]" />
                    </Link>
                </div>
            </template>
        </Table>
    </AppLayout>
</template>

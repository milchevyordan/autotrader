<script setup lang="ts">
import { Head, Link } from "@inertiajs/vue3";

import Header from "@/components/Header.vue";
import Table from "@/data-table/Table.vue";
import { DataTable } from "@/data-table/types";
import IconPencilSquare from "@/icons/PencilSquare.vue";
import AppLayout from "@/layouts/AppLayout.vue";
import { Company } from "@/types";
import { ExportType } from "@/data-table/enums/ExportType";

defineProps<{
    dataTable: DataTable<Company>;
}>();
</script>

<template>
    <Head :title="__('Companies')" />

    <AppLayout>
        <Header :text="__('Companies')" />

        <Table
            :data-table="dataTable"
            :per-page-options="[5, 10, 15, 20, 50]"
            :global-search="true"
            :advanced-filters="false"
        >
            <template #additionalContent>
                <div class="w-full flex gap-2">
                    <Link
                        v-if="$can('create-base-company')"
                        class="ml-auto w-full md:w-auto border border-[#E9E7E7] rounded-md px-5 py-1.5 active:scale-95 transition hover:bg-gray-50"
                        :href="route('companies.create')"
                    >
                        {{ __("Create") }} {{ __("Base Company") }}
                    </Link>
                </div>
            </template>

            <template #cell(action)="{ value, item }">
                <div class="flex gap-1.5">
                    <Link
                        v-if="$can('edit-base-company')"
                        :title="__('Edit base company')"
                        class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                        :href="route('companies.edit', item.id)"
                    >
                        <IconPencilSquare classes="w-4 h-4 text-[#909090]" />
                    </Link>
                </div>
            </template>
        </Table>
    </AppLayout>
</template>

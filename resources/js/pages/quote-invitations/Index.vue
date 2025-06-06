<script setup lang="ts">
import { Head, Link } from "@inertiajs/vue3";

import Header from "@/components/Header.vue";
import {ExportType} from "@/data-table/enums/ExportType";
import Table from "@/data-table/Table.vue";
import { DataTable } from "@/data-table/types";
import { QuoteInvitationStatus } from "@/enums/QuoteInvitationStatus";
import IconEye from "@/icons/Eye.vue";
import AppLayout from "@/layouts/AppLayout.vue";
import { QuoteInvitation } from "@/types";
import { dateTimeToLocaleString, findEnumKeyByValue } from "@/utils.js";

defineProps<{
    dataTable: DataTable<QuoteInvitation>;
}>();
</script>

<template>
    <Head :title="__('Quote invitation')" />

    <AppLayout>
        <Header :text="__('Quote invitations')" />

        <Table
            :data-table="dataTable"
            :per-page-options="[5, 10, 15, 20, 50]"
            :global-search="true"
            :advanced-filters="false"
            :row-click-link="
                $can('view-quote-invitation') ? '/quote-invitations/?id' : ''
            "
            :export-types="[ExportType.Csv]"
        >
            <template #cell(status)="{ value, item }">
                <div class="flex gap-1.5">
                    {{ findEnumKeyByValue(QuoteInvitationStatus, value) }}
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
                        v-if="$can('view-quote-invitation')"
                        class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                        :href="route('quote-invitations.show', item.id)"
                    >
                        <IconEye classes="w-4 h-4 text-[#909090]" />
                    </Link>
                </div>
            </template>
        </Table>
    </AppLayout>
</template>

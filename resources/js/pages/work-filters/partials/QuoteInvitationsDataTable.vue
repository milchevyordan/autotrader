<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import { route } from "ziggy-js";

import Table from "@/data-table/Table.vue";
import { DataTable } from "@/data-table/types";
import { QuoteInvitationStatus } from "@/enums/QuoteInvitationStatus";
import IconPencilSquare from "@/icons/PencilSquare.vue";
import { QuoteInvitation } from "@/types";
import { dateTimeToLocaleString, findEnumKeyByValue } from "@/utils";

defineProps<{
    dataTable: DataTable<QuoteInvitation>;
}>();
</script>

<template>
    <Table
        :data-table="dataTable"
        :per-page-options="[5, 10, 15, 20, 50]"
        :global-search="true"
        :advanced-filters="false"
        :row-click-link="
            $can('view-quote-invitation') ? '/quote-invitations/?id' : ''
        "
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
                    v-if="$can('edit-quote-invitation')"
                    class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                    :href="route('quote-invitations.edit', item.id)"
                >
                    <IconPencilSquare classes="w-4 h-4 text-[#909090]" />
                </Link>
            </div>
        </template>
    </Table>
</template>

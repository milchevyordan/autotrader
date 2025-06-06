<script setup lang="ts">
import { Head, Link } from "@inertiajs/vue3";

import Header from "@/components/Header.vue";
import Table from "@/data-table/Table.vue";
import { DataTable } from "@/data-table/types";
import IconDocument from "@/icons/Document.vue";
import IconPencilSquare from "@/icons/PencilSquare.vue";
import AppLayout from "@/layouts/AppLayout.vue";
import { User } from "@/types";
import { dateTimeToLocaleString } from "@/utils";

defineProps<{
    dataTable: DataTable<User>;
}>();
</script>

<template>
    <Head :title="__('User')" />

    <AppLayout>
        <Header :text="__('Users')" />

        <Table
            :data-table="dataTable"
            :per-page-options="[5, 10, 15, 20, 50]"
            :global-search="true"
            :advanced-filters="false"
        >
            <template #additionalContent>
                <div class="w-full flex gap-2">
                    <Link
                        v-if="$can('create-base-user')"
                        class="w-full md:w-auto border border-[#E9E7E7] rounded-md px-5 py-1.5 active:scale-95 transition hover:bg-gray-50"
                        :href="route('users.create')"
                    >
                        {{ __("Create") }} {{ __("User") }}
                    </Link>
                </div>
            </template>

            <template #cell(company.name)="{ value, item }">
                {{ item.company?.name }}
            </template>

            <template #cell(roles.name)="{ value, item }">
                <div v-for="(role, index) in item.roles" :key="index">
                    {{ role.name }}
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
                        v-if="$can('edit-base-user')"
                        class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                        :title="__('Edit base user')"
                        :href="route('users.edit', item.id)"
                    >
                        <IconPencilSquare classes="w-4 h-4 text-[#909090]" />
                    </Link>

                    <Link
                        v-if="$can('view-base-user')"
                        class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                        :title="__('View base user')"
                        :href="route('users.show', item.id)"
                    >
                        <IconDocument classes="w-4 h-4 text-[#909090]" />
                    </Link>
                </div>
            </template>
        </Table>
    </AppLayout>
</template>

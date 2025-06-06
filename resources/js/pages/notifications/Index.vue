<script setup lang="ts">
import { Head, useForm } from "@inertiajs/vue3";
import { computed } from "vue";

import Header from "@/components/Header.vue";
import Table from "@/data-table/Table.vue";
import { DataTable } from "@/data-table/types";
import Check from "@/icons/Check.vue";
import AppLayout from "@/layouts/AppLayout.vue";
import { FormMethod, Notification } from "@/types";
import {classBasename, dateTimeToLocaleString} from "@/utils";
import { validate } from "@/validations";

const props = defineProps<{
    dataTable: DataTable<Notification>;
}>();

const updateForm = useForm<{
    id: string;
}>({
    id: null!,
});

const rules = {
    id: {
        required: true,
        type: "string",
    },
};

const markRead = (itemId: string) => {
    updateForm.id = itemId;
    validate(updateForm, rules);

    updateForm.put(route("notifications.read", updateForm.id), {
        preserveScroll: true,
        onSuccess: () => {
            updateForm.reset();
        },
        onError: () => {},
    });
};

const markAllReadForm = useForm<FormMethod>({
    _method: "post",
});

const markAllRead = () => {
    markAllReadForm.post(route("notifications.read-all"), {
        preserveScroll: true,
        onSuccess: () => {},
        onError: () => {},
    });
};

const notReadYet = computed(() =>
    props.dataTable.data
        .filter((item: Notification) => item.read_at == null)
        .map((item) => item.id)
);
</script>

<template>
    <Head :title="__('Notification')" />

    <AppLayout>
        <Header :text="__('Notifications')" />

        <Table
            :data-table="dataTable"
            :per-page-options="[5, 10, 15, 20, 50]"
            :global-search="true"
            :selected-row-indexes="notReadYet"
            :selected-row-column="'id'"
        >
            <template #additionalContent>
                <div class="w-full flex gap-2">
                    <button
                        class="w-full md:w-auto border border-[#E9E7E7] rounded-md px-5 py-1.5 active:scale-95 transition hover:bg-gray-50"
                        @click="markAllRead"
                    >
                        {{ __("Mark all as read") }}
                    </button>
                </div>
            </template>

            <template #cell(data)="{ value, item }">
                {{ JSON.parse(item.data).message }}
            </template>

            <template #cell(notifiable_type)="{ value, item }">
                {{ classBasename(value) }}
            </template>

            <template #cell(created_at)="{ value, item }">
                <div class="flex gap-1.5">
                    {{ dateTimeToLocaleString(value) }}
                </div>
            </template>

            <template #cell(read_at)="{ value, item }">
                <div class="flex gap-1.5">
                    <button
                        v-if="!item.read_at"
                        :title="__('Mark read')"
                        :disabled="updateForm.processing"
                        class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                        @click="markRead(item.id)"
                    >
                        <Check classes="w-4 h-4 text-slate-600" />
                    </button>
                    <div v-else>
                        {{ dateTimeToLocaleString(value) }}
                    </div>
                </div>
            </template>
        </Table>
    </AppLayout>
</template>

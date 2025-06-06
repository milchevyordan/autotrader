<script setup lang="ts">
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref } from "vue";

import Header from "@/components/Header.vue";
import ModalSaveButtons from "@/components/html/ModalSaveButtons.vue";
import Modal from "@/components/Modal.vue";
import Table from "@/data-table/Table.vue";
import { DataTable } from "@/data-table/types";
import { WorkOrderStatus } from "@/enums/WorkOrderStatus";
import { WorkOrderType } from "@/enums/WorkOrderType";
import IconPencilSquare from "@/icons/PencilSquare.vue";
import IconTrash from "@/icons/Trash.vue";
import AppLayout from "@/layouts/AppLayout.vue";
import { baseFormRules } from "@/rules/base-form-rules";
import { WorkOrder } from "@/types";
import {
    dateTimeToLocaleString,
    findEnumKeyByValue,
    replaceEnumUnderscores,
} from "@/utils.js";
import { validate } from "@/validations";
import { ExportType } from "@/data-table/enums/ExportType";

defineProps<{
    dataTable: DataTable<WorkOrder>;
}>();

const showModal = ref(false);

const closeModal = () => {
    showModal.value = false;
    deleteForm.reset();
};

const deleteForm = useForm<{
    id: null | number;
}>({
    id: 0,
});

const handleDelete = () => {
    validate(deleteForm, baseFormRules);

    deleteForm.delete(route("work-orders.destroy", deleteForm.id as number), {
        preserveScroll: true,
    });
    closeModal();
};
</script>

<template>
    <Head :title="__('Work Order')" />

    <AppLayout>
        <Header :text="__('Work Orders')" />

        <Table
            :data-table="dataTable"
            :per-page-options="[5, 10, 15, 20, 50]"
            :global-search="true"
            :advanced-filters="false"
            :row-click-link="
                $can('edit-work-order') ? '/work-orders/?id/edit' : ''
            "
            :show-trashed="true"
            :export-types="[ExportType.Csv]"
        >
            <template #additionalContent>
                <div class="w-full flex gap-2">
                    <Link
                        v-if="$can('create-work-order')"
                        class="w-full md:w-auto border border-[#E9E7E7] rounded-md px-5 py-1.5 active:scale-95 transition hover:bg-gray-50"
                        :href="route('work-orders.create')"
                    >
                        {{ __("Create") }} {{ __("Work Order") }}
                    </Link>
                </div>
            </template>

            <template #cell(type)="{ value, item }">
                <div class="flex gap-1.5">
                    {{
                        replaceEnumUnderscores(
                            findEnumKeyByValue(WorkOrderType, value)
                        )
                    }}
                </div>
            </template>

            <template #cell(status)="{ value, item }">
                <div class="flex gap-1.5">
                    {{
                        replaceEnumUnderscores(
                            findEnumKeyByValue(WorkOrderStatus, value)
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
                        v-if="$can('edit-work-order')"
                        class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                        :href="route('work-orders.edit', item.id)"
                    >
                        <IconPencilSquare classes="w-4 h-4 text-[#909090]" />
                    </Link>

                    <button
                        v-if="$can('delete-work-order')"
                        class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                        :title="__('Delete')"
                        @click="
                            showModal = true;
                            deleteForm.id = item.id;
                        "
                    >
                        <IconTrash classes="w-4 h-4 text-[#909090]" />
                    </button>
                </div>
            </template>
        </Table>

        <Modal :show="showModal" @close="closeModal">
            <div
                class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium"
            >
                {{ __("Delete Work Order #") }} + "{{ deleteForm.id ?? "" }}" ?
            </div>

            <ModalSaveButtons
                :processing="deleteForm.processing"
                :save-text="__('Delete')"
                @cancel="closeModal"
                @save="handleDelete"
            />
        </Modal>
    </AppLayout>
</template>

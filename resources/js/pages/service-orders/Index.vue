<script setup lang="ts">
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref } from "vue";

import Header from "@/components/Header.vue";
import ModalSaveButtons from "@/components/html/ModalSaveButtons.vue";
import Modal from "@/components/Modal.vue";
import Table from "@/data-table/Table.vue";
import { DataTable } from "@/data-table/types";
import { ServiceOrderStatus } from "@/enums/ServiceOrderStatus";
import IconPencilSquare from "@/icons/PencilSquare.vue";
import IconTrash from "@/icons/Trash.vue";
import AppLayout from "@/layouts/AppLayout.vue";
import { baseFormRules } from "@/rules/base-form-rules";
import { PurchaseOrder } from "@/types";
import {
    dateTimeToLocaleString,
    findEnumKeyByValue,
    replaceEnumUnderscores,
} from "@/utils.js";
import { validate } from "@/validations";
import { ExportType } from "@/data-table/enums/ExportType";

defineProps<{
    dataTable: DataTable<PurchaseOrder>;
}>();

const showDeleteModal = ref(false);

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    deleteForm.reset();
};

const deleteForm = useForm<{
    id: null | number;
}>({
    id: 0,
});

const handleDelete = () => {
    validate(deleteForm, baseFormRules);

    deleteForm.delete(
        route("service-orders.destroy", deleteForm.id as number),
        {
            preserveScroll: true,
        }
    );
    closeDeleteModal();
};
</script>

<template>
    <Head :title="__('Service Order')" />

    <AppLayout>
        <Header :text="__('Service Orders')" />

        <Table
            :data-table="dataTable"
            :per-page-options="[5, 10, 15, 20, 50]"
            :global-search="true"
            :advanced-filters="false"
            :row-click-link="
                $can('edit-service-order') ? '/service-orders/?id/edit' : ''
            "
            :show-trashed="true"
            :export-types="[ExportType.Csv]"
        >
            <template #additionalContent>
                <div class="w-full flex gap-2">
                    <Link
                        v-if="$can('create-service-order')"
                        class="w-full md:w-auto border border-[#E9E7E7] rounded-md px-5 py-1.5 active:scale-95 transition hover:bg-gray-50"
                        :href="route('service-orders.create')"
                    >
                        {{ __("Create") }} {{ __("Service Order") }}
                    </Link>
                </div>
            </template>

            <template #cell(status)="{ value, item }">
                <div class="flex gap-1.5">
                    {{
                        replaceEnumUnderscores(
                            findEnumKeyByValue(ServiceOrderStatus, value)
                        )
                    }}
                </div>
            </template>

            <template #cell(creator.name)="{ value, item }">
                <div class="flex gap-1.5">
                    {{ item.creator.name }}
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
                        v-if="$can('edit-service-order')"
                        class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                        :href="route('service-orders.edit', item.id)"
                    >
                        <IconPencilSquare classes="w-4 h-4 text-[#909090]" />
                    </Link>

                    <button
                        v-if="$can('delete-service-order')"
                        class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                        :title="__('Delete')"
                        @click="
                            showDeleteModal = true;
                            deleteForm.id = item.id;
                        "
                    >
                        <IconTrash classes="w-4 h-4 text-[#909090]" />
                    </button>
                </div>
            </template>
        </Table>

        <Modal :show="showDeleteModal" @close="closeDeleteModal">
            <div
                class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium break-words"
            >
                {{
                    deleteForm.id
                        ? __("Delete Service Order #") + deleteForm.id
                        : ""
                }}
                ?
            </div>

            <ModalSaveButtons
                :processing="deleteForm.processing"
                :save-text="__('Delete')"
                @cancel="closeDeleteModal"
                @save="handleDelete"
            />
        </Modal>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref } from "vue";

import Header from "@/components/Header.vue";
import IndexStatus from "@/components/html/IndexStatus.vue";
import ModalSaveButtons from "@/components/html/ModalSaveButtons.vue";
import Modal from "@/components/Modal.vue";
import Table from "@/data-table/Table.vue";
import { DataTable } from "@/data-table/types";
import { ImportOrOriginType } from "@/enums/ImportOrOriginType";
import { SalesOrderStatus } from "@/enums/SalesOrderStatus";
import IconPencilSquare from "@/icons/PencilSquare.vue";
import IconTrash from "@/icons/Trash.vue";
import AppLayout from "@/layouts/AppLayout.vue";
import { baseFormRules } from "@/rules/base-form-rules";
import { SalesOrder } from "@/types";
import {
    dateTimeToLocaleString,
    findEnumKeyByValue,
    replaceEnumUnderscores,
} from "@/utils.js";
import { validate } from "@/validations";
import { ExportType } from "@/data-table/enums/ExportType";

defineProps<{
    dataTable: DataTable<SalesOrder>;
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

    deleteForm.delete(route("sales-orders.destroy", deleteForm.id as number), {
        preserveScroll: true,
    });
    closeDeleteModal();
};
</script>

<template>
    <Head :title="__('Sales order')" />

    <AppLayout>
        <Header :text="__('Sales orders')" />

        <Table
            :data-table="dataTable"
            :per-page-options="[5, 10, 15, 20, 50]"
            :global-search="true"
            :advanced-filters="false"
            :show-trashed="true"
            :export-types="[ExportType.Csv]"
        >
            <template #additionalContent>
                <div class="w-full flex gap-2">
                    <Link
                        v-if="$can('create-sales-order')"
                        class="w-full md:w-auto border border-[#E9E7E7] rounded-md px-5 py-1.5 active:scale-95 transition hover:bg-gray-50"
                        :href="route('sales-orders.create')"
                    >
                        {{ __("Create") }} {{ __("Sales Order") }}
                    </Link>
                </div>
            </template>

            <template #cell(status)="{ value, item }">
                <IndexStatus
                    :item="item"
                    :status-enum="SalesOrderStatus"
                    module="sales-order"
                    :text="__('Sales order')"
                />
            </template>

            <template #cell(type_of_sale)="{ value, item }">
                {{
                    replaceEnumUnderscores(
                        findEnumKeyByValue(ImportOrOriginType, value)
                    )
                }}
            </template>

            <template #cell(seller.full_name)="{ value, item }">
                {{ item.seller?.full_name }}
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
                        v-if="$can('edit-sales-order')"
                        class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                        :href="route('sales-orders.edit', item.id)"
                    >
                        <IconPencilSquare classes="w-4 h-4 text-[#909090]" />
                    </Link>

                    <button
                        v-if="$can('delete-sales-order')"
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
                {{ __("Delete Sales order #") + deleteForm?.id }} ?
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

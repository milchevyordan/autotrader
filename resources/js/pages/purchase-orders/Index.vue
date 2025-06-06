<script setup lang="ts">
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref } from "vue";

import Header from "@/components/Header.vue";
import IndexStatus from "@/components/html/IndexStatus.vue";
import ModalSaveButtons from "@/components/html/ModalSaveButtons.vue";
import Modal from "@/components/Modal.vue";
import Table from "@/data-table/Table.vue";
import { DataTable } from "@/data-table/types";
import { PurchaseOrderStatus } from "@/enums/PurchaseOrderStatus";
import IconPencilSquare from "@/icons/PencilSquare.vue";
import IconTrash from "@/icons/Trash.vue";
import AppLayout from "@/layouts/AppLayout.vue";
import { baseFormRules } from "@/rules/base-form-rules";
import { PurchaseOrder } from "@/types";
import { dateTimeToLocaleString } from "@/utils.js";
import { validate } from "@/validations";
import { ExportType } from "@/data-table/enums/ExportType";

defineProps<{
    dataTable: DataTable<PurchaseOrder>;
}>();

const showModal = ref(false);

const closeDeleteModal = () => {
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

    deleteForm.delete(
        route("purchase-orders.destroy", deleteForm.id as number),
        {
            preserveScroll: true,
        }
    );
    closeDeleteModal();
};
</script>

<template>
    <Head :title="__('Purchase Order')" />

    <AppLayout>
        <Header :text="__('Purchase Orders')" />

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
                        v-if="$can('create-purchase-order')"
                        class="w-full md:w-auto border border-[#E9E7E7] rounded-md px-5 py-1.5 active:scale-95 transition hover:bg-gray-50"
                        :href="route('purchase-orders.create')"
                    >
                        {{ __("Create") }} {{ __("Purchase Order") }}
                    </Link>
                </div>
            </template>

            <template #cell(status)="{ value, item }">
                <IndexStatus
                    :item="item"
                    :status-enum="PurchaseOrderStatus"
                    module="purchase-order"
                    :text="__('Purchase order')"
                />
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
                        v-if="$can('edit-purchase-order')"
                        class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                        :href="route('purchase-orders.edit', item.id)"
                    >
                        <IconPencilSquare classes="w-4 h-4 text-[#909090]" />
                    </Link>

                    <button
                        v-if="$can('delete-purchase-order')"
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

        <Modal :show="showModal" @close="closeDeleteModal">
            <div
                class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium"
            >
                {{ __("Delete Purchase Order #") + deleteForm?.id }} ?
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

<script setup lang="ts">
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref } from "vue";

import Header from "@/components/Header.vue";
import IndexStatus from "@/components/html/IndexStatus.vue";
import ModalSaveButtons from "@/components/html/ModalSaveButtons.vue";
import Modal from "@/components/Modal.vue";
import {ExportType} from "@/data-table/enums/ExportType";
import Table from "@/data-table/Table.vue";
import { DataTable } from "@/data-table/types";
import { DocumentableType } from "@/enums/DocumentableType";
import { DocumentStatus } from "@/enums/DocumentStatus";
import { PaymentCondition } from "@/enums/PaymentCondition";
import IconPencilSquare from "@/icons/PencilSquare.vue";
import IconTrash from "@/icons/Trash.vue";
import AppLayout from "@/layouts/AppLayout.vue";
import { baseFormRules } from "@/rules/base-form-rules";
import { Document } from "@/types";
import {
    dateTimeToLocaleString,
    findEnumKeyByValue,
    replaceEnumUnderscores,
} from "@/utils.js";
import { validate } from "@/validations";

defineProps<{
    dataTable: DataTable<Document>;
}>();

const showDeleteModal = ref(false);

const deleteForm = useForm<{
    id: null | number;
}>({
    id: null,
});

const duplicateForm = useForm<{
    id: null | number;
}>({
    id: null,
});

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    deleteForm.reset();
};

const handleDelete = () => {
    validate(deleteForm, baseFormRules);

    deleteForm.delete(route("documents.destroy", deleteForm.id as number), {
        preserveScroll: true,
        onSuccess: () => {},
        onError: () => {},
    });

    closeDeleteModal();
};
</script>

<template>
    <Head :title="__('Invoice')" />

    <AppLayout>
        <Header :text="__('Invoices')" />

        <Table
            :data-table="dataTable"
            :per-page-options="[5, 10, 15, 20, 50]"
            :global-search="true"
            :advanced-filters="false"
            :row-click-link="$can('edit-document') ? '/documents/?id/edit' : ''"
            :show-trashed="true"
            :export-types="[ExportType.Csv]"
        >
            <template #additionalContent>
                <div class="w-full flex gap-2">
                    <Link
                        v-if="$can('create-document')"
                        class="w-full md:w-auto border border-[#E9E7E7] rounded-md px-5 py-1.5 active:scale-95 transition hover:bg-gray-50"
                        :href="route('documents.create')"
                    >
                        {{ __("Create") }} {{ __("Invoice") }}
                    </Link>
                </div>
            </template>

            <template #cell(status)="{ value, item }">
                <IndexStatus
                    :item="item"
                    :status-enum="DocumentStatus"
                    module="document"
                    :text="__('Document')"
                />
            </template>

            <template #cell(type)="{ value, item }">
                <div class="flex gap-1.5">
                    {{
                        replaceEnumUnderscores(
                            findEnumKeyByValue(DocumentStatus, value)
                        )
                    }}
                </div>
            </template>

            <template #cell(documentable_type)="{ value, item }">
                <div class="flex gap-1.5">
                    {{
                        replaceEnumUnderscores(
                            findEnumKeyByValue(DocumentableType, value)
                        )
                    }}
                </div>
            </template>

            <template #cell(payment_condition)="{ value, item }">
                <div class="flex gap-1.5">
                    {{
                        replaceEnumUnderscores(
                            findEnumKeyByValue(PaymentCondition, value)
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
                        v-if="$can('edit-document')"
                        :href="route('documents.edit', item.id)"
                        :title="__('Edit invoice')"
                        class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                    >
                        <IconPencilSquare classes="w-4 h-4 text-[#909090]" />
                    </Link>

                    <button
                        v-if="$can('delete-document')"
                        :title="__('Delete invoice')"
                        class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
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
                class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium"
            >
                {{ __("Delete Document #") + deleteForm?.id }} ?
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

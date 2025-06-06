<script setup lang="ts">
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref } from "vue";

import Header from "@/components/Header.vue";
import IndexStatus from "@/components/html/IndexStatus.vue";
import ModalSaveButtons from "@/components/html/ModalSaveButtons.vue";
import WeekRangePicker from "@/components/html/WeekRangePicker.vue";
import Modal from "@/components/Modal.vue";
import {ExportType} from "@/data-table/enums/ExportType";
import Table from "@/data-table/Table.vue";
import { DataTable } from "@/data-table/types";
import { QuoteStatus } from "@/enums/QuoteStatus";
import IconDocumentDuplicate from "@/icons/DocumentDuplicate.vue";
import IconPencilSquare from "@/icons/PencilSquare.vue";
import IconTrash from "@/icons/Trash.vue";
import AppLayout from "@/layouts/AppLayout.vue";
import { baseFormRules } from "@/rules/base-form-rules";
import { Quote } from "@/types";
import {
    dateTimeToLocaleString,
} from "@/utils.js";
import { validate } from "@/validations";

defineProps<{
    dataTable: DataTable<Quote>;
}>();

const showModal = ref(false);
const showDuplicateModal = ref(false);

const closeModal = () => {
    deleteForm.reset();
    showModal.value = false;
};

const closeDuplicateModal = () => {
    showDuplicateModal.value = false;
    duplicateForm.reset();
};

const deleteForm = useForm<{
    id: null | number;
}>({
    id: 0,
});

const duplicateForm = useForm<{
    id: null | number;
}>({
    id: 0,
});

const handleDelete = () => {
    validate(deleteForm, baseFormRules);

    deleteForm.delete(route("quotes.destroy", deleteForm.id as number), {
        preserveScroll: true,
    });
    closeModal();
};

const handleDuplicate = () => {
    validate(duplicateForm, baseFormRules);

    duplicateForm.post(route("quotes.duplicate", duplicateForm.id as number), {
        preserveScroll: true,
    });
    closeDuplicateModal();
};
</script>

<template>
    <Head :title="__('Quote')" />

    <AppLayout>
        <Header :text="__('Quotes')" />

        <Table
            :data-table="dataTable"
            :per-page-options="[5, 10, 15, 20, 50]"
            :global-search="true"
            :advanced-filters="false"
            :row-click-link="$can('edit-quote') ? '/quotes/?id/edit' : ''"
            :show-trashed="true"
            :export-types="[ExportType.Csv]"
        >
            <template #additionalContent>
                <div class="w-full flex gap-2">
                    <Link
                        v-if="$can('create-quote')"
                        class="w-full md:w-auto border border-[#E9E7E7] rounded-md px-5 py-1.5 active:scale-95 transition hover:bg-gray-50"
                        :href="route('quotes.create')"
                    >
                        {{ __("Create") }} {{ __("Quote") }}
                    </Link>
                </div>
            </template>

            <template #cell(status)="{ value, item }">
                <IndexStatus
                    :item="item"
                    :status-enum="QuoteStatus"
                    module="quote"
                    :text="__('Quote')"
                />
            </template>

            <template #cell(delivery_week)="{ value, item }">
                <div class="flex gap-1.5 min-w-[270px]">
                    <WeekRangePicker
                        :model-value="value"
                        name="delivery_week"
                        disabled
                    />
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
                        v-if="$can('edit-quote')"
                        class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                        :href="route('quotes.edit', item.id)"
                    >
                        <IconPencilSquare classes="w-4 h-4 text-[#909090]" />
                    </Link>

                    <button
                        v-if="$can('duplicate-quote')"
                        :title="__('Duplicate quote')"
                        class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                        @click="
                            showDuplicateModal = true;
                            duplicateForm.id = item.id;
                        "
                    >
                        <IconDocumentDuplicate
                            classes="w-4 h-4 text-[#909090]"
                        />
                    </button>

                    <button
                        v-if="$can('delete-quote')"
                        :title="__('Delete record')"
                        class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
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
                {{ __("Delete record #") + deleteForm?.id }} ?
            </div>

            <ModalSaveButtons
                :processing="deleteForm.processing"
                :save-text="__('Delete')"
                @cancel="closeModal"
                @save="handleDelete"
            />
        </Modal>

        <Modal :show="showDuplicateModal" @close="closeDuplicateModal">
            <div
                class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium"
            >
                {{ __("Duplicate quote") + "#" + duplicateForm?.id }} ?
            </div>

            <ModalSaveButtons
                :processing="duplicateForm.processing"
                :save-text="__('Duplicate')"
                @cancel="closeDuplicateModal"
                @save="handleDuplicate"
            />
        </Modal>
    </AppLayout>
</template>

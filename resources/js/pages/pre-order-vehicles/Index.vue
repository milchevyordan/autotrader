<script setup lang="ts">
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref } from "vue";

import Header from "@/components/Header.vue";
import ModalSaveButtons from "@/components/html/ModalSaveButtons.vue";
import Modal from "@/components/Modal.vue";
import {ExportType} from "@/data-table/enums/ExportType";
import Table from "@/data-table/Table.vue";
import { DataTable } from "@/data-table/types";
import IconPencilSquare from "@/icons/PencilSquare.vue";
import IconTrash from "@/icons/Trash.vue";
import AppLayout from "@/layouts/AppLayout.vue";
import { baseFormRules } from "@/rules/base-form-rules";
import { PreOrderVehicle } from "@/types";
import { dateTimeToLocaleString } from "@/utils";
import { validate } from "@/validations";

defineProps<{
    dataTable: DataTable<PreOrderVehicle>;
}>();

const showDeleteModal = ref(false);

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    deleteForm.reset();
};

const showDeleteForm = (id: number) => {
    showDeleteModal.value = true;
    deleteForm.id = id;
};

const deleteForm = useForm<{
    id: null | number;
}>({
    id: 0,
});

const handleDelete = () => {
    validate(deleteForm, baseFormRules);

    deleteForm.delete(
        route("pre-order-vehicles.destroy", deleteForm.id as number),
        {
            preserveScroll: true,
        }
    );
    closeDeleteModal();
};
</script>

<template>
    <Head :title="__('Pre Order Vehicle')" />

    <AppLayout>
        <Header :text="__('Pre Order Vehicles')" />

        <Table
            :data-table="dataTable"
            :per-page-options="[5, 10, 15, 20, 50]"
            :global-search="true"
            :show-trashed="true"
            :export-types="[ExportType.Csv]"
        >
            <template #additionalContent>
                <div class="w-full flex gap-2">
                    <Link
                        v-if="$can('create-pre-order-vehicle')"
                        class="w-full md:w-auto border border-[#E9E7E7] rounded-md px-5 py-1.5 active:scale-95 transition hover:bg-gray-50"
                        :href="route('pre-order-vehicles.create')"
                    >
                        {{ __("Create") }} {{ __("Pre Order Vehicle") }}
                    </Link>
                </div>
            </template>

            <template #cell(vehicleModel.name)="{ value, item }">
                {{ item.vehicle_model?.name }}
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
                        v-if="$can('edit-pre-order-vehicle')"
                        class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                        :href="route('pre-order-vehicles.edit', item.id)"
                    >
                        <IconPencilSquare classes="w-4 h-4 text-[#909090]" />
                    </Link>

                    <button
                        v-if="$can('delete-pre-order-vehicle')"
                        :title="__('Delete vehicle')"
                        class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                        @click="showDeleteForm(item.id)"
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
                {{ __("Delete Pre Order Vehicle #") + deleteForm?.id }} ?
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

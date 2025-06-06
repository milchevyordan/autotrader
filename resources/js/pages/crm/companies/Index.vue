<script setup lang="ts">
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref } from "vue";

import Header from "@/components/Header.vue";
import ModalSaveButtons from "@/components/html/ModalSaveButtons.vue";
import Modal from "@/components/Modal.vue";
import Table from "@/data-table/Table.vue";
import { DataTable } from "@/data-table/types";
import { CompanyType } from "@/enums/CompanyType";
import { Country } from "@/enums/Country";
import IconPencilSquare from "@/icons/PencilSquare.vue";
import IconTrash from "@/icons/Trash.vue";
import AppLayout from "@/layouts/AppLayout.vue";
import { baseFormRules } from "@/rules/base-form-rules";
import { Company } from "@/types";
import { findEnumKeyByValue, replaceEnumUnderscores } from "@/utils";
import { validate } from "@/validations";

defineProps<{
    dataTable: DataTable<Company>;
}>();

const showDeleteModal = ref(false);

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    deleteForm.reset();
};

const showDeleteForm = (item: Company) => {
    deleteForm.id = item.id;
    deleteForm.name = item.name;
    showDeleteModal.value = true;
};

const deleteForm = useForm<{
    id: number;
    name: string;
}>({
    id: null!,
    name: null!,
});

const handleDelete = () => {
    validate(deleteForm, baseFormRules);

    deleteForm.delete(route("crm.companies.destroy", deleteForm.id as number), {
        preserveScroll: true,
    });
    closeDeleteModal();
};
</script>

<template>
    <Head :title="__('Company')" />

    <AppLayout>
        <Header :text="__('Companies')" />

        <Table
            :data-table="dataTable"
            :per-page-options="[5, 10, 15, 20, 50]"
            :global-search="true"
            :advanced-filters="false"
            :show-trashed="true"
        >
            <template #additionalContent>
                <div class="w-full flex gap-2">
                    <Link
                        v-if="$can('create-crm-company')"
                        class="w-full md:w-auto border border-[#E9E7E7] rounded-md px-5 py-1.5 active:scale-95 transition hover:bg-gray-50"
                        :href="route('crm.companies.create')"
                    >
                        {{ __("Create") }} {{ __("Company") }}
                    </Link>
                </div>
            </template>

            <template #cell(mainUser.full_name)="{ value, item }">
                {{ item.main_user?.full_name }}
            </template>

            <template #cell(country)="{ value, item }">
                <div class="flex gap-1.5">
                    {{
                        replaceEnumUnderscores(
                            findEnumKeyByValue(Country, value)
                        )
                    }}
                </div>
            </template>

            <template #cell(type)="{ value, item }">
                <div class="flex gap-1.5">
                    {{
                        replaceEnumUnderscores(
                            findEnumKeyByValue(CompanyType, value)
                        )
                    }}
                </div>
            </template>

            <template #cell(action)="{ value, item }">
                <div class="flex gap-1.5">
                    <Link
                        v-if="$can('edit-crm-company')"
                        class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                        :href="route('crm.companies.edit', item.id)"
                    >
                        <IconPencilSquare classes="w-4 h-4 text-[#909090]" />
                    </Link>

                    <button
                        v-if="$can('delete-crm-company')"
                        :title="__('Delete Company')"
                        class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                        @click="showDeleteForm(item)"
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
                {{ __("Delete company") }} "{{ deleteForm.name ?? "" }}" ?
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

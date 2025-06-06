<script setup lang="ts">
import { Head, useForm } from "@inertiajs/vue3";
import { ref } from "vue";

import Header from "@/components/Header.vue";
import Input from "@/components/html/Input.vue";
import ModalSaveButtons from "@/components/html/ModalSaveButtons.vue";
import Modal from "@/components/Modal.vue";
import {ExportType} from "@/data-table/enums/ExportType";
import Table from "@/data-table/Table.vue";
import { DataTable } from "@/data-table/types";
import IconPencilSquare from "@/icons/PencilSquare.vue";
import IconTrash from "@/icons/Trash.vue";
import AppLayout from "@/layouts/AppLayout.vue";
import { baseFormRules } from "@/rules/base-form-rules";
import { Make, MakeForm } from "@/types";
import { dateTimeToLocaleString } from "@/utils";
import { validate } from "@/validations";

defineProps<{
    dataTable: DataTable<Make>;
}>();

const rules = {
    name: {
        required: true,
        minLength: 2,
        type: "string",
    },
};

const updateRules = {
    id: {
        required: true,
        type: "number",
    },
    ...rules,
};

const modelDefaults: MakeForm = {
    id: null!,
    name: null!,
    active: null!,
};

const showCreateModal = ref(false);
const showUpdateModal = ref(false);
const showDeleteModal = ref(false);

const closeCreateModal = () => {
    showCreateModal.value = false;
    storeForm.reset();
};

const closeUpdateModal = () => {
    showUpdateModal.value = false;
    updateForm.reset();
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    deleteForm.reset();
};

const showDeleteForm = (item: Make) => {
    deleteForm.id = item.id;
    deleteForm.name = item.name;

    showDeleteModal.value = true;
};

const showEditForm = (item: Make) => {
    updateForm.id = item.id;
    updateForm.name = item.name;

    showUpdateModal.value = true;
};

const storeForm = useForm<MakeForm>(modelDefaults);
const updateForm = useForm<MakeForm>(modelDefaults);
const deleteForm = useForm<MakeForm>(modelDefaults);

const store = () => {
    validate(storeForm, rules);

    storeForm.post(route("makes.store"), {
        preserveScroll: true,
        onSuccess: () => {
            storeForm.reset();
            closeCreateModal();
        },
        onError: () => {},
    });
};

const update = () => {
    validate(updateForm, updateRules);

    updateForm.put(route("makes.update", updateForm.id as number), {
        preserveScroll: true,
        onSuccess: () => {
            updateForm.reset();
            closeUpdateModal();
        },
        onError: () => {},
    });
};

const handleDelete = () => {
    validate(deleteForm, baseFormRules);

    deleteForm.delete(route("makes.destroy", deleteForm.id as number), {
        preserveScroll: true,
    });
    closeDeleteModal();
};
</script>

<template>
    <Head :title="__('Make')" />

    <AppLayout>
        <Header :text="__('Makes')" />

        <Table
            :data-table="dataTable"
            :per-page-options="[5, 10, 15, 20, 50]"
            :global-search="true"
            :advanced-filters="false"
            :export-types="[ExportType.Csv]"
        >
            <template #additionalContent>
                <div class="w-full flex gap-2">
                    <button
                        v-if="$can('create-make')"
                        class="w-full md:w-auto border border-[#E9E7E7] rounded-md px-5 py-1.5 active:scale-95 transition hover:bg-gray-50"
                        @click="showCreateModal = true"
                    >
                        {{ __("Create") }} {{ __("Make") }}
                    </button>
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
                    <button
                        v-if="$can('edit-make')"
                        :title="__('Edit make')"
                        class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                        @click="showEditForm(item)"
                    >
                        <IconPencilSquare classes="w-4 h-4 text-[#909090]" />
                    </button>

                    <button
                        v-if="$can('delete-make')"
                        :title="__('Delete make')"
                        class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                        @click="showDeleteForm(item)"
                    >
                        <IconTrash classes="w-4 h-4 text-[#909090]" />
                    </button>
                </div>
            </template>
        </Table>

        <Modal :show="showCreateModal" @close="closeCreateModal">
            <div
                class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium"
            >
                {{ __("Create Make") }}
            </div>

            <div class="p-3.5">
                <label for="create_name">
                    {{ __("Make name") }}
                    <span class="text-red-500"> *</span>
                </label>
                <Input
                    id="create_name"
                    v-model="storeForm.name"
                    :name="'name'"
                    type="text"
                    :placeholder="__('Name')"
                    class="mb-3.5 sm:mb-0"
                />
            </div>

            <ModalSaveButtons
                :processing="storeForm.processing"
                :save-text="__('Add')"
                @cancel="closeCreateModal"
                @save="store"
            />
        </Modal>

        <Modal :show="showUpdateModal" @close="closeUpdateModal">
            <div
                class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium"
            >
                {{ __("Edit Make") }}
            </div>

            <div class="p-3.5">
                <label for="update_name">
                    {{ __("Make name") }}
                    <span class="text-red-500"> *</span>
                </label>
                <Input
                    id="update_name"
                    v-model="updateForm.name"
                    :name="'name'"
                    type="text"
                    :placeholder="__('Name')"
                    class="mb-3.5 sm:mb-0"
                />
            </div>

            <ModalSaveButtons
                :processing="updateForm.processing"
                :save-text="__('Update')"
                @cancel="closeUpdateModal"
                @save="update"
            />
        </Modal>

        <Modal :show="showDeleteModal" @close="closeDeleteModal">
            <div
                class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium break-words"
            >
                {{ __("Delete make") }} "{{ deleteForm.name ?? "" }}" ?
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

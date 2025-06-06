<script setup lang="ts">
import { Head, useForm } from "@inertiajs/vue3";
import { ref } from "vue";

import Header from "@/components/Header.vue";
import Input from "@/components/html/Input.vue";
import ModalSaveButtons from "@/components/html/ModalSaveButtons.vue";
import Modal from "@/components/Modal.vue";
import Select from "@/components/Select.vue";
import {ExportType} from "@/data-table/enums/ExportType";
import Table from "@/data-table/Table.vue";
import { DataTable, Multiselect } from "@/data-table/types";
import IconPencilSquare from "@/icons/PencilSquare.vue";
import IconTrash from "@/icons/Trash.vue";
import AppLayout from "@/layouts/AppLayout.vue";
import { baseFormRules } from "@/rules/base-form-rules";
import { Make, VehicleModel, VehicleModelForm } from "@/types";
import { dateTimeToLocaleString } from "@/utils";
import { validate } from "@/validations";

defineProps<{
    dataTable: DataTable<VehicleModel>;
    make: Multiselect<Make>;
}>();

const rules = {
    name: {
        required: true,
        minLength: 1,
        type: "string",
    },
    make_id: {
        required: true,
        type: "number",
    },
};

const updateRules = {
    id: {
        required: true,
        type: "number",
    },
    ...rules,
};

const modelDefaults: VehicleModelForm = {
    id: null!,
    name: null!,
    make_id: null!,
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

const showDeleteForm = (item: VehicleModel) => {
    deleteForm.id = item.id;
    deleteForm.name = item.name;

    showDeleteModal.value = true;
};

const showEditForm = (item: VehicleModel) => {
    updateForm.id = item.id;
    updateForm.name = item.name;
    updateForm.make_id = item.make_id;

    showUpdateModal.value = true;
};

const storeForm = useForm<VehicleModelForm>(modelDefaults);
const updateForm = useForm<VehicleModelForm>(modelDefaults);
const deleteForm = useForm<VehicleModelForm>(modelDefaults);

const store = () => {
    validate(storeForm, rules);

    storeForm.post(route("vehicle-models.store"), {
        preserveScroll: true,
        onSuccess: () => {
            closeCreateModal();
            storeForm.reset();
        },
        onError: () => {},
    });
};

const update = () => {
    validate(updateForm, updateRules);

    updateForm.put(route("vehicle-models.update", updateForm.id as number), {
        preserveScroll: true,
        onSuccess: () => {
            closeUpdateModal();
            updateForm.reset();
        },
        onError: () => {},
    });
};

const handleDelete = () => {
    validate(deleteForm, baseFormRules);

    deleteForm.delete(
        route("vehicle-models.destroy", deleteForm.id as number),
        {
            preserveScroll: true,
        }
    );
    closeDeleteModal();
};
</script>

<template>
    <Head :title="__('Model')" />

    <AppLayout>
        <Header :text="__('Models')" />

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
                        v-if="$can('create-vehicle-model')"
                        class="w-full md:w-auto border border-[#E9E7E7] rounded-md px-5 py-1.5 active:scale-95 transition hover:bg-gray-50"
                        @click="showCreateModal = true"
                    >
                        {{ __("Create") }} {{ __("Model") }}
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
                        v-if="$can('edit-vehicle-model')"
                        :title="__('Edit model')"
                        class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                        @click="showEditForm(item)"
                    >
                        <IconPencilSquare classes="w-4 h-4 text-[#909090]" />
                    </button>

                    <button
                        v-if="$can('delete-vehicle-model')"
                        :title="__('Delete')"
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
                {{ __("Create") }} {{ __("Model") }}
            </div>

            <div class="p-3.5">
                <label for="create_make_id">
                    {{ __("Make") }}
                    <span class="text-red-500"> *</span>
                </label>
                <Select
                    id="create_make_id"
                    v-model="storeForm.make_id"
                    :name="'make_id'"
                    :options="make"
                    :placeholder="__('Make')"
                    class="w-full mb-3.5"
                />

                <label for="create_name">
                    {{ __("Model name") }}
                    <span class="text-red-500"> *</span>
                </label>
                <Input
                    id="create_name"
                    v-model="storeForm.name"
                    :name="'name'"
                    type="text"
                    :placeholder="__('Model name')"
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
                {{ __("Edit Model") }}
            </div>

            <div class="p-3.5">
                <label for="update_make_id">
                    {{ __("Make") }}
                </label>
                <Select
                    id="update_make_id"
                    :selected-option-value="updateForm.make_id"
                    :name="'make_id'"
                    :options="make"
                    :placeholder="__('Make')"
                    :disabled="true"
                    class="w-full mb-3.5"
                />

                <label for="update_name">
                    {{ __("Model name") }}
                    <span class="text-red-500"> *</span>
                </label>
                <Input
                    id="update_name"
                    v-model="updateForm.name"
                    :name="'name'"
                    type="text"
                    :placeholder="__('Name')"
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
                {{ __("Delete model") }} "{{ deleteForm.name ?? "" }}" ?
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

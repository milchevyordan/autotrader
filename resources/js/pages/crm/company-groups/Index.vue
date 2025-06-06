<script setup lang="ts">
import { Head, useForm } from "@inertiajs/vue3";
import { ref } from "vue";

import GridView from "@/components/crm/company-groups/GridView.vue";
import Header from "@/components/Header.vue";
import Input from "@/components/html/Input.vue";
import ModalSaveButtons from "@/components/html/ModalSaveButtons.vue";
import Modal from "@/components/Modal.vue";
import GlobalSearch from "@/data-table/components/GlobalSearch.vue";
import { Paginator } from "@/data-table/types";
import AppLayout from "@/layouts/AppLayout.vue";
import { baseFormRules } from "@/rules/base-form-rules";
import { CompanyGroup, CompanyGroupForm } from "@/types";
import { validate } from "@/validations";

defineProps<{
    paginator: Paginator;
    companyGroups: CompanyGroup[];
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

const modelDefaults: CompanyGroupForm = {
    id: null!,
    name: null!,
    creator_id: null!,
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

const showDeleteForm = (id: number, name: string) => {
    showDeleteModal.value = true;
    deleteForm.id = id;
    deleteForm.name = name;
};

const showEditForm = (id: number, name: string) => {
    updateForm.id = id;
    updateForm.name = name;

    showUpdateModal.value = true;
};

const storeForm = useForm<CompanyGroupForm>(modelDefaults);
const updateForm = useForm<CompanyGroupForm>(modelDefaults);
const deleteForm = useForm<CompanyGroupForm>(modelDefaults);

const store = () => {
    validate(storeForm, rules);

    storeForm.post(route("crm.company-groups.store"), {
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

    updateForm.put(route("crm.company-groups.update", updateForm.id), {
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

    deleteForm.delete(route("crm.company-groups.destroy", deleteForm.id), {
        preserveScroll: true,
    });
    closeDeleteModal();
};
</script>

<template>
    <Head :title="__('Company Group')" />

    <AppLayout>
        <Header :text="__('Company Groups')" />

        <div
            class="bg-white border border-[#E9E7E7] rounded-lg p-4 mb-4 sm:flex items-center justify-between shadow"
        >
            <GlobalSearch prop-name="companyGroups" />

            <button
                v-if="$can('create-crm-company-group')"
                class="ml-auto w-full md:w-auto border border-[#E9E7E7] rounded-md px-5 py-1.5 active:scale-95 transition hover:bg-gray-50"
                @click="showCreateModal = true"
            >
                {{ __("Create") }} {{ __("Company Group") }}
            </button>
        </div>
        <div class="my-2 d-flex">
            <GridView
                :paginator="paginator"
                :company-groups="companyGroups"
                @updateForm="showEditForm"
                @deleteForm="showDeleteForm"
            />
        </div>

        <Modal :show="showCreateModal" @close="closeCreateModal">
            <div
                class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium"
            >
                {{ __("Create") }} {{ __("Company Group") }}
            </div>

            <div class="p-3.5">
                <label for="create_name">
                    {{ __("Group name") }}
                    <span class="text-red-500"> *</span>
                </label>
                <Input
                    id="create_name"
                    v-model="storeForm.name"
                    :name="'name'"
                    type="text"
                    :placeholder="__('Group name')"
                    class="w-full mb-3.5"
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
                {{ __("Update Group") }}
            </div>

            <div class="p-3.5">
                <label for="update_name">
                    {{ __("Group name") }}
                    <span class="text-red-500"> *</span>
                </label>
                <Input
                    id="update_name"
                    v-model="updateForm.name"
                    :name="'name'"
                    type="text"
                    :placeholder="__('Group name')"
                    class="w-full mb-3.5"
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
                {{ __("Delete company group") }} "{{ deleteForm.name ?? "" }}" ?
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

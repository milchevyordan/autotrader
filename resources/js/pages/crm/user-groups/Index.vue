<script setup lang="ts">
import { Head, useForm, Link } from "@inertiajs/vue3";
import { ref } from "vue";

import GridView from "@/components/crm/user-groups/GridView.vue";
import Header from "@/components/Header.vue";
import ModalSaveButtons from "@/components/html/ModalSaveButtons.vue";
import Section from "@/components/html/Section.vue";
import Modal from "@/components/Modal.vue";
import GlobalSearch from "@/data-table/components/GlobalSearch.vue";
import { Paginator } from "@/data-table/types";
import AppLayout from "@/layouts/AppLayout.vue";
import { baseFormRules } from "@/rules/base-form-rules";
import { UserGroup } from "@/types";
import { validate } from "@/validations";

defineProps<{
    paginator: Paginator;
    userGroups: UserGroup[];
}>();

const showDeleteModal = ref(false);

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    deleteForm.reset();
};

const showDeleteForm = (id: number, name: string) => {
    showDeleteModal.value = true;
    deleteForm.id = id;
    deleteForm.name = name;
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

    deleteForm.delete(route("crm.user-groups.destroy", deleteForm.id), {
        preserveScroll: true,
    });
    closeDeleteModal();
};
</script>

<template>
    <Head :title="'Crm User Group'" />

    <AppLayout>
        <Header :text="__('Crm User Groups')" />

        <div
            class="bg-white border border-[#E9E7E7] rounded-lg p-4 mb-4 sm:flex items-center justify-between shadow"
        >
            <GlobalSearch prop-name="userGroups" />

            <div class="sm:flex items-center gap-2">
                <Link
                    v-if="$can('create-user-group')"
                    class="md:w-auto border border-[#E9E7E7] rounded-md px-5 py-1.5 active:scale-95 transition hover:bg-gray-50"
                    :href="route('crm.user-groups.create')"
                >
                    {{ __("Create") }} {{ __("Crm User Group") }}
                </Link>
            </div>
        </div>

        <div class="my-2 d-flex">
            <GridView
                :paginator="paginator"
                :user-groups="userGroups"
                @deleteForm="showDeleteForm"
            />
        </div>

        <Modal :show="showDeleteModal" @close="closeDeleteModal">
            <div
                class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium break-words"
            >
                {{ __("Delete user group") }} "{{ deleteForm.name ?? "" }}" ?
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

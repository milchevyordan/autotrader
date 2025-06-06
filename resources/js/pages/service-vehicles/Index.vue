<script setup lang="ts">
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref } from "vue";

import Header from "@/components/Header.vue";
import ModalSaveButtons from "@/components/html/ModalSaveButtons.vue";
import Modal from "@/components/Modal.vue";
import Select from "@/components/Select.vue";
import {ExportType} from "@/data-table/enums/ExportType";
import Table from "@/data-table/Table.vue";
import { DataTable } from "@/data-table/types";
import IconDocument from "@/icons/Document.vue";
import IconPencilSquare from "@/icons/PencilSquare.vue";
import Plus from "@/icons/Plus.vue";
import IconTrash from "@/icons/Trash.vue";
import AppLayout from "@/layouts/AppLayout.vue";
import { baseFormRules } from "@/rules/base-form-rules";
import { workflowFormRules } from "@/rules/workflow-form-rules";
import { Multiselect, ServiceVehicle, WorkflowProcess } from "@/types";
import { dateTimeToLocaleString } from "@/utils";
import { validate } from "@/validations";

defineProps<{
    dataTable: DataTable<ServiceVehicle>;
    workflowProcesses: Multiselect<WorkflowProcess>;
}>();

const model: string = "App\\Models\\ServiceVehicle";

const showCreateWorkflowModal = ref(false);

const createWorkflowForm = useForm<{
    workflow_process_class: string;
    vehicleable_type: string;
    vehicleable_id: number;
}>({
    workflow_process_class: null!,
    vehicleable_type: model,
    vehicleable_id: null!,
});

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

const showCreateWorkflowForm = (id: number) => {
    showCreateWorkflowModal.value = true;
    createWorkflowForm.vehicleable_id = id;
};

const handleCreateWorkflow = () => {
    validate(createWorkflowForm, workflowFormRules);

    createWorkflowForm.post(
        route("workflows.store", createWorkflowForm.vehicleable_id as number),
        {
            preserveScroll: true,
            onSuccess: () => {
                createWorkflowForm.reset();
            },
            onError: () => {},
        }
    );
    closeCreateWorkflowModal();
};

const closeCreateWorkflowModal = () => {
    showCreateWorkflowModal.value = false;
};

const handleDelete = () => {
    validate(deleteForm, baseFormRules);

    deleteForm.delete(
        route("service-vehicles.destroy", deleteForm.id as number),
        {
            preserveScroll: true,
        }
    );
    closeDeleteModal();
};
</script>

<template>
    <Head :title="__('Service vehicle')" />

    <AppLayout>
        <Header :text="__('Service vehicles')" />

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
                        v-if="$can('create-service-vehicle')"
                        class="w-full md:w-auto border border-[#E9E7E7] rounded-md px-5 py-1.5 active:scale-95 transition hover:bg-gray-50"
                        :href="route('service-vehicles.create')"
                    >
                        {{ __("Create") }} {{ __("Service vehicle") }}
                    </Link>
                </div>
            </template>

            <template #cell(vehicleModel.name)="{ value, item }">
                {{ item.vehicle_model?.name }}
            </template>

            <template #cell(variant.name)="{ value, item }">
                {{ item.variant?.name }}
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
                        v-if="$can('edit-service-vehicle')"
                        class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                        :href="route('service-vehicles.edit', item.id)"
                    >
                        <IconPencilSquare classes="w-4 h-4 text-[#909090]" />
                    </Link>

                    <button
                        v-if="$can('create-workflow') && !item.workflow"
                        class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                        :title="__('Create Workflow')"
                        @click="showCreateWorkflowForm(item.id)"
                    >
                        <Plus classes="w-4 h-4 text-[#909090]" />
                    </button>

                    <Link
                        v-if="$can('view-workflow') && item.workflow"
                        :href="route('workflows.show', item.workflow.id)"
                        class="border border-[#008FE3] bg-[#008FE3] text-white rounded-md p-1 active:scale-90 transition"
                    >
                        <IconDocument classes="w-4 h-4" />
                    </Link>

                    <button
                        v-if="$can('delete-service-vehicle')"
                        :title="__('Delete service vehicle')"
                        class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                        @click="showDeleteForm(item.id)"
                    >
                        <IconTrash classes="w-4 h-4 text-[#909090]" />
                    </button>
                </div>
            </template>
        </Table>

        <Modal
            :show="showCreateWorkflowModal"
            @close="closeCreateWorkflowModal"
        >
            <div
                class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium"
            >
                {{
                    __("Create workflow for vehicle #") +
                    createWorkflowForm?.vehicleable_id
                }}
                ?
            </div>

            <div class="border border-[#E9E7E7] px-3.5 p-3 text-xl font-medium">
                <label for="">
                    {{ __("Workflow") }} {{ __("Type") }}
                    <span class="text-red-500"> *</span>
                </label>

                <Select
                    v-model="createWorkflowForm.workflow_process_class"
                    :name="'workflow_process_class'"
                    :options="workflowProcesses"
                    :placeholder="__('Workflow')"
                    class="w-full mb-3.5 sm:mb-0"
                />
            </div>

            <ModalSaveButtons
                :processing="createWorkflowForm.processing"
                :save-text="__('Create')"
                @cancel="closeCreateWorkflowModal"
                @save="handleCreateWorkflow"
            />
        </Modal>

        <Modal :show="showDeleteModal" @close="closeDeleteModal">
            <div
                class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium break-words"
            >
                {{ __("Delete Service Vehicle #") + deleteForm?.id }} ?
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

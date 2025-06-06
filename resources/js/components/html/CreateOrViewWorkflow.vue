<script setup lang="ts">
import { Link, router, useForm } from "@inertiajs/vue3";
import { ref } from "vue";

import ModalSaveButtons from "@/components/html/ModalSaveButtons.vue";
import Modal from "@/components/Modal.vue";
import Select from "@/components/Select.vue";
import IconDocument from "@/icons/Document.vue";
import GitBranch from "@/icons/GitBranch.vue";
import { workflowFormRules } from "@/rules/workflow-form-rules";
import { Multiselect, Vehicle, WorkflowProcess } from "@/types";
import { validate } from "@/validations";

const props = defineProps<{
    vehicle: Vehicle;
    type: string;
    workflowProcesses?: Multiselect<WorkflowProcess>;
}>();

const showCreateWorkflowModal = ref(false);

const createWorkflowForm = useForm<{
    workflow_process_class: string;
    vehicleable_type: string;
    vehicleable_id: number;
}>({
    workflow_process_class: null!,
    vehicleable_type: props.type,
    vehicleable_id: null!,
});

const showCreateWorkflowForm = async (id: number) => {
    if (!props.workflowProcesses) {
        await new Promise((resolve, reject) => {
            router.reload({
                only: ["workflowProcesses"],
                onSuccess: resolve,
                onError: reject,
            });
        });
    }

    showCreateWorkflowModal.value = true;
    createWorkflowForm.vehicleable_id = id;
};

const closeCreateWorkflowModal = () => {
    showCreateWorkflowModal.value = false;
    createWorkflowForm.reset();
};

const handleCreateWorkflow = () => {
    validate(createWorkflowForm, workflowFormRules);

    createWorkflowForm.post(
        route("workflows.store", createWorkflowForm.vehicleable_id as number),
        {
            preserveScroll: true,
        }
    );
    closeCreateWorkflowModal();
};
</script>

<template>
    <button
        v-if="$can('create-workflow') && !vehicle.workflow"
        class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
        :title="__('Create workflow')"
        @click="showCreateWorkflowForm(vehicle.id)"
    >
        <GitBranch classes="w-4 h-4 text-[#909090]" />
    </button>

    <Link
        v-if="$can('view-workflow') && vehicle.workflow && !vehicle.deleted_at"
        :href="route('workflows.show', vehicle.workflow.id)"
        class="border border-[#008FE3] bg-[#008FE3] text-white rounded-md p-1 active:scale-90 transition"
    >
        <IconDocument classes="w-4 h-4" />
    </Link>

    <Modal :show="showCreateWorkflowModal" @close="closeCreateWorkflowModal">
        <div class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium">
            {{
                __("Create workflow for vehicle #") +
                createWorkflowForm?.vehicleable_id
            }}
            ?
        </div>

        <div class="border border-[#E9E7E7] px-3.5 p-3">
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
</template>

<script setup lang="ts">
import { router, useForm } from "@inertiajs/vue3";
import { ref, watch } from "vue";

import ActionButtons from "@/components/html/ActionButtons.vue";
import InputFile from "@/components/html/InputFile.vue";
import RadioButtonToggle from "@/components/html/RadioButtonToggle.vue";
import ModalSaveButtons from "@/components/html/ModalSaveButtons.vue";
import Modal from "@/components/Modal.vue";
import { workflowStepFormRules } from "@/rules/workflow-step-form-rules";
import { workflowStepUpdateFormRules } from "@/rules/workflow-step-update-form-rules";
import { validate } from "@/validations";
import { Workflow, WorkflowFinishedStepForm, WorkflowStep } from "@/types";
import { dateForInput } from "@/utils";

const emit = defineEmits(["closeModal"]);

const props = withDefaults(
    defineProps<{
        workflow: Workflow;
        step: WorkflowStep;
        shown?: boolean;
    }>(),
    {
        shown: false,
    }
);

const upsertStepForm = useForm<WorkflowFinishedStepForm>({
    workflow_id: props.workflow.id,
    class_name: props.step.className,
    additional_value: props.step.summary == "Yes",
    files: [],
});

watch(
    () => props.step,
    (step) => {
        upsertStepForm.class_name = step.className;
        upsertStepForm.additional_value = step.summary == "Yes";
    }
);

const closeModal = () => {
    emit("closeModal");
};

const store = () => {
    upsertStepForm.additional_value = upsertStepForm.additional_value
        ? "Yes"
        : "No";
    upsertStepForm.post(route("workflow-finished-steps.upsert"), {
        preserveScroll: true,
        onSuccess: () => {
            upsertStepForm.reset();
            closeModal();
        },
        onError: () => {},
    });
};

const showDeleteModal = ref(false);

const deleteForm = useForm<WorkflowFinishedStepForm>({
    workflow_id: props.workflow.id,
    class_name: null!,
});

const openDeleteModal = () => {
    deleteForm.class_name = props.step.className;
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    deleteForm.reset();
};

const handleDelete = () => {
    deleteForm.delete(route("workflow-finished-steps.destroy"), {
        preserveScroll: true,
        preserveState: true,
    });

    closeDeleteModal();
    closeModal();
};
</script>

<template>
    <Modal :show="shown" @close="closeModal">
        <div class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium">
            {{ __("Choice either Yes or No") }}
        </div>

        <div class="p-3.5">
            <RadioButtonToggle
                v-model="upsertStepForm.additional_value"
                :name="`additional_value`"
            />
        </div>

        <div class="flex justify-center items-center">
            <ActionButtons
                :processing="upsertStepForm.processing"
                :save-text="__('Save')"
                :has-delete-option="step.isCompleted"
                @cancel="closeModal"
                @save="store"
                @open-delete-modal="openDeleteModal"
            />
        </div>
    </Modal>

    <Modal :show="showDeleteModal" @close="closeDeleteModal">
        <div class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium">
            {{ __("Are you sure you want to delete this step") }} ?
        </div>

        <ModalSaveButtons
            :processing="deleteForm.processing"
            :save-text="__('Delete')"
            @cancel="closeDeleteModal"
            @save="handleDelete"
        />
    </Modal>
</template>

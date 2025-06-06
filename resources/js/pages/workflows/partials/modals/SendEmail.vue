<script setup lang="ts">
import { useForm } from "@inertiajs/vue3";
import { ref, watch } from "vue";

import ActionButtons from "@/components/html/ActionButtons.vue";

import ModalSaveButtons from "@/components/html/ModalSaveButtons.vue";
import Modal from "@/components/Modal.vue";

import { Workflow, WorkflowFinishedStepForm, WorkflowStep } from "@/types";
import Textarea from "@/components/html/Textarea.vue";
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
    additional_value:
        props.step.finishedStepAdditionalValue ?? props.step.emailTemplateText,
    email_recipient: props.step.emailRecipient,
    email_subject: props.step.emailSubject,
    files: null!,
});

watch(
    () => props.step,
    (step) => {
        upsertStepForm.class_name = step.className;
        upsertStepForm.additional_value =
            step.finishedStepAdditionalValue ?? step.emailTemplateText;
        upsertStepForm.finished_at = step.finishedStepAdditionalValue;
    }
);

const closeModal = () => {
    emit("closeModal");
};

const store = () => {
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
    // validate(deleteForm, workflowStepFormRules);

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
            {{ __("Email") }}
        </div>

        <div class="col-span-2 flex gap-3 mt-2 pt-1 pb-3 px-4">
            <Textarea
                v-model="upsertStepForm.additional_value"
                :disabled="
                    upsertStepForm.processing ||
                    step.finishedStepAdditionalValue !== null
                "
                :name="'additional_value'"
                :placeholder="__('Email')"
                classes="mb-3.5 sm:mb-0"
            />
        </div>

        <div class="flex justify-center items-center">
            <div
                v-if="
                    'emailRecipient' in step && !upsertStepForm.email_recipient
                "
                class="bg-red-500 p-2 mb-4 text-white font-bold rounded-sm shadow"
            >
                {{ __("No email found") }}
            </div>

            <ActionButtons
                v-else
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

<script setup lang="ts">
import { useForm } from "@inertiajs/vue3";
import { Ref, ref, watch } from "vue";

import ActionButtons from "@/components/html/ActionButtons.vue";
import InputFile, { FilePreview } from "@/components/html/InputFile.vue";
import ModalSaveButtons from "@/components/html/ModalSaveButtons.vue";
import Modal from "@/components/Modal.vue";
import { Workflow, WorkflowFinishedStepForm, WorkflowStep } from "@/types";

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
    email_recipient: props.step.emailRecipient,
    email_subject: props.step.emailSubject,
    additional_value:
        props.step.finishedStepAdditionalValue ?? props.step.emailTemplateText,
    files: [],
});

watch(
    () => props.step,
    (step) => {
        upsertStepForm.class_name = step.className;
        upsertStepForm.email_recipient = step.emailRecipient;
        upsertStepForm.email_subject = step.emailSubject;
        upsertStepForm.additional_value =
            step.finishedStepAdditionalValue ?? step.emailTemplateText;
        upsertStepForm.files = [];
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
            handleFileDeletion();
        },
        onError: () => {},
    });
};

const showDeleteModal = ref(false);

const deleteForm = useForm<WorkflowFinishedStepForm>({
    _method: "delete",
    workflow_id: props.workflow.id,
    class_name: props.step.className,
});

const openDeleteModal = () => {
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    deleteForm.reset();
};

const handleDelete = async () => {
    // validate(deleteForm, workflowStepFormRules);

    deleteForm.delete(route("workflow-finished-steps.destroy"), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            deleteForm.reset();
            closeModal();
        },
    });
};

const handleFileDeletion = async () => {
    if (props.step.files.length == 1) {
        handleDelete();
    }

    closeDeleteModal();
    closeModal();
};
</script>

<template>
    <Modal :show="shown" @close="closeModal">
        <div class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium">
            {{ __("Upload") }}
        </div>

        <div class="p-3.5">
            <InputFile
                v-model="upsertStepForm.files"
                :files="step.files"
                text-classes="py-14"
                @delete="handleFileDeletion"
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

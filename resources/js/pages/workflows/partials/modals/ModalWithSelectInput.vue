<script setup lang="ts">
import { useForm } from "@inertiajs/vue3";
import { ref, watch } from "vue";

import ActionButtons from "@/components/html/ActionButtons.vue";
import ModalSaveButtons from "@/components/html/ModalSaveButtons.vue";
import Modal from "@/components/Modal.vue";
import Select from "@/components/Select.vue";
import { Workflow, WorkflowFinishedStepForm, WorkflowStep } from "@/types";
import { camelToTitleCase } from "@/utils";

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
    additional_value: props.step.finishedStepAdditionalValue,
    files: null!,
});

watch(
    () => props.step,
    (step) => {
        upsertStepForm.class_name = step.className;
        upsertStepForm.finished_at = step.finishedStepAdditionalValue;
    }
);

const closeModal = () => {
    emit("closeModal");
};

const store = () => {
    upsertStepForm.additional_value =
        upsertStepForm.additional_value?.toString();
    upsertStepForm.post(route("workflow-finished-steps.upsert"), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
        },
        onError: () => {
            upsertStepForm.reset();
        },
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
        onSuccess: () => {
            upsertStepForm.additional_value = null!;
            deleteForm.reset();
        },
    });

    closeDeleteModal();
    closeModal();
};
</script>

<template>
    <Modal :show="shown" @close="closeModal">
        <div class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium">
            {{ camelToTitleCase(step.className) }}
        </div>

        <div class="col-span-2 flex gap-3 mt-2 pt-1 pb-3 px-4">
            <Select
                v-model="upsertStepForm.additional_value"
                :name="'additional_value'"
                :options="step.componentData"
                :placeholder="__('Additional Value')"
                class="w-full mb-3.5 sm:mb-0"
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

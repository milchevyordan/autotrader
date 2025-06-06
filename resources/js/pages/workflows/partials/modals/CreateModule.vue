<script setup lang="ts">
import { router, useForm } from "@inertiajs/vue3";
import { ref, watch } from "vue";

import ActionButtons from "@/components/html/ActionButtons.vue";
import Input from "@/components/html/Input.vue";
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

const closeModal = () => {
    emit("closeModal");
};

const handleCreateModule = () => {
    const url = (props.step as any).url as string;

    window.open(url, "_blank", "noopener,noreferrer");
};
</script>

<template>
    <Modal :show="shown" @close="closeModal">
        <div class="border-b border-[#E9E7E7] px-3.5 p-3 text-xl font-medium">
            {{ __("Create ") }}
        </div>

        <div class="col-span-2 flex gap-3 mt-2 pt-1 pb-3 px-4"></div>

        <div class="flex justify-center items-center">
            <ModalSaveButtons
                :processing="false"
                :save-text="__('Create')"
                @cancel="closeModal"
                @save="handleCreateModule"
            />
        </div>
    </Modal>
</template>

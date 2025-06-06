<script setup lang="ts">
import { Link, useForm } from "@inertiajs/vue3";
import { defineAsyncComponent, ref, shallowRef } from "vue";

import Accordion from "@/components/Accordion.vue";
import Section from "@/components/html/Section.vue";
import CheckIcon from "@/icons/Check.vue";
import WarningIcon from "@/icons/Warning.vue";
import { Workflow, WorkflowFinishedStepForm } from "@/types";
import { WorkflowStep } from "@/types";
import { formatDateByTimezone } from "@/utils";

const props = defineProps<{
    workflow: Workflow;
}>();

const modalComponentMap: Record<
    string,
    ReturnType<typeof defineAsyncComponent>
> = {
    Date: defineAsyncComponent(
        () => import("@/pages/workflows/partials/modals/Date.vue")
    ),

    WeekInput: defineAsyncComponent(
        () => import("@/pages/workflows/partials/modals/WeekInput.vue")
    ),

    CreateModule: defineAsyncComponent(
        () => import("@/pages/workflows/partials/modals/CreateModule.vue")
    ),

    UploadFiles: defineAsyncComponent(
        () => import("@/pages/workflows/partials/modals/UploadFiles.vue")
    ),

    UploadImages: defineAsyncComponent(
        () => import("@/pages/workflows/partials/modals/UploadImages.vue")
    ),

    ModalWithSelectInput: defineAsyncComponent(
        () =>
            import("@/pages/workflows/partials/modals/ModalWithSelectInput.vue")
    ),

    SendEmail: defineAsyncComponent(
        () => import("@/pages/workflows/partials/modals/SendEmail.vue")
    ),

    YesOrNoResponse: defineAsyncComponent(
        () => import("@/pages/workflows/partials/modals/YesOrNoResponse.vue")
    ),
};

const iconComponentMap: Record<
    string,
    ReturnType<typeof defineAsyncComponent>
> = {
    Invoice: defineAsyncComponent(() => import("@/icons/Invoice.vue")),

    CheckListIncompleted: defineAsyncComponent(
        () => import("@/icons/CheckListIncompleted.vue")
    ),

    Truck: defineAsyncComponent(() => import("@/icons/Truck.vue")),

    TruckWithArrow: defineAsyncComponent(
        () => import("@/icons/TruckWithArrow.vue")
    ),

    CheckList: defineAsyncComponent(() => import("@/icons/CheckList.vue")),

    Mail: defineAsyncComponent(() => import("@/icons/MailIcon.vue")),

    Payment: defineAsyncComponent(() => import("@/icons/Payment.vue")),

    CalendarCheck: defineAsyncComponent(
        () => import("@/icons/CalendarCheck.vue")
    ),

    HandshakeDeal: defineAsyncComponent(
        () => import("@/icons/HandshakeDeal.vue")
    ),

    PurchaseUser: defineAsyncComponent(
        () => import("@/icons/PurchaseUser.vue")
    ),

    ImportMarketing: defineAsyncComponent(
        () => import("@/icons/ImportMarketing.vue")
    ),

    PlanetAirplane: defineAsyncComponent(
        () => import("@/icons/PlanetAirplane.vue")
    ),
};

const shownProp = ref<boolean>(false);
const currentModal = shallowRef<ReturnType<typeof defineAsyncComponent> | null>(
    null
);
const currentModalStep = ref<WorkflowStep>(null!);

const stepForm = useForm<WorkflowFinishedStepForm>({
    workflow_id: props.workflow.id,
    class_name: null!,
    additional_value: null!,
    finished_at: null!,
    files: [],
});

const openModal = (step: WorkflowStep) => {
    if (!step.modalComponentName) {
        return;
    }

    currentModal.value = modalComponentMap[step.modalComponentName];
    currentModalStep.value = step;

    shownProp.value = true;
};

const handleQuickDateSet = (step: WorkflowStep) => {
    if (!step.hasQuickDateFinish) {
        return;
    }

    const nowFormattedUtc = formatDateByTimezone(
        new Date(),
        "yyyy-MM-dd HH:mm:ss",
        true
    ).value;
    stepForm.class_name = step.className;
    stepForm.finished_at = nowFormattedUtc;

    stepForm.post(route(`workflow-finished-steps.upsert`), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            stepForm.reset();
        },
        onError: () => {},
    });
};

const closeModal = () => {
    shownProp.value = false;
};
</script>

<template>
    <Section classes="my-2 mt-4">
        <div class="p-4 font-semibold text-xl sm:text-2xl mb-4">
            {{ workflow.process.name }} {{ __("Process") }}
        </div>

        <div>
            <div
                class="items-start space-y-4 sm:space-y-0 flex flex-wrap justify-between p-4"
            >
                <div
                    v-for="(subprocess, index) in workflow.process.subprocesses"
                    :key="index"
                    class="flex flex-col flex-wrap space-y-4"
                >
                    <Accordion :body-shown="!subprocess.isCompleted">
                        <template #head>
                            <div
                                class="process-container flex flex-wrap items-start pr-10"
                            >
                                <component
                                    :is="
                                        iconComponentMap[
                                            subprocess.vueComponentIcon
                                        ]
                                    "
                                    :color="
                                        subprocess.isCompleted
                                            ? '#00A793' // Green color
                                            : '#eab308' // Yellow color
                                    "
                                    :class="{ 'w-6 h-6 flex-shrink-0': true }"
                                />
                                <h3
                                    class="font-medium leading-tight text-[#00A793]"
                                    :class="{
                                        'text-yellow-500':
                                            !subprocess.isCompleted,
                                    }"
                                >
                                    {{ subprocess.name }}
                                </h3>
                            </div>
                            <div class="status-container">
                                <div
                                    v-if="subprocess.isCompleted"
                                    class="my-1 text-sm font-medium me-2 px-2.5 py-0.5 rounded bg-green-100 text-green-800"
                                >
                                    {{ __("Completed") }}
                                </div>
                                <div
                                    v-else
                                    class="my-1 text-sm font-medium me-2 px-2.5 py-0.5 rounded bg-yellow-100 text-yellow-800"
                                >
                                    {{ __("In progress") }}
                                </div>
                            </div>
                        </template>

                        <hr class="my-1 w-full border-t border-gray-300" />
                        <!-- Statuses -->
                        <div
                            class="status-container flex flex-col flex-grow shadow p-4"
                        >
                            <div
                                v-for="status in subprocess.statuses"
                                class="flex-grow"
                            >
                                <Accordion :body-shown="status.isCompleted">
                                    <template #head>
                                        <div class="flex justify-between">
                                            <div
                                                class="text-sm font-medium me-2 px-2.5 py-0.5 mb-2 rounded bg-green-500 text-green-700"
                                                :class="
                                                    status.isCompleted
                                                        ? 'bg-green-500 text-green-700'
                                                        : 'bg-red-300 text-red-800'
                                                "
                                            >
                                                {{ status.name }}
                                            </div>

                                            <div class="summary pr-6">
                                                {{ status.summary }}
                                            </div>
                                        </div>
                                    </template>
                                    <template #collapsedContent>
                                        <div class="ml-4 text-slate-700 p-2">
                                            <div v-for="step in status.steps">
                                                <div
                                                    class="step-container flex"
                                                >
                                                    <div
                                                        class="icon w-7 border-r"
                                                    >
                                                        <span
                                                            v-if="
                                                                step.isDisabled
                                                            "
                                                            class="text-slate-400"
                                                        >
                                                            <CheckIcon />
                                                        </span>

                                                        <span
                                                            v-else-if="
                                                                step.isCompleted
                                                            "
                                                            class="text-[#00A793]"
                                                            :class="{
                                                                'cursor-pointer hover:bg-[#f5f5f5] hover:scale-[1.5]':
                                                                    step.hasQuickDateFinish,
                                                            }"
                                                            @click="
                                                                handleQuickDateSet(
                                                                    step
                                                                )
                                                            "
                                                        >
                                                            <CheckIcon />
                                                        </span>

                                                        <span
                                                            v-else
                                                            class="text-red-600 font-semi text-xl px-2"
                                                            :class="{
                                                                'cursor-pointer hover:bg-[#f5f5f5] hover:scale-[1.5]':
                                                                    step.hasQuickDateFinish,
                                                            }"
                                                            @click="
                                                                handleQuickDateSet(
                                                                    step
                                                                )
                                                            "
                                                        >
                                                            X
                                                        </span>
                                                    </div>

                                                    <div
                                                        v-if="step.isDisabled"
                                                        class="name text-slate-400"
                                                    >
                                                        {{ step.name }}
                                                    </div>

                                                    <div
                                                        v-else-if="step.url"
                                                        class="cursor-pointer hover:bg-[#f5f5f5] hover:scale-[1.05] hover:text-black;"
                                                    >
                                                        <Link :href="step.url">
                                                            {{ step.name }}
                                                        </Link>
                                                    </div>

                                                    <div
                                                        v-else
                                                        class="name"
                                                        :class="{
                                                            'cursor-pointer hover:bg-[#f5f5f5] hover:scale-[1.05] hover:text-black':
                                                                step.modalComponentName,
                                                            'bg-red-500 text-white':
                                                                step.redFlag
                                                                    ?.isTriggered,
                                                        }"
                                                        @click="openModal(step)"
                                                    >
                                                        <span class="ps-1">
                                                            {{ step.name }}
                                                        </span>
                                                    </div>

                                                    <div
                                                        class="date-or-response-container ml-auto pl-2"
                                                    >
                                                        <div
                                                            v-if="step.summary"
                                                            class="response"
                                                        >
                                                            {{
                                                                __(step.summary)
                                                            }}
                                                        </div>
                                                        <div
                                                            v-if="
                                                                step.redFlag
                                                                    ?.isTriggered
                                                            "
                                                        >
                                                            <WarningIcon
                                                                classes="w-5 h-5 text-red-500"
                                                            />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </Accordion>
                            </div>
                        </div>
                    </Accordion>
                </div>
            </div>
        </div>
    </Section>

    <!-- Dynamic modal component -->
    <component
        :is="currentModal"
        :step="currentModalStep"
        :workflow="workflow"
        :shown="shownProp"
        @closeModal="closeModal"
    />
    <!-- / Dynamic modal component -->
</template>

<script setup lang="ts">
import FileGroup from "@/components/html/FileGroup.vue";
import Section from "@/components/html/Section.vue";
import { DatabaseFile, Workflow } from "@/types";

defineProps<{
    workflow: Workflow;
}>();
</script>

<template>
    <Section classes="mt-4">
        <div class="p-4">
            <div class="font-semibold text-xl sm:text-2xl">
                {{ __("Documents") }}
            </div>
        </div>

        <div
            class="information-container flex flex-wrap justify-around p-4 gap-4 rounded-md bg-white"
        >
            <FileGroup
                v-if="
                    'purchase_order' in workflow.vehicle &&
                    workflow.vehicle?.purchase_order.length
                "
                :files="(workflow.vehicle?.purchase_order[0].files as DatabaseFile[])"
                :title="__('Purchase order files')"
            />

            <FileGroup
                v-if="
                    'sales_order' in workflow.vehicle &&
                    workflow.vehicle?.sales_order.length
                "
                :files="(workflow.vehicle?.sales_order[0].files as DatabaseFile[])"
                :title="__('Sales order files')"
            />

            <div
                v-for="(transport_order, index) in workflow.vehicle
                    .transport_orders"
                :key="index"
            >
                <FileGroup
                    v-if="transport_order.files?.length"
                    :files="(transport_order.files as DatabaseFile[])"
                    :title="`${__('Transport order #')}${
                        transport_order.id
                    }${__(' files')}`"
                />
            </div>

            <FileGroup
                v-if="
                    'documents' in workflow.vehicle &&
                    workflow.vehicle?.documents?.length
                "
                :files="(workflow.vehicle?.documents[0]?.files as DatabaseFile[])"
                :title="__('Document files')"
            />

            <FileGroup
                v-if="
                    workflow.vehicle.service_order &&
                    workflow.vehicle.service_order.files?.length
                "
                :files="(workflow.vehicle.service_order.files as DatabaseFile[])"
                :title="__('Service order files')"
            />

            <FileGroup
                v-if="
                    workflow.vehicle.work_order &&
                    workflow.vehicle.work_order.files?.length
                "
                :files="(workflow.vehicle.work_order.files as DatabaseFile[])"
                :title="__('Work order files')"
            />

            <div
                v-for="(task, taskIndex) in workflow.vehicle.work_order.tasks"
                v-if="workflow.vehicle.work_order"
                :key="taskIndex"
            >
                <FileGroup
                    v-if="task.files?.length"
                    :files="(task.files as DatabaseFile[])"
                    :title="`${__('Work order task #')}${task.id}${__(
                        ' files'
                    )}`"
                />
            </div>

            <div v-for="(fileSection, fileSectionName) in workflow.files">
                <FileGroup
                    v-if="fileSection?.length"
                    :files="fileSection"
                    :title="fileSectionName"
                />
            </div>
        </div>
    </Section>
</template>

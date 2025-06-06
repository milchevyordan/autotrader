<script setup lang="ts">
import { useForm } from "@inertiajs/vue3";

import { WorkOrderStatus } from "@/enums/WorkOrderStatus";
import { WorkOrderTaskStatus } from "@/enums/WorkOrderTaskStatus";
import { setFlashMessages } from "@/globals";
import IconCheck from "@/icons/Check.vue";
import { $can } from "@/plugins/permissions";
import { UpdateStatusForm, WorkOrderForm, WorkOrderTask } from "@/types";
import {
    changeStatus,
    dateTimeToLocaleString,
    findCreatedAtStatusDate,
    replaceEnumUnderscores,
} from "@/utils";

const props = defineProps<{
    workOrder: WorkOrderForm;
    formIsDirty: boolean;
}>();

const checkColoredLink = (index: number) => {
    return index <= props.workOrder.status;
};

const statuses = Object.entries(WorkOrderStatus)
    .filter(([name]) => isNaN(Number(name)))
    .map(([name, value]) => ({
        name,
        value,
    }));

const updateStatusForm = useForm<UpdateStatusForm>({
    _method: "patch",
    id: props.workOrder.id,
    status: null!,
    route: "work-orders",
});

const clickableStatus = (status: string) => {
    const statusNumber =
        WorkOrderStatus[status as keyof typeof WorkOrderStatus];
    const currentStatus = props.workOrder.status;

    return (
        !updateStatusForm.processing &&
        (statusNumber - currentStatus == 1 || $can("super-change-status"))
    );
};

const handleStatusChange = async (status: string) => {
    if (!clickableStatus(status)) {
        return;
    }

    if (props.formIsDirty) {
        setFlashMessages({
            error: "You need to save before changing status",
        });

        return;
    }

    switch (status) {
        case "Completed":
            if (
                !props.workOrder.tasks.every(
                    (task: WorkOrderTask) =>
                        task.status == WorkOrderTaskStatus.Completed
                )
            ) {
                setFlashMessages({
                    error: "All tasks need to be completed before changing to this status",
                });

                break;
            }

            await changeStatus(updateStatusForm, WorkOrderStatus.Completed);

            break;

        default:
            await changeStatus(
                updateStatusForm,
                WorkOrderStatus[status as keyof typeof WorkOrderStatus]
            );
            break;
    }
};
</script>

<template>
    <div
        class="flex flex-wrap w-full text-sm font-medium text-center text-gray-500 sm:text-base rounded-lg border border-[#E9E7E7] shadow-2xl bg-white mb-10 py-4 sm:py-5 px-4 sticky top-24 z-20"
    >
        <div
            v-for="(status, index) in statuses"
            :key="index"
            class="flex items-center"
        >
            <div class="status-container px-1 md:px-3 lg:px-5">
                <span
                    class="flex justify-center items-center after:hidden sm:after:mx-2"
                    :class="{
                        'border-blue-200 text-blue-400':
                            status.value <= workOrder.status,
                        'border-gray-200': status.value > workOrder.status,
                        'cursor-pointer': clickableStatus(status.name),
                    }"
                    @click="handleStatusChange(status.name)"
                >
                    <span
                        class="border sm:border-0 rounded-full px-2 py-0.5"
                        :class="
                            status.value <= workOrder.status
                                ? 'border-blue-200'
                                : 'border-gray-200'
                        "
                    >
                        <span v-if="checkColoredLink(status.value as number)">
                            <IconCheck classes="w-5 h-5" stroke="2.5" />
                        </span>
                    </span>

                    <span
                        class="hidden md:inline-flex flex-col whitespace-nowrap"
                    >
                        <span>{{
                            replaceEnumUnderscores(status.name, true)
                        }}</span>
                        <span class="text-xs">
                            {{
                                status.value == 1
                                    ? dateTimeToLocaleString(
                                          workOrder.created_at
                                      )
                                    : findCreatedAtStatusDate(
                                          workOrder.statuses,
                                          status.value as number
                                      )
                            }}
                        </span>
                    </span>
                </span>
            </div>
        </div>
    </div>
</template>

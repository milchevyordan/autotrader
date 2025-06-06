<script setup lang="ts">
import { useForm } from "@inertiajs/vue3";

import { ServiceOrderStatus } from "@/enums/ServiceOrderStatus";
import { setFlashMessages } from "@/globals";
import IconCheck from "@/icons/Check.vue";
import { $can } from "@/plugins/permissions";
import { ServiceOrder, UpdateStatusForm } from "@/types";
import {
    changeStatus,
    dateTimeToLocaleString,
    findCreatedAtStatusDate,
    replaceEnumUnderscores,
} from "@/utils";

const props = defineProps<{
    serviceOrder: ServiceOrder;
    formIsDirty: boolean;
}>();

const updateStatusForm = useForm<UpdateStatusForm>({
    _method: "patch",
    id: props.serviceOrder.id,
    status: null!,
    route: "service-orders",
});

const clickableStatus = (status: string) => {
    const orderStatus =
        ServiceOrderStatus[status as keyof typeof ServiceOrderStatus];
    const currentStatus = props.serviceOrder.status;

    return (
        !updateStatusForm.processing &&
        (orderStatus - currentStatus === 1 || $can("super-change-status"))
    );
};

const handleStatusChange = (status: string) => {
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
        case "Submitted":
            if (!props.serviceOrder.service_vehicle_id) {
                setFlashMessages({
                    error: "Not selected any vehicles, need to select Vehicle",
                });

                break;
            }
            changeStatus(updateStatusForm, ServiceOrderStatus.Submitted);
            break;

        default:
            changeStatus(
                updateStatusForm,
                ServiceOrderStatus[status as keyof typeof ServiceOrderStatus]
            );
            break;
    }
};

const checkColoredLink = (index: number) => {
    return index <= props.serviceOrder.status;
};

const statuses = Object.entries(ServiceOrderStatus)
    .filter(([name, value]) => isNaN(Number(name)))
    .map(([name, value]) => ({
        name,
        value,
    }));
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
                            status.value as number <= serviceOrder.status,
                        'border-gray-200': status.value as number > serviceOrder.status,
                        'cursor-pointer':
                            serviceOrder.status === ServiceOrderStatus.Concept &&
                            !$can('submit-service-order')
                                ? false
                                : clickableStatus(status.name),
                    }"
                    @click="
                        serviceOrder.status === ServiceOrderStatus.Concept &&
                        !$can('submit-service-order')
                            ? false
                            : handleStatusChange(status.name)
                    "
                >
                    <span
                        class="border sm:border-0 rounded-full px-2 py-0.5"
                        :class="
                            status.value as number <= serviceOrder.status
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
                                          serviceOrder.created_at
                                      )
                                    : findCreatedAtStatusDate(
                                          serviceOrder.statuses,
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

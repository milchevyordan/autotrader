<script setup lang="ts">
import { Head, useForm } from "@inertiajs/vue3";
import { computed } from "vue";

import Accordion from "@/components/Accordion.vue";
import Header from "@/components/Header.vue";
import InputFile from "@/components/html/InputFile.vue";
import InternalRemarks from "@/components/html/InternalRemarks.vue";
import ResetSaveButtons from "@/components/html/ResetSaveButtons.vue";
import GeneralInformation from "@/components/work-orders/GeneralInformation.vue";
import Stepper from "@/components/work-orders/Stepper.vue";
import Tasks from "@/components/work-orders/Tasks.vue";
import Table from "@/data-table/Table.vue";
import { DataTable, Multiselect } from "@/data-table/types";
import { WorkOrderType } from "@/enums/WorkOrderType";
import AppLayout from "@/layouts/AppLayout.vue";
import { workOrdersFormRules } from "@/rules/work-orders-form-rules";
import {
    DatabaseFile,
    Ownership,
    Role,
    User,
    WorkOrder,
    WorkOrderForm,
    Workorderable,
    WorkflowProcess,
} from "@/types";
import {
    dateTimeToLocaleString,
    findEnumKeyByValue,
    replaceEnumUnderscores,
    resetOwnerId,
    withFlash,
    workOrderTypeComponentMap,
} from "@/utils";
import { validate } from "@/validations";

const props = defineProps<{
    workOrder: WorkOrder;
    files: { files: DatabaseFile[] };
    dataTable: DataTable<Workorderable>;
    mainCompanyRoles?: Multiselect<Role>;
    mainCompanyUsers: Multiselect<User>;
    acceptedOwnership: Ownership;
    pendingOwnerships: Ownership[];
    workflowProcesses?: Multiselect<WorkflowProcess>;
}>();

const form = useForm<WorkOrderForm>({
    _method: "put",
    id: props.workOrder.id,
    owner_id: props.acceptedOwnership?.user_id,
    status: props.workOrder.status,
    type: props.workOrder.type,
    vehicleable_id: props.workOrder.vehicleable_id,

    files: [],

    internal_remark_user_ids: [],
    internal_remark_role_ids: [],
    internal_remark: null!,
});

const save = async (only?: Array<string>) => {
    validate(form, workOrdersFormRules);

    return new Promise<void>((resolve, reject) => {
        form.post(route("work-orders.update", props.workOrder.id as number), {
            preserveScroll: true,
            preserveState: true,
            forceFormData: true, // preserves all form data
            only: withFlash(only),
            onSuccess: () => {
                form.reset("internal_remark", "files");

                resetOwnerId(form);

                resolve();
            },
            onError: () => {
                reject(new Error("Error, during update"));
            },
        });
    });
};

const workOrderType = computed(() =>
    findEnumKeyByValue(WorkOrderType, form.type)
);
</script>

<template>
    <Head :title="__('Work Order')" />

    <AppLayout>
        <Header :text="__('Work Order')" />

        <Stepper :work-order="workOrder" :form-is-dirty="form.isDirty" />

        <div
            class="relative rounded-lg border border-[#E9E7E7] shadow-sm bg-white py-4 sm:py-6 px-4 mt-4"
        >
            <Accordion>
                <template #head>
                    <div class="font-semibold text-xl sm:text-2xl mb-4">
                        {{ replaceEnumUnderscores(workOrderType) }}
                    </div>
                </template>

                <Table
                    :data-table="dataTable"
                    :per-page-options="[5, 10, 15, 20, 50]"
                    :global-search="false"
                    :selected-row-indexes="[form.vehicleable_id]"
                    :selected-row-column="'id'"
                >
                    <template #cell(make.name)="{ value, item }">
                        <div class="flex gap-1.5">
                            {{ item.make?.name }}
                        </div>
                    </template>

                    <template #cell(vehicleModel.name)="{ value, item }">
                        <div class="flex gap-1.5">
                            {{ item.vehicle_model?.name }}
                        </div>
                    </template>

                    <template #cell(created_at)="{ value, item }">
                        <div class="flex gap-1.5">
                            {{ dateTimeToLocaleString(value) }}
                        </div>
                    </template>

                    <template #cell(updated_at)="{ value, item }">
                        <div class="flex gap-1.5">
                            {{ dateTimeToLocaleString(value) }}
                        </div>
                    </template>

                    <template #cell(action)="{ value, item }">
                        <div class="flex gap-1.5">
                            <component
                                :is="workOrderTypeComponentMap[workOrderType]"
                                v-if="workOrderType"
                                :workflow-processes="workflowProcesses"
                                :item="item"
                            />
                        </div>
                    </template>
                </Table>
            </Accordion>
        </div>

        <div class="flex justify-center">
            <div class="w-full max-w-[1721px]">
                <GeneralInformation
                    :form="form"
                    :work-order="workOrder"
                    :owner-props="{
                        mainCompanyUsers: mainCompanyUsers,
                        pendingOwnerships: pendingOwnerships,
                    }"
                />

                <InternalRemarks
                    :internal-remarks="workOrder.internal_remarks"
                    :form="form"
                    :main-company-roles="mainCompanyRoles"
                    :main-company-users="mainCompanyUsers"
                />

                <Tasks
                    :work-order="workOrder"
                    :main-company-users="mainCompanyUsers"
                />

                <div
                    class="relative rounded-lg border border-[#E9E7E7] shadow-sm bg-white py-4 sm:py-6 px-4 mt-4"
                >
                    <div class="font-semibold text-xl sm:text-2xl mb-4">
                        {{ __("Document") }}
                    </div>

                    <div
                        class="grid sm:grid-cols-2 xl:grid-cols-4 gap-5 gap-y-0"
                    >
                        <InputFile
                            id="files"
                            v-model="form.files"
                            :files="files.files"
                            :text="__('Work-order files')"
                            text-classes="py-14"
                        />
                    </div>
                </div>
            </div>
        </div>

        <ResetSaveButtons
            :processing="form.processing"
            @reset="form.reset()"
            @save="save"
        />
    </AppLayout>
</template>

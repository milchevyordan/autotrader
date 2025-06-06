<script setup lang="ts">
import { Head, useForm, usePage } from "@inertiajs/vue3";
import { computed, inject, onMounted, Ref, ref, watch } from "vue";

import Accordion from "@/components/Accordion.vue";
import Header from "@/components/Header.vue";
import InputFile from "@/components/html/InputFile.vue";
import InternalRemarks from "@/components/html/InternalRemarks.vue";
import ResetSaveButtons from "@/components/html/ResetSaveButtons.vue";
import GeneralInformation from "@/components/work-orders/GeneralInformation.vue";
import Table from "@/data-table/Table.vue";
import { DataTable, Multiselect } from "@/data-table/types";
import { WorkOrderType } from "@/enums/WorkOrderType";
import IconMinus from "@/icons/Minus.vue";
import IconPlus from "@/icons/Plus.vue";
import AppLayout from "@/layouts/AppLayout.vue";
import { workOrdersFormRules } from "@/rules/work-orders-form-rules";
import {
    GlobalInputErrors,
    Role,
    User,
    WorkOrderForm,
    Workorderable,
    WorkflowProcess,
} from "@/types";
import {
    dateTimeToLocaleString,
    findEnumKeyByValue,
    replaceEnumUnderscores,
    workOrderTypeComponentMap,
} from "@/utils";
import { validate } from "@/validations";

const props = defineProps<{
    dataTable: DataTable<Workorderable>;
    mainCompanyRoles?: Multiselect<Role>;
    mainCompanyUsers: Multiselect<User>;
    workflowProcesses?: Multiselect<WorkflowProcess>;
}>();

const form = useForm<WorkOrderForm>({
    id: null!,
    owner_id: usePage().props.auth.user.id,
    status: null!,
    type: null!,
    total_price: null!,
    vehicleable_id: null!,
    files: [],
    internal_remark_user_ids: [],
    internal_remark_role_ids: [],
    internal_remark: null!,
});

const tableData = ref<DataTable<Workorderable>>(props.dataTable);

onMounted(() => {
    form.type = Number(new URLSearchParams(window.location.search).get("type"));
    form.vehicleable_id = Number(
        new URLSearchParams(window.location.search).get("filter[id]")
    );
});

watch(
    () => props.dataTable,
    (newValue) => {
        tableData.value.data = newValue.data;
        tableData.value.paginator = newValue.paginator;
    }
);

const addVehicleable = (item: Workorderable) => {
    form.vehicleable_id = item.id;
    tableData.value.data = [item] as Workorderable[];
};

const removeVehicleable = () => {
    form.vehicleable_id = null!;
    if (props.dataTable) {
        tableData.value.data = props.dataTable.data;
        tableData.value.paginator = props.dataTable.paginator;
    }
};

const save = () => {
    validate(form, workOrdersFormRules);

    return new Promise<void>((resolve, reject) => {
        form.post(route("work-orders.store"), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                form.reset("files", "internal_remark");
                resolve();
            },
            onError: () => {
                reject(new Error("Error, during update"));
            },
        });
    });
};

const inputErrors = inject<Ref<GlobalInputErrors>>("globalInputErrors");

const errorMessage = computed(() => {
    const errorMessages = inputErrors?.value?.errorMessages ?? {};
    return errorMessages["vehicleable_id"] ?? null;
});

const workOrderType = computed(() =>
    findEnumKeyByValue(WorkOrderType, form.type)
);
</script>

<template>
    <Head :title="__('Work Order')" />

    <AppLayout>
        <Header :text="__('Work Order')" />

        <div
            v-if="form.type && tableData"
            class="relative rounded-lg border border-[#E9E7E7] shadow-sm bg-white py-4 sm:py-6 px-4 mt-4"
        >
            <Accordion>
                <template #head>
                    <div class="font-semibold text-xl sm:text-2xl mb-4">
                        {{ replaceEnumUnderscores(workOrderType) }}
                    </div>
                </template>

                <Table
                    :data-table="tableData"
                    :per-page-options="[5, 10, 15, 20, 50]"
                    :global-search="true"
                    :advanced-filters="true"
                    :selected-row-indexes="[form.vehicleable_id]"
                    :selected-row-column="'id'"
                >
                    <template #additionalContent>
                        <div class="w-full flex gap-2">
                            <div
                                v-if="errorMessage"
                                class="text-red-500 text-sm mt-0.5 ml-0.5"
                            >
                                {{ __(errorMessage) }}
                            </div>
                        </div>
                    </template>

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
                            <div
                                v-if="form.vehicleable_id == item.id"
                                class="flex gap-1.5"
                            >
                                <button
                                    class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                                    @click="removeVehicleable()"
                                >
                                    <IconMinus
                                        classes="w-4 h-4 text-slate-600"
                                    />
                                </button>
                            </div>
                            <div v-else>
                                <button
                                    class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                                    @click="addVehicleable(item)"
                                >
                                    <IconPlus
                                        classes="w-4 h-4 text-slate-600"
                                    />
                                </button>
                            </div>

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
                    :owner-props="{
                        mainCompanyUsers: mainCompanyUsers,
                    }"
                />

                <InternalRemarks
                    :form="form"
                    :main-company-roles="mainCompanyRoles"
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
                            :files="[]"
                            :text="__('Work order files')"
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

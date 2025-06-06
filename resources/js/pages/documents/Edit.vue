<script setup lang="ts">
import { Head, router, useForm } from "@inertiajs/vue3";
import { computed, ref, watch } from "vue";

import Accordion from "@/components/Accordion.vue";
import Header from "@/components/Header.vue";
import ChangeLogs from "@/components/html/ChangeLogs.vue";
import Communication from "@/components/html/Communication.vue";
import IndexStatus from "@/components/html/IndexStatus.vue";
import InternalRemarks from "@/components/html/InternalRemarks.vue";
import ResetSaveButtons from "@/components/html/ResetSaveButtons.vue";
import Table from "@/data-table/Table.vue";
import { DataTable, Multiselect } from "@/data-table/types";
import { DocumentableType } from "@/enums/DocumentableType";
import { DocumentStatus } from "@/enums/DocumentStatus";
import { ImportEuOrWorldType } from "@/enums/ImportEuOrWorldType";
import { ImportOrOriginType } from "@/enums/ImportOrOriginType";
import { PurchaseOrderStatus } from "@/enums/PurchaseOrderStatus";
import { QuoteStatus } from "@/enums/QuoteStatus";
import { SalesOrderStatus } from "@/enums/SalesOrderStatus";
import { TransportOrderStatus } from "@/enums/TransportOrderStatus";
import { WorkOrderType } from "@/enums/WorkOrderType";
import IconMinus from "@/icons/Minus.vue";
import IconPlus from "@/icons/Plus.vue";
import AppLayout from "@/layouts/AppLayout.vue";
import ConnectedModules from "@/pages/documents/partials/ConnectedModules.vue";
import DocumentLines from "@/pages/documents/partials/DocumentLines.vue";
import GeneralInformation from "@/pages/documents/partials/GeneralInformation.vue";
import Stepper from "@/pages/documents/partials/Stepper.vue";
import VehiclesTable from "@/pages/documents/partials/VehiclesTable.vue";
import { $can } from "@/plugins/permissions";
import { documentFormRules } from "@/rules/document-form-rules";
import {
    Company,
    Document,
    DocumentForm,
    Role,
    User,
    Vehicle,
    DocumentLine,
    Documentable,
    Ownership,
    PreOrderVehicle,
    ServiceVehicle,
    SalesOrder,
    ServiceOrder,
    WorkOrder,
    WorkflowProcess,
    WeekPicker,
} from "@/types";
import {
    withFlash,
    dateTimeToLocaleString,
    replaceEnumUnderscores,
    findEnumKeyByValue,
    resetOwnerId,
    documentableTypeComponentMap,
    updateDocumentLines,
    calculateDocumentPrices,
    documentableTypeStatusEnumMap,
} from "@/utils";
import { validate } from "@/validations";

const props = defineProps<{
    document: Document;
    selectedDocumentables: any;
    selectedDocumentableIds: Array<number>;
    companies: Multiselect<Company>;
    mainCompanyUsers: Multiselect<User>;
    mainCompanyRoles?: Multiselect<Role>;
    acceptedOwnership: Ownership;
    pendingOwnerships: Ownership[];
    dataTable: DataTable<
        | PreOrderVehicle
        | Vehicle
        | ServiceVehicle
        | SalesOrder
        | ServiceOrder
        | WorkOrder
    >;
    tableVehicles?: Vehicle[];
    customers: Multiselect<User>;
    workflowProcesses?: Multiselect<WorkflowProcess>;
}>();

const formDisabled = computed(() => {
    return (
        props.document.status !== DocumentStatus.Concept && !$can("super-edit")
    );
});

const form = useForm<DocumentForm>({
    _method: "put",
    id: props.document.id,
    owner_id: props.acceptedOwnership?.user_id,
    customer_company_id: props.document.customer_company_id,
    customer_id: props.document.customer_id,
    documentable_type: props.document.documentable_type,
    paid_at: props.document.paid_at,
    payment_condition: props.document.payment_condition,
    payment_condition_free_text: props.document.payment_condition_free_text,
    notes: props.document.notes,
    total_price_exclude_vat: props.document.total_price_exclude_vat,
    total_vat: props.document.total_vat,
    total_price_include_vat: props.document.total_price_include_vat,
    date: props.document.date,
    documentables: props.selectedDocumentables ?? [],
    documentableIds: props.selectedDocumentableIds ?? [],
    lines: props.document.lines,
    internal_remark_user_ids: [],
    internal_remark_role_ids: [],
    internal_remark: null!,
});

const save = async (only?: Array<string>) => {
    validate(form, documentFormRules);

    return new Promise<void>((resolve, reject) => {
        form.post(route("documents.update", props.document.id as number), {
            preserveScroll: true,
            preserveState: true,
            forceFormData: true, // preserves all form data
            only: withFlash(only),
            onSuccess: () => {
                form.reset("internal_remark");

                resetOwnerId(form);

                resolve();
            },
            onError: () => {
                reject(new Error("Error, during update"));
            },
        });
    });
};

const flattenedVehicles = ref<Record<number, WeekPicker> | undefined>(null!);

const addDocumentable = async (item: any) => {
    form.documentableIds.push(item.id);
    form.documentables.push(item);

    updateDocumentLines(form);

    calculateDocumentPrices(form);

    if (!documentIsSales.value) {
        return;
    }

    flattenedVehicles.value = props.dataTable.data
        .flatMap((order) =>
            (order as SalesOrder).vehicles?.map((vehicle) => ({
                [vehicle.id]: vehicle.pivot.delivery_week,
            }))
        )
        .reduce((acc, obj) => ({ ...acc, ...obj }), {});

    const vehicleIds = form.documentables.flatMap((documentable: SalesOrder) =>
        documentable.vehicles?.map((vehicle) => vehicle.id)
    );
    const thisRoute = route();
    const params = { ...thisRoute.params, vehicle_ids: vehicleIds };

    await new Promise((resolve, reject) => {
        router.visit(route("documents.edit", params as any), {
            only: ["tableVehicles"],
            preserveState: true,
            preserveScroll: true,
            onSuccess: resolve,
            onError: reject,
        });
    });
};

const removeDocumentable = async (id: number) => {
    const formIndex = form.documentableIds.indexOf(id);

    form.lines = form.lines.filter(
        (line: DocumentLine) => line.documentable_id !== id
    );

    form.documentables = form.documentables.filter(
        (documentable: Documentable) => documentable.id !== id
    );

    if (formIndex !== -1) {
        form.documentableIds.splice(formIndex, 1);
    }

    calculateDocumentPrices(form);

    if (!documentIsSales.value) {
        return;
    }

    const vehicleIds = form.documentables.flatMap((documentable: SalesOrder) =>
        documentable.vehicles?.map((vehicle) => vehicle.id)
    );
    const thisRoute = route();
    const params = { ...thisRoute.params, vehicle_ids: vehicleIds };

    await new Promise((resolve, reject) => {
        router.visit(route("documents.edit", params as any), {
            only: ["tableVehicles"],
            preserveState: true,
            preserveScroll: true,
            onSuccess: resolve,
            onError: reject,
        });
    });
};

watch(
    () => form.lines,
    () => {
        calculateDocumentPrices(form);
    },
    {
        deep: true,
    }
);

const documentIsSales = computed(() => {
    return [
        DocumentableType.Sales_order,
        DocumentableType.Sales_order_down_payment,
    ].includes(form.documentable_type);
});

const documentableType = computed(
    () => findEnumKeyByValue(DocumentableType, form.documentable_type) as string
);
</script>

<template>
    <Head :title="__('Invoice')" />

    <AppLayout>
        <Header :text="__('Invoice')" />

        <Stepper :document="document" :form-is-dirty="form.isDirty" />

        <div
            class="relative rounded-lg border border-[#E9E7E7] shadow-sm bg-white py-4 sm:py-6 px-4 mt-4"
        >
            <Accordion>
                <template #head>
                    <div class="font-semibold text-xl sm:text-2xl mb-4">
                        {{
                            replaceEnumUnderscores(
                                findEnumKeyByValue(
                                    DocumentableType,
                                    props.document.documentable_type
                                )
                            )
                        }}
                    </div>
                </template>

                <Table
                    :data-table="dataTable"
                    :per-page-options="[5, 10, 15, 20, 50]"
                    :global-search="true"
                    :selected-row-indexes="form.documentableIds"
                    :selected-row-column="'id'"
                >
                    <template #cell(status)="{ value, item }">
                        {{
                            replaceEnumUnderscores(
                                findEnumKeyByValue(
                                    documentableTypeStatusEnumMap[
                                        documentableType
                                    ],
                                    value
                                )
                            )
                        }}
                    </template>

                    <template #cell(type_of_sale)="{ value, item }">
                        {{
                            replaceEnumUnderscores(
                                findEnumKeyByValue(ImportOrOriginType, value)
                            )
                        }}
                    </template>

                    <template #cell(type)="{ value, item }">
                        <div v-if="form.type == DocumentableType.Work_order">
                            {{
                                replaceEnumUnderscores(
                                    findEnumKeyByValue(WorkOrderType, value)
                                )
                            }}
                        </div>
                        <div v-else>
                            {{
                                replaceEnumUnderscores(
                                    findEnumKeyByValue(
                                        ImportEuOrWorldType,
                                        value
                                    )
                                )
                            }}
                        </div>
                    </template>

                    <template #cell(purchaseOrder)="{ value, item }">
                        <IndexStatus
                            v-if="item.purchase_order[0]"
                            :item="item.purchase_order[0]"
                            :status-enum="PurchaseOrderStatus"
                            module="purchase-order"
                            :text="__('Purchase order')"
                        />
                    </template>

                    <template #cell(salesOrder)="{ value, item }">
                        <IndexStatus
                            v-if="item.sales_order[0]"
                            :item="item.sales_order[0]"
                            :status-enum="SalesOrderStatus"
                            module="sales-order"
                            :text="__('Sales order')"
                        />
                    </template>

                    <template #cell(transportOrderInbound)="{ value, item }">
                        <IndexStatus
                            v-if="item.transport_order_inbound[0]"
                            :item="item.transport_order_inbound[0]"
                            :status-enum="TransportOrderStatus"
                            module="transport-order"
                            :text="__('Transport order')"
                        />
                    </template>

                    <template #cell(transportOrderOutbound)="{ value, item }">
                        <IndexStatus
                            v-if="item.transport_order_outbound[0]"
                            :item="item.transport_order_outbound[0]"
                            :status-enum="TransportOrderStatus"
                            module="transport-order"
                            :text="__('Transport order')"
                        />
                    </template>

                    <template #cell(documents)="{ value, item }">
                        <IndexStatus
                            v-if="item.documents[0]"
                            :item="item.documents[0]"
                            :status-enum="DocumentStatus"
                            module="document"
                            :text="__('Document')"
                        />
                    </template>

                    <template #cell(quotes)="{ value, item }">
                        <IndexStatus
                            v-if="item.quotes.length"
                            :items="item.quotes"
                            :status-enum="QuoteStatus"
                            module="quote"
                            :multiple="true"
                            :text="__('Quotes')"
                        />
                    </template>

                    <template #cell(created_at)="{ value, item }">
                        {{ dateTimeToLocaleString(value) }}
                    </template>

                    <template #cell(updated_at)="{ value, item }">
                        {{ dateTimeToLocaleString(value) }}
                    </template>

                    <template #cell(action)="{ value, item }">
                        <div class="flex gap-1.5">
                            <button
                                v-if="
                                    document.status == DocumentStatus.Concept &&
                                    form.documentableIds.includes(item.id)
                                "
                                class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                                @click="removeDocumentable(item.id)"
                            >
                                <IconMinus classes="w-4 h-4 text-slate-600" />
                            </button>
                            <button
                                v-else-if="
                                    document.status == DocumentStatus.Concept
                                "
                                class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                                @click="addDocumentable(item)"
                            >
                                <IconPlus classes="w-4 h-4 text-slate-600" />
                            </button>

                            <component
                                :is="
                                    documentableTypeComponentMap[
                                        DocumentableType[
                                            document.documentable_type
                                        ]
                                    ]
                                "
                                v-if="document.documentable_type"
                                :item="item"
                                :workflow-processes="workflowProcesses"
                            />
                        </div>
                    </template>
                </Table>
            </Accordion>
        </div>

        <div
            v-if="tableVehicles && documentIsSales && form.documentables.length"
            class="relative rounded-lg border border-[#E9E7E7] shadow-sm bg-white py-4 sm:py-6 px-4 mt-4"
        >
            <Accordion>
                <template #head>
                    <div class="font-semibold text-xl sm:text-2xl mb-4">
                        {{ __("Vehicles") }}
                    </div>
                </template>

                <VehiclesTable
                    :flattened-vehicles="flattenedVehicles"
                    :workflow-processes="workflowProcesses"
                    :table-vehicles="tableVehicles"
                />
            </Accordion>
        </div>

        <div class="flex justify-center">
            <div class="w-full max-w-[1721px]">
                <GeneralInformation
                    :form="form"
                    :form-disabled="formDisabled"
                    :owner-props="{
                        mainCompanyUsers: mainCompanyUsers,
                        pendingOwnerships: pendingOwnerships,
                    }"
                    :document="document"
                    :companies="companies"
                    :customers="customers"
                />

                <DocumentLines
                    :lines="form.lines"
                    :documentable-ids="form.documentableIds"
                    :form-disabled="formDisabled"
                />

                <InternalRemarks
                    :internal-remarks="document.internal_remarks"
                    :form="form"
                    :main-company-roles="mainCompanyRoles"
                    :main-company-users="mainCompanyUsers"
                />

                <Communication
                    :mails="document.mails"
                    :files="document.files"
                />

                <ChangeLogs :change-logs="document.change_logs" />

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-8">
                    <ConnectedModules :document="document" />
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

<script setup lang="ts">
import { Head, router, useForm, usePage } from "@inertiajs/vue3";
import { computed, onMounted, ref, watch } from "vue";

import Accordion from "@/components/Accordion.vue";
import Header from "@/components/Header.vue";
import IndexStatus from "@/components/html/IndexStatus.vue";
import InternalRemarks from "@/components/html/InternalRemarks.vue";
import ResetSaveButtons from "@/components/html/ResetSaveButtons.vue";
import Table from "@/data-table/Table.vue";
import { DataTable, Multiselect } from "@/data-table/types";
import { DocumentableType } from "@/enums/DocumentableType";
import { DocumentStatus } from "@/enums/DocumentStatus";
import { ImportEuOrWorldType } from "@/enums/ImportEuOrWorldType";
import { ImportOrOriginType } from "@/enums/ImportOrOriginType";
import { PaymentCondition } from "@/enums/PaymentCondition";
import { PurchaseOrderStatus } from "@/enums/PurchaseOrderStatus";
import { QuoteStatus } from "@/enums/QuoteStatus";
import { SalesOrderStatus } from "@/enums/SalesOrderStatus";
import { TransportOrderStatus } from "@/enums/TransportOrderStatus";
import { WorkOrderType } from "@/enums/WorkOrderType";
import IconMinus from "@/icons/Minus.vue";
import IconPlus from "@/icons/Plus.vue";
import AppLayout from "@/layouts/AppLayout.vue";
import DocumentLines from "@/pages/documents/partials/DocumentLines.vue";
import GeneralInformation from "@/pages/documents/partials/GeneralInformation.vue";
import VehiclesTable from "@/pages/documents/partials/VehiclesTable.vue";
import { documentFormRules } from "@/rules/document-form-rules";
import {
    Company,
    Documentable,
    DocumentForm,
    DocumentLine,
    PreOrderVehicle,
    Role,
    SalesOrder,
    ServiceOrder,
    ServiceVehicle,
    User,
    Vehicle,
    WeekPicker,
    WorkflowProcess,
    WorkOrder,
} from "@/types";
import {
    dateTimeToLocaleString,
    findEnumKeyByValue,
    documentableTypeComponentMap,
    documentableTypeStatusEnumMap,
    replaceEnumUnderscores,
    updateDocumentLines,
    calculateDocumentPrices,
} from "@/utils";
import { validate } from "@/validations";

const props = defineProps<{
    companies: Multiselect<Company>;
    queryId: number;
    dataTable: DataTable<
        | PreOrderVehicle
        | Vehicle
        | ServiceVehicle
        | SalesOrder
        | ServiceOrder
        | WorkOrder
    >;
    tableVehicles?: Vehicle[];
    lines?: any;
    customers: Multiselect<User>;
    mainCompanyRoles?: Multiselect<Role>;
    mainCompanyUsers: Multiselect<User>;
    workflowProcesses?: Multiselect<WorkflowProcess>;
}>();

onMounted(() => {
    if (props.queryId && props.dataTable && props.dataTable.data[0]) {
        const params = new URLSearchParams(window.location.search);

        form.documentable_type = Number(params.get("documentable_type"));
        if (
            form.documentable_type == DocumentableType.Sales_order_down_payment
        ) {
            form.payment_condition = PaymentCondition.Payment_immediately;
        } else {
            const paymentCondition = params.get("payment_condition");
            if (paymentCondition !== null) {
                form.payment_condition = Number(paymentCondition);
            }
        }

        const customerCompanyId = params.get("customer_company_id");
        if (customerCompanyId !== null) {
            form.customer_company_id = Number(customerCompanyId);
        }

        const customerId = params.get("customer_id");
        if (customerId !== null) {
            form.customer_id = Number(customerId);
        }

        addDocumentable(props.dataTable.data[0]);
    }
});

const form = useForm<DocumentForm>({
    id: null!,
    owner_id: usePage().props.auth.user.id,
    customer_company_id: null!,
    customer_id: null!,
    documentable_type: null!,
    paid_at: null!,
    payment_condition: null!,
    payment_condition_free_text: null!,
    notes: null!,
    total_price_exclude_vat: null!,
    total_price_include_vat: null!,
    total_vat: null!,
    date: null!,
    documentables: [],
    documentableIds: [],
    lines: [],
    internal_remark_user_ids: [],
    internal_remark_role_ids: [],
    internal_remark: null!,
});

const save = () => {
    validate(form, documentFormRules);

    form.post(route("documents.store"), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset("internal_remark");
        },
        onError: () => {},
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
        router.visit(route("documents.create", params), {
            only: ["tableVehicles"],
            preserveState: true,
            preserveScroll: true,
            onSuccess: resolve,
            onError: reject,
        });
    });
};

const documentIsSales = computed(() => {
    return [
        DocumentableType.Sales_order,
        DocumentableType.Sales_order_down_payment,
    ].includes(form.documentable_type);
});

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
        router.visit(route("documents.create", params), {
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

const documentableType = computed(
    () => findEnumKeyByValue(DocumentableType, form.documentable_type) as string
);
</script>

<template>
    <Head :title="__('Invoice')" />

    <AppLayout>
        <Header :text="__('Invoice')" />

        <div
            v-if="form.documentable_type && dataTable"
            class="relative rounded-lg border border-[#E9E7E7] shadow-sm bg-white py-4 sm:py-6 px-4 mt-4"
        >
            <Accordion>
                <template #head>
                    <div class="font-semibold text-xl sm:text-2xl mb-4">
                        {{ replaceEnumUnderscores(documentableType) }}
                    </div>
                </template>

                <Table
                    :data-table="dataTable"
                    :per-page-options="[5, 10, 15, 20, 50]"
                    :global-search="true"
                    :advanced-filters="false"
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

                    <template #cell(type_of_sale)="{ value, item }">
                        {{
                            replaceEnumUnderscores(
                                findEnumKeyByValue(ImportOrOriginType, value)
                            )
                        }}
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
                            <div
                                v-if="form.documentableIds.includes(item.id)"
                                class="flex gap-1.5"
                            >
                                <button
                                    class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                                    @click="removeDocumentable(item.id)"
                                >
                                    <IconMinus
                                        classes="w-4 h-4 text-slate-600"
                                    />
                                </button>
                            </div>
                            <div v-else>
                                <button
                                    class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                                    @click="addDocumentable(item)"
                                >
                                    <IconPlus
                                        classes="w-4 h-4 text-slate-600"
                                    />
                                </button>
                            </div>

                            <component
                                :is="
                                    documentableTypeComponentMap[
                                        documentableType
                                    ]
                                "
                                v-if="documentableType"
                                :item="item"
                                :workflow-processes="workflowProcesses"
                            />
                        </div>
                    </template>
                </Table>
            </Accordion>
        </div>

        <div
            v-if="tableVehicles && documentIsSales"
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
                    :owner-props="{
                        mainCompanyUsers: mainCompanyUsers,
                    }"
                    :companies="companies"
                    :customers="customers"
                />

                <DocumentLines
                    :lines="form.lines"
                    :documentable-ids="form.documentableIds"
                />

                <InternalRemarks
                    :form="form"
                    :main-company-roles="mainCompanyRoles"
                    :main-company-users="mainCompanyUsers"
                />
            </div>
        </div>

        <ResetSaveButtons
            :processing="form.processing"
            @reset="form.reset()"
            @save="save"
        />
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, router, useForm } from "@inertiajs/vue3";
import { computed, ref } from "vue";

import Accordion from "@/components/Accordion.vue";
import Header from "@/components/Header.vue";
import Communication from "@/components/html/Communication.vue";
import WeekRangePicker from "@/components/html/WeekRangePicker.vue";
import InputFile from "@/components/html/InputFile.vue";
import InternalRemarks from "@/components/html/InternalRemarks.vue";
import ResetSaveButtons from "@/components/html/ResetSaveButtons.vue";
import VehicleLinks from "@/components/html/VehicleLinks.vue";
import GeneralInformation from "@/components/purchase-orders/GeneralInformation.vue";
import Stepper from "@/components/purchase-orders/Stepper.vue";
import SummaryFinancialInformation from "@/components/purchase-orders/SummaryFinancialInformation.vue";
import Table from "@/data-table/Table.vue";
import { DataTable, Multiselect } from "@/data-table/types";
import { Coc } from "@/enums/Coc";
import { Currency } from "@/enums/Currency";
import { PurchaseOrderStatus } from "@/enums/PurchaseOrderStatus";
import { SupplierOrIntermediary } from "@/enums/SupplierOrIntermediary";
import { setFlashMessages } from "@/globals";
import CurrencyExchange from "@/icons/CurrencyExchange.vue";
import IconMinus from "@/icons/Minus.vue";
import IconPlus from "@/icons/Plus.vue";
import AppLayout from "@/layouts/AppLayout.vue";
import { $can } from "@/plugins/permissions";
import { purchaseOrdersFormRules } from "@/rules/purchase-orders-form-rules";
import {
    CompanyDefaults,
    OrderFiles,
    Ownership,
    SelectInput,
    UpdateStatusForm,
    Vehicle,
    WorkflowProcess,
} from "@/types";
import { Company, PurchaseOrderForm, Role, User } from "@/types";
import {
    replaceEnumUnderscores,
    withFlash,
    findEnumKeyByValue,
    resetOwnerId,
    changeStatus,
} from "@/utils";
import { validate } from "@/validations";

const props = defineProps<{
    acceptedOwnership: Ownership;
    pendingOwnerships: Ownership[];
    purchaseOrder: PurchaseOrderForm;
    selectedVehicles: Vehicle[];
    selectedVehicleIds: Array<number>;
    files: OrderFiles;
    companies: Multiselect<Company>;
    companyDefaults?: CompanyDefaults;
    suppliers: Multiselect<User>;
    intermediaries: Multiselect<User>;
    purchasers: Multiselect<User>;
    dataTable: DataTable<Vehicle>;
    workflowProcesses?: Multiselect<WorkflowProcess>;
    mainCompanyRoles?: Multiselect<Role>;
    mainCompanyUsers: Multiselect<User>;
}>();

const vehicles = ref<Vehicle[]>(props.selectedVehicles);

const formDisabled = computed(() => {
    return (
        props.purchaseOrder.status !== PurchaseOrderStatus.Concept &&
        !$can("super-edit")
    );
});

const form = useForm<PurchaseOrderForm>({
    _method: "put",
    id: props.purchaseOrder.id,
    owner_id: props.acceptedOwnership?.user_id,
    supplier_company_id: props.purchaseOrder.supplier_company_id,
    supplier_id: props.purchaseOrder.supplier_id,
    intermediary_company_id: props.purchaseOrder.intermediary_company_id,
    intermediary_id: props.purchaseOrder.intermediary_id,
    purchaser_id: props.purchaseOrder.purchaser_id,
    document_from_type: props.purchaseOrder.document_from_type,
    papers: props.purchaseOrder.papers,
    payment_condition: props.purchaseOrder.payment_condition,
    payment_condition_free_text:
        props.purchaseOrder.payment_condition_free_text,
    status: props.purchaseOrder.status,
    type: props.purchaseOrder.type,
    currency_po: props.purchaseOrder.currency_po,
    vat_percentage: props.purchaseOrder.vat_percentage,
    down_payment_amount: props.purchaseOrder.down_payment_amount,
    total_payment_amount: props.purchaseOrder.total_payment_amount,
    sales_restriction: props.purchaseOrder.sales_restriction,
    contact_notes: props.purchaseOrder.contact_notes,
    transport_included: props.purchaseOrder.transport_included,
    vat_deposit: props.purchaseOrder.vat_deposit,
    vat_deposit_amount: props.purchaseOrder.vat_deposit_amount,
    down_payment: props.purchaseOrder.down_payment,
    total_purchase_price: props.purchaseOrder.total_purchase_price,
    total_purchase_price_eur: props.purchaseOrder.total_purchase_price_eur,
    total_fee_intermediate_supplier:
        props.purchaseOrder.total_fee_intermediate_supplier,
    total_purchase_price_exclude_vat:
        props.purchaseOrder.total_purchase_price_exclude_vat,
    total_transport: props.purchaseOrder.total_transport,
    total_vat: props.purchaseOrder.total_vat,
    total_bpm: props.purchaseOrder.total_bpm,
    total_purchase_price_include_vat:
        props.purchaseOrder.total_purchase_price_include_vat,
    currency_rate: props.purchaseOrder.currency_rate,

    viesFiles: [],
    creditCheckFiles: [],
    contractSignedFiles: [],
    files: [],

    vehicleIds: props.selectedVehicleIds ?? [],
    internal_remark_user_ids: [],
    internal_remark_role_ids: [],
    internal_remark: null!,
});

const updateStatusForm = useForm<UpdateStatusForm>({
    _method: "patch",
    id: props.purchaseOrder.id,
    status: null!,
    route: "purchase-orders",
});

const handleContractSignedUpload = async () => {
    await save();

    await changeStatus(
        updateStatusForm,
        PurchaseOrderStatus.Uploaded_signed_contract
    );
};

const updateForm = async (newFormValue: SelectInput) => {
    form[newFormValue.name] = newFormValue.value;

    switch (newFormValue.name) {
        case "supplier_company_id":
            await new Promise((resolve, reject) => {
                router.reload({
                    data: {
                        supplier_company_id: newFormValue.value,
                        company_id: newFormValue.value,
                    },
                    only: ["suppliers", "dataTable", "companyDefaults"],
                    onSuccess: resolve,
                    onError: reject,
                });
            });

            if (Object.keys(props.suppliers).length == 1) {
                form.supplier_id = Object.values(props.suppliers)[0];
            }

            if (form.document_from_type == SupplierOrIntermediary.Supplier) {
                form.currency_po = props.companyDefaults?.default_currency;
                form.type = props.companyDefaults?.purchase_type;
            }

            break;
    }
};

const save = async (only?: Array<string>) => {
    validate(form, purchaseOrdersFormRules);

    return new Promise<void>((resolve, reject) => {
        form.post(
            route("purchase-orders.update", props.purchaseOrder.id as number),
            {
                preserveScroll: true,
                preserveState: true,
                forceFormData: true, // preserves all form data
                only: withFlash(only),
                onSuccess: () => {
                    form.reset(
                        "internal_remark",
                        "viesFiles",
                        "creditCheckFiles",
                        "contractSignedFiles",
                        "files"
                    );

                    resetOwnerId(form);

                    resolve();
                },
                onError: () => {
                    reject(new Error("Error, during update"));
                },
            }
        );
    });
};

const addVehicle = (item: Vehicle) => {
    vehicles.value.push(item);
    form.vehicleIds.push(item.id);

    if (vehicles.value.length == 1 && !form.supplier_company_id) {
        updateForm({
            name: "supplier_company_id",
            value: item.supplier_company_id,
        });
    }
};

const saveAndSubmit = async () => {
    await save();

    if (form.vehicleIds.length == 0) {
        setFlashMessages({
            error: "Not selected any vehicles, need to select at least one Vehicle",
        });
    }

    await changeStatus(updateStatusForm, PurchaseOrderStatus.Submitted);
};

const removeVehicle = (item: Vehicle) => {
    const formIndex = form.vehicleIds.indexOf(item.id);
    const vehicleIndex = vehicles.value.findIndex((obj) => obj.id === item.id);

    vehicles.value.splice(vehicleIndex, 1);

    if (formIndex !== -1) {
        form.vehicleIds.splice(formIndex, 1);
    }
};
</script>

<template>
    <Head :title="__('Purchase Order')" />

    <AppLayout>
        <Header :text="__('Purchase Order')" />

        <Stepper
            :purchase-order="purchaseOrder"
            :selected-vehicle-ids="selectedVehicleIds"
            :vehicles="selectedVehicles"
            :form-is-dirty="form.isDirty"
            :companies="companies"
            :files="files"
            @save-and-submit="saveAndSubmit"
        />

        <div
            class="relative rounded-lg border border-[#E9E7E7] shadow-sm bg-white py-4 sm:py-6 px-4 mt-4"
        >
            <Accordion>
                <template #head>
                    <div class="font-semibold text-xl sm:text-2xl mb-4">
                        {{ __("Vehicles") }}
                    </div>
                </template>

                <Table
                    :data-table="dataTable"
                    :per-page-options="[5, 10, 15, 20, 50]"
                    :global-search="true"
                    :selected-row-indexes="form.vehicleIds"
                    :selected-row-column="'id'"
                >
                    <template #cell(make.name)="{ value, item }">
                        {{ item.make?.name }}
                    </template>

                    <template #cell(vehicleModel.name)="{ value, item }">
                        {{ item.vehicle_model?.name }}
                    </template>

                    <template #cell(engine.name)="{ value, item }">
                        {{ item.engine?.name }}
                    </template>

                    <template
                        #cell(calculation.original_currency)="{ value, item }"
                    >
                        <div class="flex gap-1.5">
                            <CurrencyExchange
                                v-if="
                                    item.calculation.original_currency &&
                                    item.calculation.original_currency !=
                                        form.currency_po
                                "
                                classes="w-4 h-4"
                            />

                            {{
                                __(
                                    replaceEnumUnderscores(
                                        findEnumKeyByValue(
                                            Currency,
                                            Number(
                                                item.calculation
                                                    .original_currency
                                            )
                                        )
                                    )
                                )
                            }}
                        </div>
                    </template>

                    <template
                        #cell(expected_date_of_availability_from_supplier)="{
                            value,
                            item,
                        }"
                    >
                        <div class="flex gap-1.5 min-w-[270px]">
                            <WeekRangePicker
                                :model-value="value"
                                name="expected_date_of_availability_from_supplier"
                                disabled
                            />
                        </div>
                    </template>

                    <template #cell(coc)="{ value, item }">
                        <div class="flex gap-1.5">
                            {{ findEnumKeyByValue(Coc, value) }}
                        </div>
                    </template>

                    <template #cell(action)="{ value, item }">
                        <div class="flex gap-1.5">
                            <div
                                v-if="
                                    purchaseOrder.status ==
                                    PurchaseOrderStatus.Concept
                                "
                                class="flex gap-1.5"
                            >
                                <div
                                    v-if="form.vehicleIds.includes(item.id)"
                                    class="flex gap-1.5"
                                >
                                    <button
                                        class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                                        @click="removeVehicle(item)"
                                    >
                                        <IconMinus
                                            classes="w-4 h-4 text-slate-600"
                                        />
                                    </button>
                                </div>
                                <div v-else>
                                    <button
                                        class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                                        @click="addVehicle(item)"
                                    >
                                        <IconPlus
                                            classes="w-4 h-4 text-slate-600"
                                        />
                                    </button>
                                </div>
                            </div>

                            <VehicleLinks
                                :item="item"
                                :workflow-processes="workflowProcesses"
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
                    :form-disabled="formDisabled"
                    :owner-props="{
                        mainCompanyUsers: mainCompanyUsers,
                        pendingOwnerships: pendingOwnerships,
                    }"
                    :suppliers="suppliers"
                    :intermediaries="intermediaries"
                    :purchasers="purchasers"
                    :companies="companies"
                    :company-defaults="companyDefaults"
                    :purchase-order="purchaseOrder"
                    @form-updated="updateForm"
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
                            id="upload-vies"
                            v-model="form.viesFiles"
                            :files="files.viesFiles"
                            :text="__('Upload VIES files')"
                            text-classes="py-14"
                            :delete-disabled="formDisabled"
                        />

                        <InputFile
                            id="credit-check-supplier"
                            v-model="form.creditCheckFiles"
                            :files="files.creditCheckFiles"
                            :text="__('Upload credit check supplier')"
                            text-classes="py-14"
                            :delete-disabled="formDisabled"
                        />

                        <InputFile
                            id="order-contract-signed"
                            v-model="form.contractSignedFiles"
                            :files="files.contractSignedFiles"
                            :disabled="
                                props.purchaseOrder.status !=
                                PurchaseOrderStatus.Sent_to_supplier
                            "
                            :delete-disabled="formDisabled"
                            :text="__('Upload Purchase order contract signed')"
                            text-classes="py-14"
                            @change="handleContractSignedUpload"
                        />

                        <InputFile
                            v-model="form.files"
                            :files="files.files"
                            :text="__('General Purchase Order Documents')"
                            text-classes="py-14"
                            :delete-disabled="formDisabled"
                        />
                    </div>
                </div>

                <SummaryFinancialInformation
                    :vehicles="vehicles"
                    :form="form"
                    :form-disabled="formDisabled"
                />

                <InternalRemarks
                    :internal-remarks="purchaseOrder.internal_remarks"
                    :form="form"
                    :main-company-roles="mainCompanyRoles"
                    :main-company-users="mainCompanyUsers"
                />

                <Communication
                    :mails="purchaseOrder.mails"
                    :files="files.generatedPdf"
                />
            </div>
        </div>

        <ResetSaveButtons
            :processing="form.processing || updateStatusForm.processing"
            @reset="form.reset()"
            @save="save"
        />
    </AppLayout>
</template>

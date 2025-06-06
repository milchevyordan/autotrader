<script setup lang="ts">
import { Head, Link, useForm } from "@inertiajs/vue3";
import { computed, ref, watch } from "vue";

import Accordion from "@/components/Accordion.vue";
import Header from "@/components/Header.vue";
import ChangeLogs from "@/components/html/ChangeLogs.vue";
import Communication from "@/components/html/Communication.vue";
import InputFile from "@/components/html/InputFile.vue";
import InternalRemarks from "@/components/html/InternalRemarks.vue";
import ResetSaveButtons from "@/components/html/ResetSaveButtons.vue";
import GeneralInformation from "@/components/pre-orders/GeneralInformation.vue";
import Stepper from "@/components/pre-orders/Stepper.vue";
import SummaryFinancialInformation from "@/components/pre-orders/SummaryFinancialInformation.vue";
import Table from "@/data-table/Table.vue";
import { DataTable, Multiselect } from "@/data-table/types";
import { Currency } from "@/enums/Currency";
import { PreOrderStatus } from "@/enums/PreOrderStatus";
import IconMinus from "@/icons/Minus.vue";
import IconPencilSquare from "@/icons/PencilSquare.vue";
import IconPlus from "@/icons/Plus.vue";
import AppLayout from "@/layouts/AppLayout.vue";
import { $can } from "@/plugins/permissions";
import { preOrdersFormRules } from "@/rules/pre-orders-form-rules";
import {
    Company,
    PreOrderForm,
    OrderFiles,
    PreOrderVehicle,
    User,
    Role,
    Ownership,
    UpdateStatusForm,
    DatabaseFile,
} from "@/types";
import {
    calculatePreOrderPrices,
    changeStatus,
    dateTimeToLocaleString,
    resetOwnerId,
    withFlash,
} from "@/utils";
import { validate } from "@/validations";

const props = defineProps<{
    preOrder: PreOrderForm;
    files: OrderFiles;
    companies: Multiselect<Company>;
    defaultCurrencies: Record<number, Currency>;
    suppliers: Multiselect<User>;
    intermediaries: Multiselect<User>;
    purchasers: Multiselect<User>;
    dataTable: DataTable<PreOrderVehicle>;
    mainCompanyRoles?: Multiselect<Role>;
    mainCompanyUsers: Multiselect<User>;
    acceptedOwnership: Ownership;
    pendingOwnerships: Ownership[];
}>();

const form = useForm<PreOrderForm>({
    _method: "put",
    id: props.preOrder.id,
    owner_id: props.acceptedOwnership?.user_id,
    supplier_company_id: props.preOrder.supplier_company_id,
    supplier_id: props.preOrder.supplier_id,
    intermediary_company_id: props.preOrder.intermediary_company_id,
    intermediary_id: props.preOrder.intermediary_id,
    purchaser_id: props.preOrder.purchaser_id,
    pre_order_vehicle_id: props.preOrder.pre_order_vehicle_id,
    document_from_type: props.preOrder.document_from_type,
    status: props.preOrder.status,
    currency_po: props.preOrder.currency_po,
    type: props.preOrder.type,
    vat_percentage: props.preOrder.vat_percentage,
    down_payment_amount: props.preOrder.down_payment_amount,
    contact_notes: props.preOrder.contact_notes,
    transport_included: props.preOrder.transport_included,
    vat_deposit: props.preOrder.vat_deposit,
    amount_of_vehicles: props.preOrder.amount_of_vehicles,
    down_payment: props.preOrder.down_payment,
    total_purchase_price: props.preOrder.total_purchase_price,
    total_purchase_price_eur: props.preOrder.total_purchase_price_eur,
    total_fee_intermediate_supplier:
        props.preOrder.total_fee_intermediate_supplier,
    total_purchase_price_exclude_vat:
        props.preOrder.total_purchase_price_exclude_vat,
    total_vat: props.preOrder.total_vat,
    total_bpm: props.preOrder.total_bpm,
    total_purchase_price_include_vat:
        props.preOrder.total_purchase_price_include_vat,
    currency_rate: props.preOrder.currency_rate,

    viesFiles: [],
    creditCheckFiles: [],
    contractUnsignedFiles: [],
    contractSignedFiles: [],
    files: [],

    internal_remark_user_ids: [],
    internal_remark_role_ids: [],
    internal_remark: null!,
});

const formDisabled = computed(() => {
    return (
        props.preOrder.status !== PreOrderStatus.Concept && !$can("super-edit")
    );
});

const updateStatusForm = useForm<UpdateStatusForm>({
    _method: "patch",
    id: props.preOrder.id,
    status: null!,
    route: "pre-orders",
});

const handleContractSignedUpload = async () => {
    await save();

    await changeStatus(
        updateStatusForm,
        PreOrderStatus.Uploaded_signed_contract
    );
};

const preOrderVehicle = ref<PreOrderVehicle>(props.preOrder.pre_order_vehicle);
const tableData = ref<DataTable<PreOrderVehicle>>(props.dataTable);

watch(
    () => props.dataTable,
    () => {
        tableData.value.paginator = props.dataTable.paginator;
        tableData.value.data = props.dataTable.data;
    }
);

const save = async (only?: Array<string>) => {
    validate(form, preOrdersFormRules);

    return new Promise<void>((resolve, reject) => {
        form.post(route("pre-orders.update", props.preOrder.id as number), {
            preserveScroll: true,
            preserveState: true,
            forceFormData: true, // preserves all form data
            only: withFlash(only),
            onSuccess: () => {
                form.reset(
                    "internal_remark",
                    "viesFiles",
                    "creditCheckFiles",
                    "contractUnsignedFiles",
                    "contractSignedFiles",
                    "files"
                );

                resetOwnerId(form);

                resolve();
            },
            onError: () => {
                reject(new Error("Error, during update"));
            },
        });
    });
};

const addVehicle = async (item: PreOrderVehicle) => {
    preOrderVehicle.value = item;
    form.pre_order_vehicle_id = item.id;
    tableData.value.data = [item] as PreOrderVehicle[];
    calculatePreOrderPrices(form, preOrderVehicle?.value.calculation);

    await save();
};

const removeVehicle = async () => {
    preOrderVehicle.value = null!;
    form.pre_order_vehicle_id = null!;
    calculatePreOrderPrices(form, null);

    await save();
};
</script>

<template>
    <Head :title="__('Pre Order')" />

    <AppLayout>
        <Header :text="__('Pre Order')" />

        <Stepper
            :pre-order="preOrder"
            :companies="companies"
            :form-is-dirty="form.isDirty"
            :files="files"
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
                    :data-table="tableData"
                    :per-page-options="[5, 10, 15, 20, 50]"
                    :global-search="true"
                    :selected-row-indexes="[form.pre_order_vehicle_id]"
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
                                v-if="preOrder.status == PreOrderStatus.Concept"
                                class="flex gap-1.5"
                            >
                                <div
                                    v-if="form.pre_order_vehicle_id == item.id"
                                    class="flex gap-1.5"
                                >
                                    <button
                                        class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                                        @click="removeVehicle()"
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
                            <Link
                                v-if="
                                    $can('edit-pre-order-vehicle') &&
                                    !preOrder.pre_order_vehicle?.deleted_at
                                "
                                :href="
                                    route('pre-order-vehicles.edit', item.id)
                                "
                                class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition"
                            >
                                <IconPencilSquare
                                    classes="w-4 h-4 text-slate-600"
                                />
                            </Link>
                        </div>
                    </template>
                </Table>
            </Accordion>
        </div>

        <div class="flex justify-center">
            <div class="w-full max-w-[1721px]">
                <GeneralInformation
                    :form="form"
                    :default-currencies="defaultCurrencies"
                    :pre-order="preOrder"
                    :companies="companies"
                    :owner-props="{
                        mainCompanyUsers: mainCompanyUsers,
                        pendingOwnerships: pendingOwnerships,
                    }"
                    :suppliers="suppliers"
                    :intermediaries="intermediaries"
                    :purchasers="purchasers"
                    :disabled="formDisabled"
                    @calculate-prices="
                        calculatePreOrderPrices(
                            form,
                            preOrderVehicle?.calculation
                        )
                    "
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
                        />

                        <InputFile
                            id="credit-check-supplier"
                            v-model="form.creditCheckFiles"
                            :files="files.creditCheckFiles"
                            :text="__('Upload credit check supplier')"
                            text-classes="py-14"
                        />

                        <InputFile
                            id="order-contract-unsigned"
                            v-model="form.contractUnsignedFiles"
                            :files="files.contractUnsignedFiles as DatabaseFile[]"
                            :text="__('Upload Pre-order contract unsigned')"
                            text-classes="py-14"
                        />

                        <InputFile
                            id="order-contract-signed"
                            v-model="form.contractSignedFiles"
                            :files="files.contractSignedFiles"
                            :disabled="
                                props.preOrder.status !=
                                PreOrderStatus.Sent_to_supplier
                            "
                            :text="__('Upload Pre-order contract signed')"
                            text-classes="py-14"
                            @change="handleContractSignedUpload"
                        />

                        <InputFile
                            id="files"
                            v-model="form.files"
                            :files="files.files"
                            :text="__('Pre-order files')"
                            text-classes="py-14"
                        />
                    </div>
                </div>

                <SummaryFinancialInformation
                    :form="form"
                    :pre-order-vehicle="preOrderVehicle"
                    @calculate-prices="
                        calculatePreOrderPrices(
                            form,
                            preOrderVehicle?.calculation
                        )
                    "
                />

                <InternalRemarks
                    :internal-remarks="preOrder.internal_remarks"
                    :form="form"
                    :main-company-roles="mainCompanyRoles"
                    :main-company-users="mainCompanyUsers"
                />

                <Communication
                    :mails="preOrder.mails"
                    :files="files.generatedPdf"
                />

                <ChangeLogs :change-logs="preOrder.change_logs" />
            </div>
        </div>

        <ResetSaveButtons
            :processing="form.processing || updateStatusForm.processing"
            @reset="form.reset()"
            @save="save"
        />
    </AppLayout>
</template>

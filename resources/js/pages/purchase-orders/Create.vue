<script setup lang="ts">
import { Head, router, useForm, usePage } from "@inertiajs/vue3";
import { onMounted, ref } from "vue";

import Accordion from "@/components/Accordion.vue";
import Header from "@/components/Header.vue";
import WeekRangePicker from "@/components/html/WeekRangePicker.vue";
import InputFile from "@/components/html/InputFile.vue";
import InternalRemarks from "@/components/html/InternalRemarks.vue";
import ResetSaveButtons from "@/components/html/ResetSaveButtons.vue";
import VehicleLinks from "@/components/html/VehicleLinks.vue";
import GeneralInformation from "@/components/purchase-orders/GeneralInformation.vue";
import SummaryFinancialInformation from "@/components/purchase-orders/SummaryFinancialInformation.vue";
import Table from "@/data-table/Table.vue";
import { DataTable, Multiselect } from "@/data-table/types";
import { Coc } from "@/enums/Coc";
import { Currency } from "@/enums/Currency";
import { SupplierOrIntermediary } from "@/enums/SupplierOrIntermediary";
import CurrencyExchange from "@/icons/CurrencyExchange.vue";
import IconMinus from "@/icons/Minus.vue";
import IconPlus from "@/icons/Plus.vue";
import AppLayout from "@/layouts/AppLayout.vue";
import { purchaseOrdersFormRules } from "@/rules/purchase-orders-form-rules";
import {
    CompanyDefaults,
    SelectInput,
    Vehicle,
    WorkflowProcess,
} from "@/types";
import { Company, PurchaseOrderForm, Role, User } from "@/types";
import { findEnumKeyByValue, replaceEnumUnderscores } from "@/utils";
import { validate } from "@/validations";

const props = defineProps<{
    companies: Multiselect<Company>;
    dataTable: DataTable<Vehicle>;
    suppliers: Multiselect<User>;
    queryVehicleIds: Array<number>;
    intermediaries?: Multiselect<User>;
    companyDefaults?: CompanyDefaults;
    purchasers: Multiselect<User>;
    mainCompanyRoles?: Multiselect<Role>;
    mainCompanyUsers: Multiselect<User>;
    workflowProcesses?: Multiselect<WorkflowProcess>;
}>();

const vehicles = ref<Vehicle[]>([]);

const form = useForm<PurchaseOrderForm>({
    id: null!,
    owner_id: usePage().props.auth.user.id,
    status: null!,
    supplier_company_id: null!,
    supplier_id: null!,
    intermediary_company_id: null!,
    intermediary_id: null,
    purchaser_id: null!,
    type: null!,
    document_from_type: SupplierOrIntermediary.Supplier,
    papers: null!,
    payment_condition: null!,
    payment_condition_free_text: null!,
    currency_po: null!,
    vat_percentage: usePage().props?.auth.company.vat_percentage,
    down_payment_amount: null!,
    sales_restriction: null!,
    contact_notes: null!,
    transport_included: true,
    vat_deposit: false,
    vat_deposit_amount: null!,
    down_payment: false,
    total_purchase_price: null!,
    total_purchase_price_eur: null!,
    total_fee_intermediate_supplier: null!,
    total_purchase_price_exclude_vat: null!,
    total_transport: null!,
    total_vat: null!,
    total_bpm: null!,
    total_purchase_price_include_vat: null!,
    currency_rate: null!,

    viesFiles: [],
    creditCheckFiles: [],
    contractSignedFiles: [],
    files: [],

    vehicleIds: [],
    internal_remark_user_ids: [],
    internal_remark_role_ids: [],
    internal_remark: null!,
});

const summaryFinancialInformationRef = ref<typeof SummaryFinancialInformation>(
    null!
);

onMounted(() => {
    props.queryVehicleIds.forEach((vehicleId) => {
        const selectedVehicle = props.dataTable.data.find(
            (vehicle: Vehicle) => vehicle.id === vehicleId
        );

        if (selectedVehicle) {
            addVehicle(selectedVehicle); // Add each selected vehicle
        }
    });

    if (props.queryVehicleIds.length > 0) {
        summaryFinancialInformationRef.value.calculatePrices();

        const supplierId = Number(
            new URLSearchParams(window.location.search).get("supplier_id")
        );

        if (supplierId){
            form.supplier_id = supplierId;
        }
    }
});

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

const addVehicle = (item: Vehicle) => {
    vehicles.value.push(item);
    form.vehicleIds.push(item.id);

    if (vehicles.value.length == 1) {
        updateForm({
            name: "supplier_company_id",
            value: item.supplier_company_id,
        });
    }
};

const removeVehicle = (item: Vehicle) => {
    const formIndex = form.vehicleIds.indexOf(item.id);
    const vehicleIndex = vehicles.value.findIndex((obj) => obj.id === item.id);

    vehicles.value.splice(vehicleIndex, 1);

    if (formIndex !== -1) {
        form.vehicleIds.splice(formIndex, 1);
    }

    if (!vehicles.value.length) {
        updateForm({
            name: "supplier_company_id",
            value: null!,
        });
        updateForm({
            name: "supplier_id",
            value: null!,
        });
    }
};

const save = () => {
    validate(form, purchaseOrdersFormRules);

    form.post(route("purchase-orders.store"), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
        onError: () => {},
    });
};
</script>

<template>
    <Head :title="__('Purchase Order')" />

    <AppLayout>
        <Header :text="__('Purchase Order')" />

        <div
            class="relative rounded-lg border border-[#E9E7E7] shadow-sm bg-white py-4 sm:py-6 px-4 mt-4"
        >
            <Accordion>
                <template #head>
                    <div class="font-semibold text-xl sm:text-2xl mb-4">
                        {{ __("Vehicles") }}
                    </div>
                    {{form.supplier_id}}
                </template>

                <Table
                    :data-table="dataTable"
                    :per-page-options="[5, 10, 15, 20, 50]"
                    :global-search="true"
                    :advanced-filters="true"
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
                    :owner-props="{
                        mainCompanyUsers: mainCompanyUsers,
                    }"
                    :suppliers="suppliers"
                    :intermediaries="intermediaries"
                    :purchasers="purchasers"
                    :companies="companies"
                    :company-defaults="companyDefaults"
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
                            :files="[]"
                            :text="__('Upload VIES')"
                            text-classes="py-14"
                        />

                        <InputFile
                            id="credit-check-supplier"
                            v-model="form.creditCheckFiles"
                            :files="[]"
                            :text="__('Upload credit check supplier')"
                            text-classes="py-14"
                        />

                        <InputFile
                            id="order-contract-signed"
                            :files="[]"
                            :disabled="true"
                            :text="__('Upload Purchase order contract signed')"
                            text-classes="py-14"
                        />

                        <InputFile
                            id="files"
                            v-model="form.files"
                            :files="[]"
                            :text="__('General Purchase Order Documents')"
                            text-classes="py-14"
                        />
                    </div>
                </div>

                <SummaryFinancialInformation
                    ref="summaryFinancialInformationRef"
                    :vehicles="vehicles"
                    :form="form"
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
